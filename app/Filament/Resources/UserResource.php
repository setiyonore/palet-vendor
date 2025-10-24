<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Sistem';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?string $modelLabel       = 'User';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Profil')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(190),
                ])->columns(2),

            Forms\Components\Section::make('Keamanan')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null) // biarkan casting hashed yang handle
                        ->required(fn(string $context) => $context === 'create')
                        ->rule(PasswordRule::default())
                        ->same('password_confirmation'),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->dehydrated(false) // tidak disimpan
                        ->required(fn(string $context) => $context === 'create'),
                ])->columns(2),

            Forms\Components\Section::make('Akses')
                ->schema([
                    // Assign Roles (Spatie)
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name') // penting: butuh trait HasRoles
                        ->multiple()
                        ->preload()
                        ->searchable(),
                    // (Opsional) assign permission langsung
                    // Forms\Components\Select::make('permissions')
                    //     ->label('Permissions')
                    //     ->relationship('permissions', 'name')
                    //     ->multiple()
                    //     ->preload()
                    //     ->searchable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter Role')
                    ->relationship('roles', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                        // Cegah user menghapus dirinya sendiri
                        if (auth()->id() === $record->id) {
                            Notification::make()
                                ->title('Tidak bisa menghapus diri sendiri.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            // Cegah termasuk current user
                            if (collect($records)->contains(fn($r) => $r->id === auth()->id())) {
                                Notification::make()
                                    ->title('Bulk delete dibatalkan: daftar berisi akun Anda sendiri.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
