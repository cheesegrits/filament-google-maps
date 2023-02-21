<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationFillable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'street',
        'city',
        'state',
        'zip',
        'formatted_address',
        'processed',
    ];

    protected $casts = [
        'processed' => 'bool',
    ];
}
