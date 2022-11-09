<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Models;

use Cheesegrits\FilamentGoogleMaps\Tests\Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
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
        'full_address',
        'processed',
    ];

    protected $casts = [
        'processed' => 'bool',
    ];

    /**
     * Insert this code in your model, overwriting any existing $appends array (we already merged any existing
     * append attributes from your model here).
     *
     * The 'lat' and 'lng' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The 'location' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */

    protected $appends = [
        'location',
    ];

    /**
     * Returns the 'lat' and 'lng' attributes as the computed 'location' attribute,
     * as a standard Google Maps style Point array with 'lat' and 'lng' attributes, JSON encoded.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $appends array.
     *
     * @return string
     */
    function getLocationAttribute(): string
    {
        return json_encode([
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ]);
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'lat' and 'lng' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $appends array.
     *
     * @param array $location
     * @return void
     */
    function setLocationAttribute(array $location): void
    {
        $this->attributes['lat'] = $location['lat'];
        $this->attributes['lng'] = $location['lng'];
    }

	protected static function newFactory()
	{
		return LocationFactory::new();
	}

}
