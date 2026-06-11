<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundLog extends Model
{
    protected $table = 'refund_logs' ;

    protected $fillable = [
        'refund_id', 'admin_id', 'action', 'note',
    ];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
