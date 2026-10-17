<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UserResource::sendPasswordLinkAction(),
            DeleteAction::make()
                ->hidden(fn (): bool => $this->record->is(auth()->user())),
        ];
    }

    // AuthenticateSession logs out sessions whose stored hash no longer
    // matches, so keep the current one valid when editing yourself.
    protected function afterSave(): void
    {
        if ($this->record->is(auth()->user()) && request()->hasSession()) {
            request()->session()->put([
                'password_hash_'.Filament::getAuthGuard() => $this->record->getAuthPassword(),
            ]);
        }
    }
}
