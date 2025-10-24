<?php

/**
 * File: app/Filament/PaletManagement/Resources/MasterVendorResource.php
 */

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\MasterVendorResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Models\PM\MasterVendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterVendorResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = MasterVendor::class;
    protected static ?string $pluralModelLabel = 'Master Vendor(Ekspeditur)';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('kode_fios')
                            ->label('Kode FIOS')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('kode_vendor_si')
                            ->label('Kode Vendor SI')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                    ]),

                Forms\Components\TextInput::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_fios')->label('Kode FIOS')->searchable(),
                Tables\Columns\TextColumn::make('kode_vendor_si')->label('Kode Vendor SI')->searchable(),
                Tables\Columns\TextColumn::make('nama_vendor')->label('Nama Vendor')->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => Pages\ListMasterVendors::route('/'),
            'create' => Pages\CreateMasterVendor::route('/create'),
            'edit' => Pages\EditMasterVendor::route('/{record}/edit'),
        ];
    }
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
}
