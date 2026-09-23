<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { margin: 0; padding: 0; background: #f4f5f8; font-family: Georgia, 'Times New Roman', serif; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: #0f3d3e; padding: 40px 30px; text-align: center; }
        .header .brand { color: #d4af37; font-size: 22px; font-weight: bold; letter-spacing: 4px; }
        .header .tagline { color: #f3e6c2; font-size: 11px; letter-spacing: 3px; margin-top: 6px; }
        .intro { padding: 30px 30px 0; color: #0f3d3e; font-size: 17px; font-style: italic; }
        .content { padding: 20px 30px 30px; color: #3d3d3d; font-size: 15px; line-height: 1.8; }
        .content h2 { color: #0f3d3e; font-size: 18px; }
        .footer { background: #070d1f; padding: 25px 30px; text-align: center; }
        .footer p { color: #94a3b8; font-size: 11px; margin: 0 0 10px; }
        .footer a { color: #d4af37; font-size: 11px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">KMAA</div>
        <div class="tagline">KMAA BRIEF — LA NEWSLETTER</div>
    </div>

    @if ($campaign->intro)
        <p class="intro">{{ $campaign->intro }}</p>
    @endif

    <div class="content">
        {!! $campaign->content !!}   {{-- HTML validé par l'admin via le RichEditor --}}
    </div>

    <div class="footer">
        <p>
            Vous recevez cet email car vous êtes inscrit à la newsletter KMAA.<br>
            © {{ date('Y') }} KMAA Academy — Apprendre. Expérimenter. Transformer.
        </p>

        {{-- ✅ LIEN DE DÉSINSCRIPTION PERSONNALISÉ (le système déjà en place) --}}
        <a href="{{ config('app.url') }}/newsletter?token={{ $subscriber->unsubscribe_token }}">
            Se désinscrire de la newsletter
        </a>
    </div>

</div>
</body>
</html>