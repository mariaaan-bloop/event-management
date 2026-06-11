<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Refund extends Model
{
    protected $table = 'refunds' ;

    protected $fillable = [
        'payment_id', 'event_registration_id', 'user_id',
        'reason', 'description',
        'bank_name', 'account_number', 'account_holder',
        'amount', 'status', 'admin_note', 'transfer_proof', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function eventRegistration()
    {
        return $this->belongsTo(EventRegistration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(RefundLog::class);
    }

    // ===== HELPERS =====

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
