<?php
return [
    'ffmpeg.binaries'  => env('FFMPEG_BINARY', 'ffmpeg'),
    'ffprobe.binaries' => env('FFPROBE_BINARY', 'ffprobe'),
    'timeout' => 3600,
    'ffmpeg.threads' => 4,
];
