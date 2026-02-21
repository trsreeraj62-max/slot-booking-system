<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TimeSlot extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'store_id',
        'service_id',
        'start_time',
        'end_time',
        'slot_date',
        'status',
        'locked_by',
        'lock_expires_at',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'lock_expires_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
