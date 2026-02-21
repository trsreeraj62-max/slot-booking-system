<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Service extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'store_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'status',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}
