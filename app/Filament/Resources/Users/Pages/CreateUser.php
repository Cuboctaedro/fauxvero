<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $sendsPasswordLink = false;

    // Without a password the user chooses their own through an emailed link,
    // so the stored one is random and never shown.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            $this->sendsPasswordLink = true;
            $data['password'] = Str::password();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->sendsPasswordLink) {
            UserResource::sendPasswordLink($this->record);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->sendsPasswordLink
            ? 'Invitation sent to '.$this->record->email
            : 'User created';
    }
}
