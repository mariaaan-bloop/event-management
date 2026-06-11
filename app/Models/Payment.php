<?php

namespace App\Models;

use App\Support\StorageImage;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments' ;

    protected $fillable = [
        'event_registration_id', 'payment_method_id',
        'payment_code', 'amount', 'status',
        'proof_image', 'paid_at', 'admin_note',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Auto-generate kode pembayaran
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            $payment->payment_code =
                'PAY-' . strtoupper(substr(uniqid(), -6));
        });
    }

    // ===== RELATIONSHIPS =====

    public function eventRegistration()
    {
        return $this->belongsTo(EventRegistration::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    // Shortcut ke event via registration
    public function event()
    {
        return $this->hasOneThrough(
            Event::class,
            EventRegistration::class,
            'id',              // FK di event_registrations
            'id',              // FK di events
            'event_registration_id', // local key di payments
            'event_id'         // local key di event_registrations
        );
    }

    // ===== HELPERS =====

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function getProofImageUrlAttribute(): ?string
    {
        return StorageImage::url($this->proof_image);
    }
}
