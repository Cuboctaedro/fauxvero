<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                ...self::passwordFields(),
            ]);
    }

    /**
     * Temporary: lets admins set passwords until a mail provider is set up.
     * Remove this method and its call to rely on emailed password links only.
     */
    protected static function passwordFields(): array
    {
        return [
            TextInput::make('password')
                ->password()
                ->revealable()
                ->rule(Password::default())
                ->confirmed()
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText(fn (string $operation): string => $operation === 'create'
                    ? 'Leave empty to email the user a link to set their own password.'
                    : 'Leave empty to keep the current password.'),

            TextInput::make('password_confirmation')
                ->label('Confirm password')
                ->password()
                ->revealable()
                ->required(fn (Get $get): bool => filled($get('password')))
                ->dehydrated(false),
        ];
    }
}
