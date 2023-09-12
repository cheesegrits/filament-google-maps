<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures;

use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Customer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class CustomerTable extends Component implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('location.name'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            //			Tables\Filters\TernaryFilter::make('processed'),
            RadiusFilter::make('radius')
                ->attribute('location.name')
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
        return Customer::query();
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
