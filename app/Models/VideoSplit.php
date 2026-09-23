<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VideoSplit extends Model
{
    protected $fillable = ['chapter_id', 'original_path', 'segment_minutes', 'status', 'error_message'];
    public function chapter() { return $this->belongsTo(Chapter::class); }
}