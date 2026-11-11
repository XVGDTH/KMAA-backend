<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnterpriseLicenseMember extends Model
{
    protected $fillable = [
        'license_id', 'user_id', 'email', 'name', 'status',
        'invited_at', 'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'joined_at'  => 'datetime',
        ];
    }

    public function license() { return $this->belongsTo(EnterpriseLicense::class, 'license_id'); }
    public function user()    { return $this->belongsTo(User::class); }
}