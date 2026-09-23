<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f1ea; padding:20px; margin:0;">

<div style="max-width:600px; margin:0 auto; background:#fff; padding:32px; border-radius:12px; border-top:5px solid #0f3d3e;">

    <h1 style="color:#0f3d3e; font-size:22px;">
        Bonjour {{ $participant->name }},
    </h1>

    <p>Merci pour votre participation à la formation en présentiel :</p>

    <div style="background:#f8fafc; padding:16px; border-left:4px solid #b87333; border-radius:6px; margin:20px 0;">
        <strong style="color:#0f3d3e;">{{ $certificate->course_title }}</strong><br>
        <em>Du {{ $training->starts_at->format('d/m/Y') }} au {{ $training->ends_at->format('d/m/Y') }}</em><br>
        <em>{{ $training->location ?? '' }}</em>
    </div>

    <p>
        Votre <strong>{{ $certificate->type === 'attestation' ? 'attestation' : 'certificat' }}</strong>
        est prêt. Vous pouvez le télécharger à tout moment à l'aide du lien ci-dessous.
    </p>

    <p style="text-align:center; margin:32px 0;">
        <a href="{{ $downloadUrl }}"
           style="background:#0f3d3e; color:#fff; padding:14px 32px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;">
            ⬇ Télécharger mon document
        </a>
    </p>

    <p style="font-size:13px; color:#666;">
        Référence : <strong>{{ $certificate->reference }}</strong><br>
        Vérification en ligne : <a href="{{ $verifyUrl }}" style="color:#0f3d3e;">{{ $verifyUrl }}</a>
    </p>

    <hr style="border:none; border-top:1px solid #e2e8f0; margin:28px 0;">

    <p style="font-size:12px; color:#999; line-height:1.6;">
        <strong>KMAA AUDIT ET CONSEIL</strong><br>
        Avenue Kadiogo, Pissy, Ouagadougou, Burkina Faso<br>
        www.kmaa-ac.com — contact@kmaa-ac.com
    </p>

</div>

</body>
</html>