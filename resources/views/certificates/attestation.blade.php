<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        /* ============================================================
           ATTESTATION KMAA — A4 PAYSAGE, compatible DomPDF
           ============================================================ */
        @page {
            margin: 0; /* ✅ CRUCIAL : supprime les marges DomPDF → tout tient sur 1 page */
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: Helvetica, Arial, sans-serif;
            color: #2b2b2b;
        }

        /* A4 paysage ≈ 1122×793px. On retire 1px pour absorber les arrondis DomPDF */
        .page {
            position: relative;
            width: 1121px;
            height: 792px;
            background: #ffffff;
            overflow: hidden;
        }

        .frame {
            position: absolute;
            top: 30px;
            left: 30px;
            width: 1061px;
            height: 732px;
            border: 2px solid #0f3d3e;
        }

        /* Logo (haut gauche) */
        .logo-box {
            position: absolute;
            top: 68px;
            left: 78px;
            width: 220px;
        }

        .logo-box img {
            width: 150px;
            display: block;
        }

        .logo-box .logo-text {
            display: block;
            border: 2px solid #0f3d3e;
            padding: 14px 22px;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #0f3d3e;
            width: 190px;
            text-align: center;
        }

        .logo-box .logo-underline {
            display: block;
            width: 190px;
            height: 3px;
            background: #b87333;
            margin-top: 4px;
        }

        /* Référence + QR (haut droite) */
        .reference-box {
            position: absolute;
            top: 60px;
            right: 220px;
            width: 260px;
            text-align: right;
        }

        .reference-label {
            font-size: 11px;
            color: #777;
            letter-spacing: 1px;
        }

        .reference-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f3d3e;
            letter-spacing: 1px;
            border-bottom: 1px dotted #999;
            padding-bottom: 6px;
            margin-top: 4px;
        }

        .qr-box {
            position: absolute;
            top: 55px;
            right: 70px;
            width: 120px;
            text-align: center;
        }

        .qr-box img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto;
        }

        .qr-box .qr-caption {
            font-size: 9px;
            color: #999;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        /* Titre central */
        .title-block {
            position: absolute;
            top: 190px;
            left: 160px;
            width: 800px;
            text-align: center;
        }

        .eyebrow {
            font-size: 12px;
            letter-spacing: 6px;
            color: #999;
            text-transform: uppercase;
        }

        .main-title {
            font-size: 54px;
            font-weight: bold;
            color: #0f3d3e;
            letter-spacing: 4px;
            margin-top: 6px;
        }

        .title-underline {
            width: 130px;
            height: 4px;
            background: #b87333;
            margin: 10px auto 0;
        }

        /* Corps du texte */
        .body-block {
            position: absolute;
            top: 300px;
            left: 160px;
            width: 800px;
            text-align: center;
        }

        .quote-line {
            font-size: 15px;
            font-style: italic;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 1px dotted #ccc;
            margin-bottom: 16px;
        }

        .parcours-line {
            font-size: 12px;
            color: #444;
            margin-bottom: 14px;
        }

        .legal-line {
            font-size: 11px;
            color: #777;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .delivered-to-label {
            font-size: 13px;
            color: #333;
            margin-bottom: 10px;
        }

        .student-name {
            font-size: 26px;
            font-weight: bold;
            color: #0f3d3e;
            display: inline-block;
            min-width: 500px;
            border-bottom: 1px dotted #999;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .promotion-line {
            font-size: 11px;
            color: #999;
            margin-top: 8px;
        }

        .made-in-line {
            font-size: 12px;
            color: #444;
            margin-top: 14px;
        }

        /* Cachet (bas gauche) */
       .seal {
    position: absolute;
    top: 560px;          /* ✅ top au lieu de bottom */
    left: 90px;
    width: 130px;
    height: 130px;
    border: 1.5px dashed #bbb;
    text-align: center;
}

        .seal span {
            display: block;
            margin-top: 55px;
            font-size: 10px;
            color: #aaa;
        }

        /* Signatures */
        .signatures {
    position: absolute;
    top: 590px;          /* ✅ top au lieu de bottom */
    left: 340px;
    width: 720px;
    display: table;
}

        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px dotted #999;
            width: 200px;
            margin: 0 auto 8px;
        }

        .signature-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f3d3e;
        }

        .signature-role {
            font-size: 10px;
            color: #777;
            margin-top: 2px;
        }

        /* Pied de page */
       .footer {
    position: absolute;
    top: 695px;          /* ✅ top au lieu de bottom */
    left: 78px;
    width: 966px;
}

        .footer-legal {
            font-size: 9.5px;
            color: #999;
            display: table;
            width: 100%;
        }

        .footer-legal .left {
            display: table-cell;
            text-align: left;
        }

        .footer-legal .right {
            display: table-cell;
            text-align: right;
        }

        .footer-address {
            font-size: 9.5px;
            color: #999;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="frame"></div>

    <!-- Logo -->
    <div class="logo-box">
        @if (!empty($logoPath) && file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="KMAA">
        @else
            <span class="logo-text">KMAA</span>
            <span class="logo-underline"></span>
        @endif
    </div>

    <!-- Référence + QR -->
    <div class="reference-box">
        <div class="reference-label">Référence</div>
        <div class="reference-value">{{ $reference }}</div>
    </div>

    <div class="qr-box">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
        <div class="qr-caption">Vérifier sur kmaa-ac.com</div>
    </div>

    <!-- Titre -->
    <div class="title-block">
        <div class="eyebrow">Formation professionnelle</div>
        <div class="main-title">ATTESTATION</div>
        <div class="title-underline"></div>
    </div>

    <!-- Corps -->
    <div class="body-block">
        <div class="quote-line">« {{ $courseTitle }} »</div>

        <!-- ✅ Conforme au modèle : du ... au ... + volume horaire -->
        <div class="parcours-line">
            Parcours suivi <strong>{{ $courseTitle }}</strong>
            du <strong>{{ $startedAt }}</strong>
            au <strong>{{ $endedAt }}</strong>,
            pour un volume horaire de <strong>{{ $durationHours }}</strong> heures.
        </div>

        <div class="legal-line">
            Atteste que l'intéressé(e) a suivi avec assiduité l'ensemble des séances
            de la formation mentionnée ci-dessus.
        </div>

        <div class="delivered-to-label">L'attestation est délivrée à</div>
        <div class="student-name">{{ $studentName }}</div>

        <!-- @if (!empty($course->promotion_label))
            <div class="promotion-line">Promotion {{ $course->promotion_label }}</div>
        @endif -->
        @if (!empty($promotionLabel))
    <div class="promotion-line">Promotion {{ $promotionLabel }}</div>
@endif

        <div class="made-in-line">Fait à Ouagadougou, le {{ $issuedAt }}</div>
    </div>

    <!-- Cachet -->
    <div class="seal">
        <span>Cachet du cabinet</span>
    </div>

    <!-- Signatures : les 3 signataires officiels -->
    <div class="signatures">
        <div class="signature-cell">
            <div class="signature-line"></div>
            <div class="signature-name">Touwend-Taoré M. YAOGO</div>
            <div class="signature-role">Expert-comptable diplômé | Commissaire aux comptes</div>
            <div class="signature-role"><strong>Directeur Associé</strong></div>
            <div class="signature-role">KMAA Burkina</div>
        </div>
        <div class="signature-cell">
            <div class="signature-line"></div>
            <div class="signature-name">Mme NABASSAGA/ZOUNGRANA Halifatou</div>
            <div class="signature-role"><strong>Responsable Pédagogique</strong></div>
            <div class="signature-role">KMAA Burkina</div>
        </div>
        <div class="signature-cell">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $instructorName }}</div>
            <div class="signature-role"><strong>Formateur</strong></div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div class="footer-legal">
            <span class="left">Attestation de participation délivrée par KMAA AUDIT ET CONSEIL. Elle ne constitue pas un diplôme d'État ni un certificat de compétences.</span>
            <span class="right">Authenticité vérifiable en scannant le code.</span>
        </div>
        <div class="footer-address">
            KMAA AUDIT ET CONSEIL, SAS, société d'expertise comptable inscrite au tableau de l'ONECCA-BF sous le n° ....................................<br>
            Avenue Kadiogo, Pissy, Ouagadougou, Burkina Faso, 11 BP 164 Ouagadougou CMS 11<br>
            téléphone +226 72 53 46 46 / +226 54 53 38 53, www.kmaa-ac.com, contact@kmaa-ac.com
        </div>
    </div>
</div>

</body>
</html>