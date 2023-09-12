<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Models;

use Cheesegrits\FilamentGoogleMaps\Tests\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_id',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }
}
