<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $table = 'participants' ;

    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'age', 'location',
    ];

    // ===== RELATIONSHIPS =====

    // Participant milik user mana
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Participant ini mendaftar ke event mana saja
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    // Shortcut ke events yang diikuti participant ini
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_registrations')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    // ===== HELPERS =====

    // Cek apakah participant sudah terdaftar di event tertentu
    public function isRegisteredTo(int $eventId): bool
    {
        return $this->eventRegistrations()
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }
}
