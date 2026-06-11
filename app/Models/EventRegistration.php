<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventRegistration extends Model
{
    protected $table = 'event_registrations' ;

    protected $fillable = [
        'event_id', 'participant_id', 'status',
    ];

    // ===== RELATIONSHIPS =====

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    // 1 registrasi punya 1 payment (jika event berbayar)
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // 1 registrasi punya 1 refund (jika diajukan)
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    // ===== HELPERS =====

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    // Cek apakah pembayaran sudah diverifikasi
    public function isPaid(): bool
    {
        return $this->payment && $this->payment->status === 'approved';
    }
}
