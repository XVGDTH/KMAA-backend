<?php

namespace App\Jobs;

use App\Models\{Chapter, Sequence, VideoSplit};
use FFMpeg\FFProbe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SplitCourseVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(public VideoSplit $split) {}

    public function handle(): void
    {
        $this->split->update(['status' => 'en_cours']);

        try {
            // ----------------------------------------------------
            // ✅ FIX 1 : l'original est lu sur le disque LOCAL
            //    (là où le FileUpload l'a réellement déposé)
            // ----------------------------------------------------
            $originalFullPath = Storage::disk('local')->path($this->split->original_path);

            if (! file_exists($originalFullPath)) {
                throw new \RuntimeException("Fichier source introuvable : {$originalFullPath}");
            }

            $chapter = $this->split->chapter;

            $ffprobe = FFProbe::create([
                'ffmpeg.binaries'  => config('ffmpeg.ffmpeg.binaries'),
                'ffprobe.binaries' => config('ffmpeg.ffprobe.binaries'),
            ]);

            $durationSeconds = (int) $ffprobe->format($originalFullPath)->get('duration');
            $segmentSeconds  = $this->split->segment_minutes * 60;
            $segmentCount    = (int) ceil($durationSeconds / $segmentSeconds);

            // ----------------------------------------------------
            // ✅ Les segments sont écrits sur le disque PUBLIC
            //    (pour être servis via /storage/ aux apprenants)
            // ----------------------------------------------------
            $outputDir = 'videos-segments/' . $chapter->id . '-' . uniqid();
            Storage::disk('public')->makeDirectory($outputDir);
            $outputFullDir = Storage::disk('public')->path($outputDir);

            $startPosition = ($chapter->sequences()->max('position') ?? 0) + 1;

            for ($i = 0; $i < $segmentCount; $i++) {
                $start  = $i * $segmentSeconds;
                $length = min($segmentSeconds, $durationSeconds - $start);
                $segmentFile = "segment-{$i}.mp4";
                $segmentFullPath = $outputFullDir . '/' . $segmentFile;

                $process = new Process([
                    config('ffmpeg.ffmpeg.binaries'),
                    '-i', $originalFullPath,
                    '-ss', (string) $start,
                    '-t', (string) $length,
                    '-c', 'copy',
                    '-avoid_negative_ts', 'make_zero',
                    $segmentFullPath,
                    '-y',
                ]);
                $process->setTimeout(3600);
                $process->run();

                if (! $process->isSuccessful()) {
                    throw new \RuntimeException(
                        "Segment {$i} : " . $process->getErrorOutput()
                    );
                }

                Sequence::create([
                    'chapter_id' => $chapter->id,
                    'title'      => 'Partie ' . ($i + 1) . " / {$segmentCount}",
                    'type'       => 'video',
                    'duration'   => gmdate('i:s', $length),

                    // ✅ FIX 3 : chemin RELATIF (pas Storage::url qui
                    // produit http://localhost/... → 404 côté apprenant)
                    'video_url'  => '/storage/' . $outputDir . '/' . $segmentFile,

                    'position'   => $startPosition + $i,
                ]);
            }

            // Supprime l'original une fois découpé
            Storage::disk('local')->delete($this->split->original_path);

            $this->split->update(['status' => 'termine']);

        } catch (\Throwable $e) {
            $this->split->update([
                'status'        => 'echec',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}