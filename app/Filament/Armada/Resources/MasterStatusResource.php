<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterStatusResource\Pages;
use App\Filament\Armada\Resources\MasterStatusResource\RelationManagers;
use App\Models\AM\MasterStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterStatusResource extends Resource
{
    protected static ?string $model = MasterStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 6;
    protected static ?string $modelLabel = 'Status';
    protected static ?string $pluralModelLabel = 'Master Status';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_status')
                    ->label('Nama Status')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_status')
                    ->label('Nama Status')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListMasterStatuses::route('/'),
            'create' => Pages\CreateMasterStatus::route('/create'),
            'edit' => Pages\EditMasterStatus::route('/{record}/edit'),
        ];
    }
}
