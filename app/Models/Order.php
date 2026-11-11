<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'course_id', 'type',
        'reference', 'invoice_number',
        'subtotal', 'discount', 'tva', 'total', 'currency', 'promo_code',
        'payment_method', 'payment_method_label', 'transaction_id',
        'customer_name', 'customer_email',
        'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tva' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()   { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }

    // Helpers
    public static function generateReference(): string
    {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('KMAA-%d-%06d', $year, $count);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('FAC-%d-%05d', $year, $count);
    }
}