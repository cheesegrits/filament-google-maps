<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources;

use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
//use App\Filament\Resources\LocationResource\RelationManagers;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(256),
                Forms\Components\TextInput::make('lat')
                    ->maxLength(32),
                Forms\Components\TextInput::make('lng')
                    ->maxLength(32),
                Forms\Components\TextInput::make('street')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip')
                    ->maxLength(255),
                Geocomplete::make('location')
                    ->isLocation(),
                //                    ->types(['airport'])
                //                    ->placeField('name')
                //                    ->isLocation()
                //                    ->reverseGeocode([
                //                        'city'   => '%L',
                //                        'zip'    => '%z',
                //                        'state'  => '%A1',
                //                        'street' => '%n %S',
                //                    ])
                //                    ->prefix('Choose:')
                //                    ->placeholder('Start typing and address ...')
                //                    ->maxLength(1024),
                //                Forms\Components\TextInput::make('formatted_address')
                //                    ->maxLength(1024),
                //                Map::make('location')
                //                    ->debug()
                //                    ->clickable()
                //                    ->layers([
                //                        'https://googlearchive.github.io/js-v2-samples/ggeoxml/cta.kml',
                //                    ])
                ////                    ->autocomplete('formatted_address')
                ////                    ->autocompleteReverse()
                //                    ->reverseGeocode([
                //                        'city' => '%L',
                //                        'zip' => '%z',
                //                        'state' => '%A1',
                //                        'street' => '%n %S',
                //                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('lat'),
                Tables\Columns\TextColumn::make('lng'),
                Tables\Columns\TextColumn::make('street'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('state'),
                Tables\Columns\TextColumn::make('zip'),
                Tables\Columns\TextColumn::make('formatted_address'),
                MapColumn::make('location'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('processed'),
                RadiusFilter::make('radius')
                    ->latitude('lat')
                    ->longitude('lng')
                    ->selectUnit(),
                //                    ->section('Radius Search'),
            ]
            )
            ->filtersLayout(Tables\Filters\Layout::Popover)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view'   => Pages\ViewLocation::route('/{record}'),
            'edit'   => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
