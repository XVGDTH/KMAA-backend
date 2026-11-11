<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rappel de séance</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #b87333;">KMAA Academy</h1>
        <h2>Rappel : Séance en direct à venir</h2>
        
        <p>Bonjour,</p>
        <p>Nous vous rappelons que la séance en direct du cours <strong>{{ $courseTitle }}</strong> aura lieu le :</p>
        
        <p style="background: #f4f4f4; padding: 15px; border-radius: 8px; font-size: 18px; text-align: center;">
            <strong>{{ $scheduledAt }}</strong>
        </p>

        <p>Pour rejoindre la visioconférence, connectez-vous à votre espace de cours et cliquez sur l'onglet "Séances en direct".</p>
        
        <p>À très bientôt !<br>L'équipe KMAA Academy</p>
    </div>
</body>
</html>