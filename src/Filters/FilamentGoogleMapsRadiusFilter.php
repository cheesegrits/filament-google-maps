<?php

namespace Cheesegrits\FilamentGoogleMaps\Filters;

use Cheesegrits\FilamentGoogleMaps\Fields\FilamentGoogleGeocomplete;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Filament\Resources\Form;
use Filament\Tables\Filters\BaseFilter;


class FilamentGoogleMapsRadiusFilter extends BaseFilter
{
	protected string|Closure|null $latitude = null;

	protected string|Closure|null $longitude = null;

	protected bool|Closure|null $kilometers = false;

	protected bool|Closure|null $selectUnit = false;

	protected string|Closure|null $section = null;


	protected function setUp(): void
	{
		parent::setUp();
	}

	public function apply(Builder $query, array $data = []): Builder
	{
		$latitude  = $data['latitude'] ?? null;
		$longitude = $data['longitude'] ?? null;
		$distance  = $data['radius'] ?? null;


		if ($latitude && $longitude && $distance)
		{
			$kilometers = $this->getKilometers();

			if ($this->getSelectUnit())
			{
				$kilometers = ($data['unit'] ?? null) === 'k';
			}

			$latName = $this->getLatitude();
			$lonName = $this->getLongitude();

//			$sql = "((ACOS(SIN(? * PI() / 180) * SIN(" . $latName . " * PI() / 180) + COS(? * PI() / 180) * COS(" .
//				$latName . " * PI() / 180) * COS((? - " . $lonName . ") * PI() / 180)) * 180 / PI()) * 60 * ?) as distance";

			$sql = "((ACOS(SIN($latitude * PI() / 180) * SIN(" . $latName . " * PI() / 180) + COS($latitude * PI() / 180) * COS(" .
				$latName . " * PI() / 180) * COS(($longitude - " . $lonName . ") * PI() / 180)) * 180 / PI()) * 60 * %f) < $distance";

			$sql = sprintf($sql, $kilometers ? (1.1515 * 1.609344) : 1.1515);

			$query->whereIn(
				$query->getModel()->getKeyName(),
				function ($builder) use ($latitude, $longitude, $sql, $distance, $query) {
					$builder->select($query->getModel()->getKeyName())
						->from($query->getModel()->getTable());
					$builder->whereRaw($sql);
				}
			);
		}

		return $query;
	}

	public function getFormSchema(): array
	{
		$form = [
			FilamentGoogleGeocomplete::make('geocomplete')
				->label('Address')
				->filterName($this->getName())
				->lazy(),
			TextInput::make('radius')
				->numeric()
				->lazy(),
			Hidden::make('latitude'),
			Hidden::make('longitude'),
		];

		if ($this->getSelectUnit())
		{
			$form = array_merge($form, [
				Select::make('unit')
					->options([
						'm' => 'Miles',
						'k' => 'Kilometers',
					])
					->default(
						$this->getKilometers() ? 'k' : 'm'
					),
			]);
		}

		if ($this->getSection())
		{
			$form = [
				Section::make($this->getSection())->schema($form),
			];
		}

		return $form;
	}

	public function kilometers(bool|Closure $kilometers = true): static
	{
		$this->kilometers = $kilometers;

		return $this;
	}

	public function getKilometers(): string
	{
		return $this->evaluate($this->kilometers);
	}

	public function selectUnit(bool|Closure $selectUnit = true): static
	{
		$this->selectUnit = $selectUnit;

		return $this;
	}

	public function getSelectUnit(): string
	{
		return $this->evaluate($this->selectUnit);
	}

	public function latitude(string|Closure|null $name): static
	{
		$this->latitude = $name;

		return $this;
	}

	public function getLatitude(): string
	{
		return $this->evaluate($this->latitude);
	}

	public function longitude(string|Closure|null $name): static
	{
		$this->longitude = $name;

		return $this;
	}

	public function getLongitude(): string
	{
		return $this->evaluate($this->longitude);
	}

	public function section(string|Closure|null $section): static
	{
		$this->section = $section;

		return $this;
	}

	public function getSection(): string|null
	{
		return $this->evaluate($this->section);
	}
}
