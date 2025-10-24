<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PaletStokResource\Pages;
use App\Models\PM\PaletStok;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaletStokResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PaletStok::class;
    protected static ?string $pluralModelLabel = 'Stok Palet';
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    /* ======================
     * NAV & ACCESS GUARDS
     * ====================== */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_palet::stok') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_palet::stok') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_palet::stok') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_palet::stok') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_palet::stok') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_palet::stok') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Detail Stok Palet')->schema([
                Select::make('gudang_id')
                    ->label('Plant Palet')
                    ->relationship('gudangPalet', 'nama_gudang')
                    ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_gudang}] {$record->nama_gudang}")
                    ->searchable(['kode_gudang', 'nama_gudang'])
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                    ->helperText('Pilih plant untuk mencatat stok awal. Plant tidak bisa diubah saat edit.'),

                TextInput::make('qty_rfi')->label('Jumlah RFI (Ready For Issue)')->required()->numeric()->minValue(0)->default(0),
                TextInput::make('qty_tbr')->label('Jumlah TBR (To Be Repaired)')->required()->numeric()->minValue(0)->default(0),
                TextInput::make('qty_ber')->label('Jumlah BER (Beyond Economical Repair)')->required()->numeric()->minValue(0)->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gudangPalet.nama_gudang')->label('Nama Plant')->searchable()->sortable(),
                TextColumn::make('qty_rfi')->label('Stok RFI')->numeric()->sortable(),
                TextColumn::make('qty_tbr')->label('Stok TBR')->numeric()->sortable(),
                TextColumn::make('qty_ber')->label('Stok BER')->numeric()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => auth()->user()?->can('view_palet::stok') ?? false),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->can('update_palet::stok') ?? false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_palet::stok') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->can('delete_any_palet::stok') ?? false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaletStoks::route('/'),
            'create' => Pages\CreatePaletStok::route('/create'),
            'edit'   => Pages\EditPaletStok::route('/{record}/edit'),
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
