<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — {{ $training->title }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; background: #f4f1ea; margin: 0; padding: 20px; }
        .card { max-width: 520px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 14px; border-top: 6px solid #0f3d3e; box-shadow: 0 8px 30px rgba(0,0,0,.08); }
        h1 { color: #0f3d3e; margin: 0 0 8px; font-size: 22px; }
        .subtitle { color: #777; margin-bottom: 24px; font-size: 14px; }
        .info { background: #f8fafc; padding: 14px; border-radius: 8px; font-size: 13.5px; margin-bottom: 24px; line-height: 1.7; border-left: 4px solid #b87333; }
        label { display: block; font-size: 13px; color: #333; margin-bottom: 6px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; margin-bottom: 16px; box-sizing: border-box; }
        input:focus { border-color: #0f3d3e; outline: none; }
        button { width: 100%; background: #0f3d3e; color: #fff; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background .15s; }
        button:hover { background: #164c4d; }
        .errors { color: #dc2626; font-size: 13px; margin-bottom: 12px; }
        .hint { font-size: 12px; color: #999; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $training->title }}</h1>
        <p class="subtitle">Inscrivez-vous pour recevoir votre document officiel</p>

        <div class="info">
            📅 <strong>{{ $training->getPeriodLabel() }}</strong><br>
            📍 {{ $training->location ?? 'Lieu à préciser' }}<br>
            👨‍🏫 {{ $training->instructor_name ?? 'Formateur KMAA' }}<br>
            ⏱ {{ $training->days_count }} jour(s) — {{ $training->hours_count ?? '?' }} heures<br>
            📄 Document : <strong>{{ $training->getCertificateTypeLabel() }}</strong>
        </div>

        <form method="POST" action="{{ route('presential.register', $training->registration_token) }}">
            @csrf

            <label>Nom complet *</label>
            <input type="text" name="name" required value="{{ old('name') }}">

            <label>Adresse email *</label>
            <input type="email" name="email" required value="{{ old('email') }}">

            <label>Téléphone (optionnel)</label>
            <input type="tel" name="phone" value="{{ old('phone') }}">

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $e) • {{ $e }}<br>@endforeach
                </div>
            @endif

            <button type="submit">Recevoir mon document →</button>
        </form>

        <p class="hint">Vos informations ne seront utilisées que pour la génération du document.</p>
    </div>
</body>
</html>