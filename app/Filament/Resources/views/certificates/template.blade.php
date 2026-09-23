<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Courier', sans-serif; 
            margin: 0;
            padding: 0;
        }

        .certificate {
            width: 100%;
            height: 100vh;
            box-sizing: border-box;
            border: 14px solid #0f3d3e;
            padding: 60px;
            text-align: center;
            position: relative;
        }

        .border-inner {
            position: absolute;
            top: 24px;
            left: 24px;
            right: 24px;
            bottom: 24px;
            border: 2px solid #b87333;
        }

        .content {
            position: relative;
            z-index: 1;
            padding-top: 40px;
        }

        .logo {
            width: 90px;
            margin-bottom: 20px;
        }

        .eyebrow {
            color: #b87333;
            letter-spacing: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title {
            color: #0f3d3e;
            font-size: 34px;
            font-weight: bold;
            margin: 10px 0 30px;
        }

        .intro {
            color: #555;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .student-name {
            color: #0f3d3e;
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0 30px;
            border-bottom: 2px solid #b87333;
            display: inline-block;
            padding-bottom: 8px;
        }

        .course-title {
            color: #333;
            font-size: 18px;
            margin-bottom: 40px;
        }

        .footer {
            display: table;
            width: 100%;
            margin-top: 60px;
        }

        .footer-cell {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #666;
        }

        .qr-container {
            width: 90px;
            height: 90px;
            margin: 0 auto 5px auto;
            text-align: center;
        }

        .qr-container img {
            width: 80px;
            height: 80px;
            display: block;
            margin: 0 auto;
        }

        .date {
            font-size: 12px;
            color: #555;
        }

        .uuid {
            font-size: 8px;
            color: #999;
            margin-top: 4px;
            word-break: break-all;
        }
    </style>
</head>

<body>

<div class="certificate">

    <div class="border-inner"></div>

    <div class="content">

        @if (!empty($logoPath))
            <img class="logo" src="{{ $logoPath }}" alt="KMAA">
        @endif

        <div class="eyebrow">
            Certificat de réussite
        </div>

        <div class="title">
            KMAA — {{ $course->category }}
        </div>

        <div class="intro">
            Ce certificat est décerné à
        </div>

        <div class="student-name">
            {{ $studentName }}
        </div>

        <div class="course-title">
            pour avoir complété avec succès la formation
            <br>

            <strong>
                {{ $course->title }}
            </strong>
        </div>

        <div class="footer">

            <div class="footer-cell">
                <div class="date">
                    Délivré le {{ $issuedAt }}
                </div>
            </div>

            <div class="footer-cell">

                <div class="qr-container">
                    <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
                </div>

                <div class="uuid">
                    {{ $uuid }}
                </div>

            </div>

            <div class="footer-cell">
                <div class="date">
                    KMAA E-Learning
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>