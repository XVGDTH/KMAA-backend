<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks'; 
    protected $fillable = ['user_id', 'sequence_id', 'course_id', 'comment', 'rating'];
}