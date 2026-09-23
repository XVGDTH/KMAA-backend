<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification — KMAA</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background: #f4f1ea;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: #fff;
            max-width: 520px;
            width: 100%;
            padding: 32px;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            text-align: center;
            border-top: 6px solid #0f3d3e;
        }

        .card--revoked {
            border-top-color: #b3261e;
        }

        .badge {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .badge--valid   { background: #e6f4ea; color: #137333; }
        .badge--revoked { background: #fef3c7; color: #92400e; }
        .badge--invalid { background: #fde8e8; color: #b3261e; }

        h1 {
            font-size: 20px;
            color: #0f3d3e;
            margin: 16px 0;
        }

        .card--revoked h1 { color: #b3261e; }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0f3d3e, #164c4d);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        dl {
            text-align: left;
            font-size: 14px;
            line-height: 1.8;
            margin: 18px 0;
        }

        dt {
            color: #777;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 12px;
        }

        dd {
            margin: 0 0 10px;
            font-weight: bold;
            color: #2b2b2b;
        }

        .ref {
            letter-spacing: 1px;
            font-family: monospace;
            font-size: 13px;
        }

        .source-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
        }

        .source-tag--online     { background: #eef2ff; color: #4338ca; }
        .source-tag--presential { background: #dbeafe; color: #1e40af; }

        .revoked-note {
            background: #fef3c7;
            color: #92400e;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 16px;
            text-align: left;
            border-left: 4px solid #f59e0b;
        }

        .footer {
            font-size: 11px;
            color: #999;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }

        .download-btn {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 20px;
            background: #0f3d3e;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s;
        }

        .download-btn:hover { background: #164c4d; }
    </style>
</head>
<body>

    <div class="card {{ $certificate && $certificate->revoked ? 'card--revoked' : '' }}">

        @if ($certificate)

            {{-- ============================================================ --}}
            {{-- CAS 1 : Certificat révoqué --}}
            {{-- ============================================================ --}}
            @if ($certificate->revoked)

                <span class="badge badge--revoked">⚠️ Document révoqué</span>

                <h1>Ce document n'est plus valide</h1>

                <dl>
                    <dt>Référence</dt>
                    <dd class="ref">{{ $certificate->reference }}</dd>

                    <dt>Délivré à</dt>
                    <dd>
                        {{ $certificate->student_name
                            ?? $certificate->enrollment?->user?->name
                            ?? '—' }}
                    </dd>

                    <dt>Formation</dt>
                    <dd>
                        {{ $certificate->course_title
                            ?? $certificate->enrollment?->course?->title
                            ?? '—' }}
                    </dd>

                    <dt>Révoqué le</dt>
                    <dd>{{ $certificate->revoked_at?->format('d/m/Y') ?? '—' }}</dd>
                </dl>

                <div class="revoked-note">
                    <strong>Raison de la révocation :</strong><br>
                    {{ $certificate->revocation_reason ?? 'Non précisée' }}
                </div>

            @else

                {{-- ============================================================ --}}
                {{-- CAS 2 : Certificat valide --}}
                {{-- ============================================================ --}}

                <span class="badge badge--valid">✔ Document authentique</span>

                <div class="avatar" style="margin-top: 16px;">
                    @php
                        $name = $certificate->student_name
                            ?? $certificate->enrollment?->user?->name
                            ?? '?';
                        echo mb_strtoupper(mb_substr($name, 0, 1));
                    @endphp
                </div>

                <h1>
                    {{ $certificate->type === 'attestation' ? 'Attestation' : 'Certificat' }}
                    vérifié
                </h1>

                <dl>
                    <dt>Référence</dt>
                    <dd class="ref">{{ $certificate->reference }}</dd>

                    <dt>Délivré à</dt>
                    <dd>
                        {{ $certificate->student_name
                            ?? $certificate->enrollment?->user?->name
                            ?? '—' }}
                    </dd>

                    <dt>Formation</dt>
                    <dd>
                        {{ $certificate->course_title
                            ?? $certificate->enrollment?->course?->title
                            ?? '—' }}
                    </dd>

                    <dt>Délivré le</dt>
                    <dd>{{ $certificate->issued_at->format('d/m/Y') }}</dd>

                    <dt>Volume horaire</dt>
                    <dd>{{ $certificate->duration_hours }} heures</dd>

                    <dt>Source</dt>
                    <dd>
                        @if ($certificate->presential_training_id)
                            <span class="source-tag source-tag--presential">📍 Formation présentielle</span>
                        @else
                            <span class="source-tag source-tag--online">💻 Formation en ligne</span>
                        @endif
                    </dd>
                </dl>

                <a href="{{ route('certificates.download', $certificate->uuid) }}"
                   class="download-btn"
                   target="_blank"
                   rel="noopener">
                    ⬇ Télécharger le document
                </a>

            @endif

        @else

            {{-- ============================================================ --}}
            {{-- CAS 3 : Aucun certificat trouvé --}}
            {{-- ============================================================ --}}

            <span class="badge badge--invalid">✖ Document introuvable</span>

            <h1>Certificat non reconnu</h1>

            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                Aucun document KMAA ne correspond à ce code.<br>
                Il pourrait être falsifié ou mal saisi.
            </p>

            <p style="color: #999; font-size: 12px; margin-top: 20px;">
                Si vous pensez qu'il s'agit d'une erreur,<br>
                contactez-nous : <strong>contact@kmaa-ac.com</strong>
            </p>

        @endif

        <div class="footer">
            KMAA AUDIT ET CONSEIL — kmaa-ac.com
        </div>

    </div>

</body>
</html>