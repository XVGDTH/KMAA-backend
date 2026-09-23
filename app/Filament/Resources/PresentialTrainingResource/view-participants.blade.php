<x-filament-panels::page>
    <div class="space-y-6">

        {{-- En-tête formation --}}
        <x-filament::section>
            <h2 class="text-xl font-bold">{{ $record->title }}</h2>
            <p class="text-sm text-gray-600">
                📅 {{ $record->getPeriodLabel() }} · ⏱ {{ $record->days_count }} jour(s) ·
                📍 {{ $record->location ?? '—' }}
            </p>
            <p class="mt-2 text-sm">
                🔗 Lien d'inscription :
                <code class="bg-gray-100 px-2 py-1 rounded">{{ $record->getRegistrationUrl() }}</code>
            </p>
        </x-filament::section>

        {{-- Liste participants --}}
        <x-filament::section heading="Participants ({{ $record->registered_count }})">
            <div class="space-y-2">
                @forelse ($record->participants as $p)
                    <div class="flex items-center justify-between p-3 border rounded
                                {{ $p->revoked ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200' }}">
                        <div>
                            <strong>{{ $p->name }}</strong>
                            <div class="text-sm text-gray-600">{{ $p->email }}</div>
                            <div class="text-xs text-gray-400">
                                Inscrit le {{ $p->registered_at?->format('d/m/Y H:i') }}
                                @if ($p->certificate_sent_at)
                                    · Email envoyé {{ $p->certificate_sent_at->diffForHumans() }}
                                @endif
                                @if ($p->revoked)
                                    · <span class="text-red-600">RETIRÉ — {{ $p->revocation_reason }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-2">
                            @if (! $p->revoked)
                                <x-filament::button
                                    size="sm"
                                    color="info"
                                    wire:click="resendEmail({{ $p->id }})"
                                >
                                    Renvoyer l'email
                                </x-filament::button>

                                <x-filament::button
                                    size="sm"
                                    color="danger"
                                    x-on:click="
                                        const reason = prompt('Justificatif obligatoire :');
                                        if (reason) $wire.revoke({{ $p->id }}, reason);
                                    "
                                >
                                    Retirer
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Aucun participant inscrit pour le moment.</p>
                @endforelse
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>