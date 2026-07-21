<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\ArsipLampiran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReaderException;

class ArsipLampiranService
{
    /** Direktori cache PDF gabungan & draft, relatif base_path/storage/app. */
    private const CACHE_DIR = 'pdf_cache';

    /**
     * Service version — di-include di cache key supaya perubahan logic merge/render
     * (mis. tambah cover-page placeholder, urutan append, dst) auto-invalidate cache lama.
     * Bump angka ini setiap kali ada perubahan signifikan di flow streamMergedPdf.
     */
    private const SERVICE_VERSION = 2;

    /**
     * Simpan banyak file lampiran PDF (semua sudah tervalidasi di controller).
     * @return ArsipLampiran[]
     */
    public function storeMany(Arsip $arsip, array $files, User $uploader, ?string $keterangan = null): array
    {
        $result = [];
        $sort = (int) (ArsipLampiran::where('arsip_id', $arsip->id)->max('sort_order') ?? 0);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $sort++;

            $hash = hash_file('sha256', $file->getRealPath());
            $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $filename = 'LAMP_' . ($arsip->no_registrasi ?: 'A' . $arsip->id) . '_' . time() . '_' . $sort . '_' . $clean;
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

            $path = $file->storeAs("lampiran/{$arsip->id}", $filename, 'public');
            $pageCount = $this->countPdfPages(Storage::disk('public')->path($path));

            $result[] = ArsipLampiran::create([
                'arsip_id' => $arsip->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_hash' => $hash,
                'mime_type' => $file->getMimeType() ?: 'application/pdf',
                'page_count' => $pageCount,
                'keterangan' => $keterangan,
                'uploaded_by' => $uploader->id,
                'sort_order' => $sort,
            ]);
        }

        // Invalidasi cache merge untuk arsip ini
        $this->invalidateCache($arsip->id);

