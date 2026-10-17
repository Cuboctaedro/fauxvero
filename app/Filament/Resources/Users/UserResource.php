<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Notifications\SetPassword;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Emails the user a link to Filament's reset page. Creates the token
     * directly so the public form's throttle doesn't apply to admins.
     */
    public static function sendPasswordLink(User $user): void
    {
        $token = Password::broker(Filament::getAuthPasswordBroker())->createToken($user);

        $notification = new SetPassword($token);
        $notification->url = Filament::getResetPasswordUrl($token, $user);

        $user->notify($notification);
    }

    public static function sendPasswordLinkAction(): Action
    {
        return Action::make('sendPasswordLink')
            ->label('Send password link')
            ->icon(Heroicon::OutlinedEnvelope)
            ->requiresConfirmation()
            ->modalDescription(fn (User $record): string => 'Email '.$record->email.' a link to set a new password.')
            ->action(function (User $record): void {
                self::sendPasswordLink($record);

                Notification::make()
                    ->title('Password link sent to '.$record->email)
                    ->success()
                    ->send();
            });
    }
}
