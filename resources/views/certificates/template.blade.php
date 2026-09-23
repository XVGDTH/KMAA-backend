<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <style>
        /* ============================================================
           CERTIFICAT KMAA — A4 PAYSAGE, compatible DomPDF
           (pas de vh/vw, pas de border-radius en %, pas de flex)
           ============================================================ */

        @font-face {
            font-family: 'Great Vibes';
            /* str_replace : le \ est un caractère d'échappement CSS */
            src: url("{{ str_replace('\\', '/', storage_path('fonts/GreatVibes-Regular.ttf')) }}");
        }

        @page {
            margin: 0;
            /* size ignoré par DomPDF : le format paysage est
               défini dans le contrôleur via setPaper('a4','landscape') */
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: Georgia, 'Times New Roman', serif;
        }

        /* A4 paysage = 297mm x 210mm = 1122px x 793px (à 96dpi) */
        .certificate {
            position: relative;
            width: 1122px;
            height: 793px;          /* ✅ px fixes, PLUS de 100vh */
            background: #fdfcf7;
        }

        /* ------------------------------------------------------------
         * 1. VAGUE DU HAUT (coins arrondis en PX, pas en %)
         * ------------------------------------------------------------ */

        .wave-gold {
            position: absolute;
            top: 0; left: 0;
            width: 710px;
            height: 276px;
            background: #d4af37;
            border-radius: 0 0 280px 0;
        }

        .wave-main {
            position: absolute;
            top: 0; left: 0;
            width: 696px;
            height: 262px;
            background: #0f3d3e;
            border-radius: 0 0 270px 0;
        }

        .wave-top {
            position: absolute;
            top: 0; left: 0;
            width: 1122px;
            height: 92px;
            background: #0f3d3e;
        }

        .cert-heading {
            position: absolute;
            top: 14px;
            left: 48px;
            width: 420px;
            text-align: center;
        }

        .cert-heading .big {
            display: block;
            color: #d4af37;
            font-size: 40px;
            font-weight: bold;
            letter-spacing: 10px;
            line-height: 1;
        }

        .cert-heading .small {
            display: block;
            color: #f3e6c2;
            font-size: 13px;
            letter-spacing: 6px;
            margin-top: 6px;
        }

        /* ------------------------------------------------------------
         * 2. LOGO EN HAUT À DROITE
         * ------------------------------------------------------------ */

        .logo-box {
            position: absolute;
            top: 24px;
            right: 52px;
            width: 200px;
            text-align: center;
        }

        .logo-box img {
            width: 105px;
        }

        .logo-box .brand {
            display: block;
            margin-top: 4px;
            color: #0f3d3e;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        /* ------------------------------------------------------------
         * 3. RUBANS VERTICAUX (hauteurs EXPLICITES : 793 - top)
         * ------------------------------------------------------------ */

        .ribbon-gold-1 {
            position: absolute;
            left: 102px;
            top: 210px;
            height: 583px;          /* jusqu'en bas de la page */
            width: 22px;
            background: #d4af37;
        }

        .ribbon-navy {
            position: absolute;
            left: 128px;
            top: 250px;
            height: 543px;
            width: 64px;
            background: #0f3d3e;
        }

        .ribbon-gold-2 {
            position: absolute;
            left: 196px;
            top: 210px;
            height: 583px;
            width: 22px;
            background: #d4af37;
        }

        /* ------------------------------------------------------------
         * 4. MÉDAILLE (cercle = border-radius en PX : moitié du diamètre)
         * ------------------------------------------------------------ */

        .badge {
            position: absolute;
            left: 82px;
            top: 300px;
            width: 158px;
            height: 158px;
            border-radius: 79px;    /* ✅ 50% → 79px */
            background: #d4af37;
            border: 5px solid #b87333;
            text-align: center;
        }

        .badge-inner {
            width: 112px;
            height: 112px;
            margin: 15px auto;
            border-radius: 56px;    /* ✅ */
            border: 2px dashed #0f3d3e;
            padding-top: 12px;
        }

        .badge-stars {
            color: #0f3d3e;
            font-size: 11px;
            letter-spacing: 4px;
        }

        .badge-label {
            color: #0f3d3e;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 3px;
        }

        /* .badge-crown {
            color: #0f3d3e;
            font-size: 24px;
            line-height: 1.1;
        } */
            .badge-crown {
    color: #0f3d3e;
    font-size: 18px;
    font-weight: bold;
    letter-spacing: 2px;
    line-height: 1.4;
}

        .badge-year {
            color: #0f3d3e;
            font-size: 20px;
            font-weight: bold;
            margin-top: 1px;
        }

        /* ------------------------------------------------------------
         * 5. CONTENU PRINCIPAL (largeurs explicites, pas de right: seul)
         * ------------------------------------------------------------ */

        .content {
            position: absolute;
            left: 400px;
            top: 168px;
            width: 660px;
            text-align: center;
        }

        .presented {
            color: #0f3d3e;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            line-height: 2;
        }

        .student-name {
            display: inline-block;
            margin-top: 14px;
            padding: 0 46px 8px;
            border-bottom: 2px solid #d4af37;
            color: #1c1c1c;
            font-family: 'Great Vibes', 'Brush Script MT', cursive, serif;
            font-size: 46px;
            font-style: normal;
            line-height: 1.2;
        }

        .org {
            margin-top: 8px;
            color: #666;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .course-text {
            margin-top: 28px;
            color: #3d3d3d;
            font-size: 12.5px;
            line-height: 1.9;
            text-align: justify;
        }

        .course-text strong {
            color: #0f3d3e;
            font-size: 15px;
        }

        /* ------------------------------------------------------------
         * 6. PIED : DATE + QR
         * ------------------------------------------------------------ */

        /* .footer {
            position: absolute;
            left: 400px;
            bottom: 60px;
            width: 660px;
            display: table;
        } */
            .footer {
    position: absolute;
    left: 400px;
    top: 585px;        
    width: 660px;
    display: table;
}

        .footer-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #555;
        }

        .footer-line {
            width: 190px;
            margin: 0 auto;
            border-top: 1px solid #999;
            padding-top: 6px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .qr-container img {
            width: 74px;
            height: 74px;
            display: block;
            margin: 0 auto 4px;
        }

        .uuid {
            font-size: 7px;
            color: #999;
            word-break: break-all;
            margin-top: 2px;
        }

        /* ------------------------------------------------------------
         * 7. BANDEAUX DU BAS
         * ------------------------------------------------------------ */

        .footer-bar-navy {
            position: absolute;
            bottom: 22px; left: 0;
            width: 1122px;
            height: 6px;
            background: #0f3d3e;
        }

        .footer-bar-gold {
            position: absolute;
            bottom: 0; left: 0;
            width: 1122px;
            height: 22px;
            background: #d4af37;
        }
    </style>
</head>

<body>

<div class="certificate">

    <!-- ===== Vague du haut ===== -->
    <div class="wave-gold"></div>
    <div class="wave-main"></div>
    <div class="wave-top"></div>

    <div class="cert-heading">
        <span class="big">CERTIFICAT</span>
        <span class="small">DE RÉUSSITE</span>
    </div>

    <!-- ===== Logo ===== -->
    <div class="logo-box">
        @if (!empty($logoPath))
            <img src="{{ $logoPath }}" alt="KMAA">
        @endif
        <span class="brand">KMAA E-LEARNING</span>
    </div>

    <!-- ===== Rubans + médaille ===== -->
    <div class="ribbon-gold-1"></div>
    <div class="ribbon-navy"></div>
    <div class="ribbon-gold-2"></div>

    <!-- <div class="badge">
        <div class="badge-inner">
            <div class="badge-stars">★ ★ ★</div>
            <div class="badge-label">EXCELLENCE</div>
            <div class="badge-crown">♛</div>
            <div class="badge-year">{{ date('Y') }}</div>
        </div>
    </div> -->
        <div class="badge">
        <div class="badge-inner">
            <div class="badge-stars">&bull; &bull; &bull;</div>      <!-- au lieu de ★ ★ ★ -->
            <div class="badge-label">EXCELLENCE</div>
            <div class="badge-crown" style="font-family: Georgia, serif;">KMAA</div>  <!-- au lieu de ♛ -->
            <div class="badge-year">{{ date('Y') }}</div>
        </div>
    </div>

    <!-- ===== Contenu principal ===== -->
    <div class="content">

        <div class="presented">
            Ce certificat<br>
            est fièrement décerné à
        </div>

        <div class="student-name">
            {{ $studentName }}
        </div>

        <div class="org">
            KMAA — {{ $course->category }}
        </div>

        <div class="course-text">
            pour avoir complété avec succès l'intégralité de la formation
            <strong>« {{ $course->title }} »</strong>,
            incluant l'ensemble des chapitres, séquences, travaux pratiques
            et évaluations du parcours. La présente atteste de l'assiduité,
            de l'engagement et de la validation des compétences requises
            au terme de ce programme.
        </div>

    </div>

    <!-- ===== Pied : date + vérification ===== -->
    <div class="footer">

        <div class="footer-cell">
            <div class="footer-line">
                Délivré le {{ $issuedAt }}
            </div>
        </div>

        <div class="footer-cell">
            <div class="qr-container">
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
            </div>
            <div class="uuid">
                Vérifiez l'authenticité : {{ $uuid }}
            </div>
        </div>

    </div>

    <!-- ===== Bandeaux du bas ===== -->
    <div class="footer-bar-navy"></div>
    <div class="footer-bar-gold"></div>

</div>

</body>
</html>