        return $result;
    }

    private function countPdfPages(string $absolutePath): ?int
    {
        try {
            $pdf = new Fpdi();
            return $pdf->setSourceFile($absolutePath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Render Draft + gabung lampiran → 1 PDF. PAKAI CACHE bila arsip belum berubah.
     */
    public function streamMergedPdf(Arsip $arsip)
    {
        $cacheDir = storage_path('app/' . self::CACHE_DIR);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $cacheKey = $this->buildCacheKey($arsip);
        $cachePath = $cacheDir . DIRECTORY_SEPARATOR . "arsip_{$arsip->id}_{$cacheKey}.pdf";

        if (is_file($cachePath) && filesize($cachePath) > 0) {
            return $this->streamFromCache($arsip, $cachePath);
        }

        // Bersihkan cache lama untuk arsip ini (kalau hash berubah)
        foreach (glob($cacheDir . DIRECTORY_SEPARATOR . "arsip_{$arsip->id}_*.pdf") as $old) {
            @unlink($old);
        }

        // 1) Render draft → binary
        $draftBin = $this->renderDraftBinary($arsip);
        $tmpDraft = $cacheDir . DIRECTORY_SEPARATOR . "tmp_draft_{$arsip->id}_" . uniqid() . '.pdf';
        file_put_contents($tmpDraft, $draftBin);

        // 1b) Render lampiran catatan personal (kalau ada) → binary terpisah
        $tmpNotes = null;
        $personalNotes = $arsip->relationLoaded('personalNotes')
            ? $arsip->personalNotes
            : $arsip->personalNotes()->with('user:id,name,role')->get();
        if ($personalNotes && $personalNotes->isNotEmpty()) {
            $notesBin = $this->renderNotesAttachmentBinary($arsip, $personalNotes);
            $tmpNotes = $cacheDir . DIRECTORY_SEPARATOR . "tmp_notes_{$arsip->id}_" . uniqid() . '.pdf';
            file_put_contents($tmpNotes, $notesBin);
        }

        // Temp paths utk cleanup at-the-end
        $tmpToCleanup = [$tmpDraft];
        if ($tmpNotes) $tmpToCleanup[] = $tmpNotes;

        try {
            // 2) Gabung dengan FPDI: draft → uploaded lampiran → catatan personal (terakhir)
            $merged = new Fpdi();
            $this->appendPdfPages($merged, $tmpDraft);

            foreach ($arsip->lampirans as $lamp) {
                $this->appendLampiranSafely($merged, $lamp, $cacheDir, $tmpToCleanup);
            }

            if ($tmpNotes && is_file($tmpNotes)) {
                try {
                    $this->appendPdfPages($merged, $tmpNotes);
                } catch (PdfReaderException $e) {
                    // skip notes kalau gagal
                }
            }

            $output = $merged->Output('S');
            // Simpan ke cache
            file_put_contents($cachePath, $output);

            return $this->streamFromCache($arsip, $cachePath);
        } finally {
            foreach ($tmpToCleanup as $tmp) {
                if (is_file($tmp)) @unlink($tmp);
            }
        }
    }

    /**
     * Coba append 1 lampiran ke merged PDF dgn fallback chain:
     * 1. FPDI langsung (PDF kompatibel)
     * 2. Decrypt via shell tool (qpdf / ghostscript) bila lampiran terenkripsi & tool tersedia
     * 3. Render placeholder cover page (dompdf) yg menampilkan info lampiran + alasan gagal merge
     */
    private function appendLampiranSafely(Fpdi $merged, ArsipLampiran $lamp, string $cacheDir, array &$tmpToCleanup): void
    {
        $abs = Storage::disk('public')->path($lamp->file_path);

        if (!is_file($abs)) {
            $this->appendPlaceholder($merged, $lamp, $cacheDir, $tmpToCleanup,
                'File tidak ditemukan di server (mungkin terhapus atau path-nya berubah).');
            return;
        }

        try {
            $this->appendPdfPages($merged, $abs);
            return;
        } catch (PdfReaderException $e) {
            $msg = $e->getMessage();

            // Kasus 1: ter-enkripsi → coba decrypt via shell tool
            if (stripos($msg, 'encrypted') !== false || stripos($msg, 'security') !== false) {
                $decrypted = $this->tryDecryptPdf($abs, $cacheDir);
                if ($decrypted) {
                    $tmpToCleanup[] = $decrypted;
                    try {
                        $this->appendPdfPages($merged, $decrypted);
                        return;
                    } catch (\Throwable $e2) {
                        // fall through ke placeholder
                    }
                }
                $this->appendPlaceholder($merged, $lamp, $cacheDir, $tmpToCleanup,
                    'PDF terenkripsi/proteksi sehingga tidak dapat di-merge inline. ' .
                    'Silakan download file asli dari sistem & save ulang tanpa proteksi bila ingin tergabung.');
                return;
            }

            // Kasus 2: format/struktur tidak didukung
            $this->appendPlaceholder($merged, $lamp, $cacheDir, $tmpToCleanup,
                'Format PDF tidak didukung untuk merge inline (' . $msg . ').');
        } catch (\Throwable $e) {
            $this->appendPlaceholder($merged, $lamp, $cacheDir, $tmpToCleanup,
                'Error tidak terduga saat memproses lampiran: ' . $e->getMessage());
        }
    }

    /**
     * Coba decrypt PDF terenkripsi via shell tool. Return path file decrypted bila berhasil, null bila gagal.
     * Coba urutan: qpdf → ghostscript (gswin64c/gswin32c/gs).
     */
    private function tryDecryptPdf(string $absPath, string $cacheDir): ?string
    {
        $outPath = $cacheDir . DIRECTORY_SEPARATOR . 'tmp_decrypt_' . uniqid() . '.pdf';
        $isWin = stripos(PHP_OS, 'WIN') === 0;
        $whichCmd = $isWin ? 'where' : 'which';

        // 1) qpdf — paling reliable utk strip encryption
        $qpdfPath = trim((string) @shell_exec("$whichCmd qpdf 2>" . ($isWin ? 'nul' : '/dev/null')));
        if ($qpdfPath !== '') {
            $src = escapeshellarg($absPath);
            $out = escapeshellarg($outPath);
            @shell_exec("qpdf --decrypt $src $out 2>&1");
            if (is_file($outPath) && filesize($outPath) > 0) return $outPath;
        }

        // 2) ghostscript fallback (re-write PDF strips most encryption)
        foreach (['gswin64c', 'gswin32c', 'gs'] as $bin) {
            $gsPath = trim((string) @shell_exec("$whichCmd $bin 2>" . ($isWin ? 'nul' : '/dev/null')));
            if ($gsPath === '') continue;

            $src = escapeshellarg($absPath);
            $out = escapeshellarg($outPath);
            @shell_exec("$bin -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dPDFSETTINGS=/default -sOutputFile=$out $src 2>&1");
            if (is_file($outPath) && filesize($outPath) > 0) return $outPath;
            break;
        }

        return null;
    }

    /**
     * Render placeholder page (info lampiran + alasan) lalu append ke merged PDF.
     */
    private function appendPlaceholder(Fpdi $merged, ArsipLampiran $lamp, string $cacheDir, array &$tmpToCleanup, string $reason): void
    {
        try {
            $pdf = Pdf::loadView('print.lampiran_placeholder', [
                'arsip' => $lamp->arsip ?? Arsip::find($lamp->arsip_id),
                'lampiran' => $lamp,
                'reason' => $reason,
            ])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'dpi' => 96,
                ]);

            $tmpPath = $cacheDir . DIRECTORY_SEPARATOR . 'tmp_placeholder_' . $lamp->id . '_' . uniqid() . '.pdf';
            file_put_contents($tmpPath, $pdf->output());
            $tmpToCleanup[] = $tmpPath;

            $this->appendPdfPages($merged, $tmpPath);
        } catch (\Throwable $e) {
            // Last resort: silent skip — lebih baik output sebagian daripada gagal total
        }
    }

    private function renderNotesAttachmentBinary(Arsip $arsip, $personalNotes): string
    {
        $pdf = Pdf::loadView('print.arsip_notes_attachment', [
            'arsip' => $arsip,
            'personalNotes' => $personalNotes,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isJavascriptEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
                'fontHeightRatio' => 1,
                'isPhpEnabled' => false,
            ]);
        return $pdf->output();
    }

    private function renderDraftBinary(Arsip $arsip): string
    {
        $template = $arsip->jenis_pengajuan === 'Bundel'
            ? 'print.arsip_draft_bundel'
            : 'print.arsip_draft';

        $pdf = Pdf::loadView($template, ['arsip' => $arsip, 'forPdf' => true])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isJavascriptEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
                'fontHeightRatio' => 1,
                'isPhpEnabled' => false,
            ]);
        return $pdf->output();
    }

    /**
     * Build cache key dari kombinasi (arsip.updated_at, sum updated_at lampirans, signatures, approvals, personal_notes).
     */
    private function buildCacheKey(Arsip $arsip): string
    {
        $notesAgg = \DB::table('arsip_personal_notes')
            ->where('arsip_id', $arsip->id)
            ->selectRaw('MAX(updated_at) as max_at, COUNT(*) as cnt')
            ->first();

        $parts = [
            'v' . self::SERVICE_VERSION,
            (string) ($arsip->updated_at?->timestamp ?? 0),
            (string) ($arsip->lampirans?->max('updated_at')?->timestamp ?? 0),
            (string) ($arsip->signatures?->max('updated_at')?->timestamp ?? 0),
            (string) ($arsip->approvals?->max('updated_at')?->timestamp ?? 0),
            (string) $arsip->lampirans?->count(),
            (string) ($notesAgg->max_at ? strtotime($notesAgg->max_at) : 0),
            (string) ($notesAgg->cnt ?? 0),
        ];
        return substr(md5(implode('|', $parts)), 0, 12);
    }

    private function streamFromCache(Arsip $arsip, string $cachePath)
    {
        $filename = ($arsip->no_registrasi ?: 'pengajuan-' . $arsip->id) . '_full.pdf';
        $etag = '"' . md5_file($cachePath) . '"';
        return response(file_get_contents($cachePath), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            // Force browser revalidate setiap kali; backend tetap pakai disk cache utk speed
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'ETag' => $etag,
            'X-PDF-Cache' => 'HIT',
            'X-Service-Version' => (string) self::SERVICE_VERSION,
        ]);
    }

    /** Invalidasi semua cache PDF utk satu arsip. */
    public function invalidateCache(int $arsipId): void
    {
        $cacheDir = storage_path('app/' . self::CACHE_DIR);
        if (!is_dir($cacheDir)) return;
        foreach (glob($cacheDir . DIRECTORY_SEPARATOR . "arsip_{$arsipId}_*.pdf") as $old) {
            @unlink($old);
        }
    }

    /** Append semua halaman dari $sourcePath ke instance Fpdi. */
    private function appendPdfPages(Fpdi $pdf, string $sourcePath): void
    {
        $pageCount = $pdf->setSourceFile($sourcePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }
    }
}
