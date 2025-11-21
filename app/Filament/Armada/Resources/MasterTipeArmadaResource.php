<?php

namespace App\Filament\Armada\Resources;

use App\Filament\Armada\Resources\MasterTipeArmadaResource\Pages;
use App\Models\AM\MasterTipeArmada;
// 1. Import Interface
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
// 2. Import Trait dengan Alias untuk menghindari bentrok nama
use BezhanSalleh\FilamentShield\Traits\HasShieldPermissions as HasShieldPermissionsTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterTipeArmadaResource extends Resource implements HasShieldPermissions
{
    // 3. Gunakan Trait via Alias
    // use HasShieldPermissionsTrait;

    protected static ?string $model = MasterTipeArmada::class;

    protected static ?string $modelLabel = 'Tipe Armada';
    protected static ?string $pluralModelLabel = 'Master Tipe Armada';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tipe Armada')
                    ->schema([
                        Forms\Components\TextInput::make('kode_tipe')
                            ->label('Kode Tipe')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Contoh: 022'),

                        Forms\Components\TextInput::make('nama_tipe')
                            ->label('Nama Tipe Armada')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: TRONTON 30T'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_tipe')
                    ->label('Kode Tipe')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_tipe')
                    ->label('Tipe Armada')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterTipeArmadas::route('/'),
            'create' => Pages\CreateMasterTipeArmada::route('/create'),
            'edit' => Pages\EditMasterTipeArmada::route('/{record}/edit'),
        ];
    }
}
