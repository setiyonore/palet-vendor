<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\GudangPaletResource\Pages;
use App\Models\PM\GudangPalet;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GudangPaletResource extends Resource
{
    protected static ?string $model = GudangPalet::class;

    protected static ?string $modelLabel = 'Plant Palet';
    protected static ?string $pluralModelLabel = 'Plant Palet';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    /* ======================
     * NAV & ACCESS GUARDS
     * ====================== */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_gudang::palet') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_gudang::palet') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_gudang::palet') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_gudang::palet') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_gudang::palet') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_gudang::palet') ?? false;
    }

    public static function getPermissionPrefixes(): array
    {
        // biar konsisten dengan Shield
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('kode_gudang')
                    ->label('Kode Plant')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('nama_gudang')
                    ->label('Nama Plant')
                    ->required()
                    ->maxLength(255),

                Textarea::make('lokasi')
                    ->label('Alamat / Lokasi Plant')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_gudang')->label('Kode Plant')->searchable()->sortable(),
                TextColumn::make('nama_gudang')->label('Nama Plant')->searchable()->sortable(),
                TextColumn::make('lokasi')->limit(50)->tooltip('Lihat selengkapnya'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => auth()->user()?->can('view_gudang::palet') ?? false),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->can('update_gudang::palet') ?? false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_gudang::palet') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->can('delete_any_gudang::palet') ?? false),
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
            'index'  => Pages\ListGudangPalets::route('/'),
            'create' => Pages\CreateGudangPalet::route('/create'),
            'edit'   => Pages\EditGudangPalet::route('/{record}/edit'),
        ];
    }
}
