<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Closure;
use Exception;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanConcealComponents;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use JsonException;

class FilamentGoogleGeocomplete extends TextInput
{
	protected string $view = 'filament-google-maps::fields.filament-google-geocomplete';

	protected int $precision = 8;

	protected Closure|string|null $filterName = null;

	public function filterName(Closure|string $name): static
	{
		$this->filterName = $name;

		return $this;
	}

	public function getFilterName(): string|null
	{
		$name = $this->evaluate($this->filterName);

		if ($name)
		{
			return 'tableFilters.' . $name;
		}

		return null;
	}

	/**
	 * Create json configuration string
	 * @return string
	 */
	public function getMapConfig(): string
	{
		$gmaps = 'https://maps.googleapis.com/maps/api/js'
			. '?key=' . config('filament-google-maps.key')
			. '&libraries=places'
			. '&v=weekly'
			. '&language=' . app()->getLocale();

		$config = json_encode([
			'filterName' => $this->getFilterName(),
			'statePath' => $this->getStatePath(),
			'gmaps'     => $gmaps,
		]);

		//ray($config);

		return $config;
	}

	public function hasJs(): bool
	{
		return true;
	}

	public function jsUrl(): string
	{
		$manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);

		return url($manifest['/cheesegrits/filament-google-maps/filament-google-geocomplete.js']);
	}

	public function hasCss(): bool
	{
		return false;
	}

	public function cssUrl(): string
	{
		$manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);

		return url($manifest['/cheesegrits/filament-google-maps/filament-google-geocomplete.css']);
	}

}
