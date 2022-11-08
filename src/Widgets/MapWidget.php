<?php

namespace Cheesegrits\FilamentGoogleMaps\Widgets;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Widgets;

class MapWidget extends Widgets\Widget
{
	use Widgets\Concerns\CanPoll;

	protected ?array $cachedData = null;

	public string $dataChecksum;

	public ?string $filter = null;

	protected static ?string $heading = null;

	protected static ?string $maxHeight = null;

	protected static ?array $options = null;

	protected static ?int $precision = 8;

	protected static ?bool $clustering = true;

	protected static ?bool $fitToBounds = true;

	protected static ?int $zoom = null;

	protected static array $layers = [];

	protected static string $view = 'filament-google-maps::widgets.filament-google-maps-widget';

	public array $controls = [
		'mapTypeControl'    => true,
		'scaleControl'      => true,
		'streetViewControl' => true,
		'rotateControl'     => true,
		'fullscreenControl' => true,
		'searchBoxControl'  => false,
		'zoomControl'       => true,
	];

	private array $mapConfig = [
		'draggable'  => false,
		'center'     => [
			'lat' => 15.3419776,
			'lng' => 44.2171392,
		],
		'zoom'       => 8,
		'fit'        => true,
		'gmaps'      => '',
		'clustering' => true,
	];

	public function mount()
	{
		$this->dataChecksum = md5('{}');
	}

	protected function generateDataChecksum(): string
	{
		return md5(json_encode($this->getCachedData()));
	}

	protected function getCachedData(): array
	{
		return $this->cachedData ??= $this->getData();
	}

	protected function getData(): array
	{
		return [];
	}

	protected function getFilters(): ?array
	{
		return null;
	}

	protected function getZoom(): ?int
	{
		return static::$zoom ?? 8;
	}

	protected function getHeading(): ?string
	{
		return static::$heading;
	}

	protected function getMaxHeight(): ?string
	{
		return static::$maxHeight;
	}

	protected function getOptions(): ?array
	{
		return static::$options;
	}

	protected function getClustering(): ?bool
	{
		return static::$clustering;
	}

	protected function getFitToBounds(): ?bool
	{
		return static::$fitToBounds;
	}

	protected function getLayers(): array
	{
		return static::$layers;
	}

	public function getMapConfig(): string
	{
		$gmaps = 'https://maps.googleapis.com/maps/api/js'
			. '?key=' . config('filament-google-maps.key')
			. '&libraries=places&v=weekly'
			. '&language=' . app()->getLocale();

		return json_encode(
			array_merge($this->mapConfig, [
				'clustering' => self::getClustering(),
				'layers'     => $this->getLayers(),
				'zoom'       => $this->getZoom(),
				'controls'   => $this->controls,
				'fit'        => $this->getFitToBounds(),
				'gmaps'      => $gmaps,
			])
		);
	}

	public function updateMapData()
	{
		$newDataChecksum = $this->generateDataChecksum();

		if ($newDataChecksum !== $this->dataChecksum)
		{
			$this->dataChecksum = $newDataChecksum;

			$this->emitSelf('updateMapData', [
				'data' => $this->getCachedData(),
			]);
		}
	}

	public function updatedFilter(): void
	{
		$newDataChecksum = $this->generateDataChecksum();

		if ($newDataChecksum !== $this->dataChecksum)
		{
			$this->dataChecksum = $newDataChecksum;

			$this->emitSelf('filterChartData', [
				'data' => $this->getCachedData(),
			]);
		}
	}

	public function hasJs(): bool
	{
		return true;
	}

	public function jsUrl(): string
	{
		$manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);

		return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps-widget.js']);
	}

	public function hasCss(): bool
	{
		return false;
	}

	public function cssUrl(): string
	{
		$manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);

		return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps-widget.css']);
	}
}
