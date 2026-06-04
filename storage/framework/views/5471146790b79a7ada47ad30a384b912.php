<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>IT Submission Report</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e($app_logo_url); ?>">
    <!-- PDF.js library locally -->
    <script src="<?php echo e(asset('vendor/pdfjs/pdf.min.js')); ?>"></script>
    <style>
        :root {
            --bg-color: #1a1a1a;
            --toolbar-color: #2d2d2d;
            --text-color: #e0e0e0;
            --accent-color: #3b82f6;
        }

        body,
        html {
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
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            background-color: white;
            max-width: 100%;
        }

        .toolbar {
            height: 56px;
            background-color: var(--toolbar-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            z-index: 100;
            color: var(--text-color);
        }

        .toolbar-title {
            font-size: 14px;
            font-weight: 500;
        }

        .controls {
            display: flex;
            gap: 10px;
        }

        .btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-color);
            padding: 6px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.1);
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
            to {
                transform: rotate(360deg);
            }
        }

        canvas {
            max-width: 100%;
            height: auto !important;
        }

        @media (max-width: 600px) {
            .btn span {
                display: none;
            }

            .toolbar {
                padding: 0 10px;
            }
        }
    </style>
</head>

<body>
    <div id="loader" class="loader">
        <div class="spinner"></div>
        <div style="color: var(--text-color)">Memproses Laporan...</div>
    </div>

    <div class="toolbar">
        <div class="toolbar-title">IT Submission Report Analytics</div>
        <div class="controls">
            <button class="btn" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>Cetak</span>
            </button>
            <a href="<?php echo e($pdfUrl); ?>&download=1" class="btn">
                <i class="bi bi-download"></i>
                <span>Unduh PDF</span>
            </a>
        </div>
    </div>

    <div id="viewer-container"></div>

    <script>
        const url = '<?php echo $pdfUrl; ?>';
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo e(asset('vendor/pdfjs/pdf.worker.min.js')); ?>';

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

                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    await renderPage(i);
                }
            } catch (error) {
                console.error('Error loading PDF:', error);
                loader.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 20px;">Gagal memuat laporan. Silakan coba cetak ulang atau unduh file.</div>';
            }
        }

        loadPdf();
    </script>
</body>

</html><?php /**PATH C:\laragon\www\e_arsip\resources\views\laporan\pdf_viewer.blade.php ENDPATH**/ ?>