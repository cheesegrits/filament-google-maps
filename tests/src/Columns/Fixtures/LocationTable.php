<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures;

use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class LocationTable extends Component implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('lat'),
            Tables\Columns\TextColumn::make('lng'),
            Tables\Columns\TextColumn::make('street'),
            Tables\Columns\TextColumn::make('city'),
            Tables\Columns\TextColumn::make('state'),
            Tables\Columns\TextColumn::make('zip'),
            //			Tables\Columns\TextColumn::make('processed')
            //				->hidden(),
            //			Tables\Columns\TextColumn::make('formatted_address'),
            MapColumn::make('location'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            //			Tables\Filters\TernaryFilter::make('processed'),
            RadiusFilter::make('radius')
                ->latitude('lat')
                ->longitude('lng')
                ->selectUnit(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
        ];
    }

    protected function getTableActions(): array
    {
        return [
            //			Tables\Actions\EditAction::make(),
            //			Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            //BulkActionGroup::make([
            //			Tables\Actions\DeleteBulkAction::make(),
            //]),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Location::query();
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    public function render(): View
    {
        return view('columns.fixtures.table');
    }
}
