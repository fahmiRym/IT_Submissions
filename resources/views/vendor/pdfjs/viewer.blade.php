<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PDF Viewer | {{ $filename }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- PDF.js library locally -->
    <script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
    <style>
        :root {
            --bg-color: #1a1a1a;
            --toolbar-color: #2d2d2d;
            --text-color: #e0e0e0;
            --accent-color: #3b82f6;
        }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        #viewer-container {
            height: calc(100% - 56px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            scroll-behavior: smooth;
        }
        .pdf-page {
            margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            background-color: white;
            transition: transform 0.2s;
        }
        .toolbar {
            height: 56px;
            background-color: var(--toolbar-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            z-index: 100;
            color: var(--text-color);
        }
        .toolbar-title {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 50%;
        }
        .controls {
            display: flex;
            gap: 10px;
        }
        .btn {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-color);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn:hover {
            background: rgba(255,255,255,0.05);
            border-color: var(--accent-color);
        }
        .loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            z-index: 1000;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(59, 130, 246, 0.1);
            border-left-color: var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error-message {
            background: #ef4444;
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            max-width: 80%;
        }
        canvas {
            max-width: 100%;
            height: auto !important;
        }
        /* Mobile optimization */
        @media (max-width: 600px) {
            .toolbar {
                padding: 0 10px;
            }
            .btn span { display: none; }
            .btn { padding: 8px; }
        }
    </style>
</head>
<body>
    <div id="loader" class="loader">
        <div class="spinner"></div>
        <div style="color: var(--text-color)">Memuat Dokumen...</div>
    </div>

    <div class="toolbar">
        <div class="toolbar-title">{{ $filename }}</div>
        <div class="controls">
            <button class="btn" onclick="window.print()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
                <span>Cetak</span>
            </button>
            <a href="{{ $fileUrl }}" download class="btn" style="text-decoration: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span>Unduh</span>
            </a>
        </div>
    </div>

    <div id="viewer-container"></div>

    <script>
        const url = '{{ $fileUrl }}';
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset('vendor/pdfjs/pdf.worker.min.js') }}';

        let pdfDoc = null;
        const container = document.getElementById('viewer-container');
        const loader = document.getElementById('loader');

        async function renderPage(pageNum) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: 1.5 });
            
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page';
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            container.appendChild(canvas);
            await page.render(renderContext).promise;
        }

        async function loadPdf() {
            try {
                const loadingTask = pdfjsLib.getDocument(url);
                pdfDoc = await loadingTask.promise;
                
                loader.style.display = 'none';

                // Render all pages
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    await renderPage(i);
                }
            } catch (error) {
                console.error('Error loading PDF:', error);
                loader.innerHTML = `
                    <div class="error-message">
                        <strong>Gagal Memuat PDF</strong><br>
                        ${error.message}<br><br>
                        <small>Pastikan koneksi internet stabil atau coba unduh file langsung.</small>
                    </div>
                `;
            }
        }

        loadPdf();
    </script>
</body>
</html>
