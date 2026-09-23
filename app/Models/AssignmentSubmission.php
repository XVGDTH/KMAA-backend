<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'user_id', 'sequence_id', 'course_id',
        'file_path', 'comment', 'status', 'grade', 'instructor_feedback',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function sequence(){ return $this->belongsTo(Sequence::class); }
    public function course()  { return $this->belongsTo(Course::class); }
}