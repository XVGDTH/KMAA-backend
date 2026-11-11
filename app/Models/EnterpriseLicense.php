<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EnterpriseLicense extends Model
{
    protected $fillable = [
        'uuid', 'course_id', 'plan_id', 'owner_id',
        'company_name', 'company_email', 'company_phone',
        'company_address', 'company_tax_id',
        'seats_total', 'seats_used', 'price_per_seat',
        'total_paid', 'discount_applied', 'promo_code',
        'status', 'purchased_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($license) {
            if (empty($license->uuid)) {
                $license->uuid = (string) Str::uuid();
            }
        });
    }

    public function course()   { return $this->belongsTo(Course::class); }
    public function plan()     { return $this->belongsTo(EnterprisePricingPlan::class, 'plan_id'); }
    public function owner()    { return $this->belongsTo(User::class, 'owner_id'); }
    public function members()  { return $this->hasMany(EnterpriseLicenseMember::class, 'license_id'); }

    public function seatsRemaining(): int
    {
        return max(0, $this->seats_total - $this->seats_used);
    }
}