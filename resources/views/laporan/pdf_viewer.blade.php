<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IT Submission Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Favicon dengan cache-bust agar langsung update setelah ganti logo --}}
    <link rel="icon" type="image/x-icon" href="{{ $app_logo_url }}">
    <link rel="apple-touch-icon" href="{{ $app_logo_url }}">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; background: #404040; }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
    {{-- Iframe menampilkan stream PDF dari route pdf.stream --}}
    <iframe src="{{ $pdfUrl }}" type="application/pdf" title="IT Submission Report"></iframe>
</body>
</html>
