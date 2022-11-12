<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Models;

use Cheesegrits\FilamentGoogleMaps\Tests\Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationGuarded extends Model
{
    use HasFactory;

    protected $guarded = [
		'id',
	    'location'
    ];

	protected $casts = [
        'processed' => 'bool',
    ];

}
