<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Notifications\SetPassword;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

describe('password recovery', function () {
    it('links to password recovery from the login page', function () {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(route('filament.admin.auth.password-reset.request'));
    });

    it('emails a reset link to an existing user', function () {
        $user = User::factory()->create();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });

    it('resets the password with a valid token', function () {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
            ->fillForm([
                'password' => 'new-Secret-123',
                'passwordConfirmation' => 'new-Secret-123',
            ])
            ->call('resetPassword')
            ->assertHasNoFormErrors();

        expect(Hash::check('new-Secret-123', $user->fresh()->password))->toBeTrue();
    });
});

describe('user management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    });

    it('lists users', function () {
        $other = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$this->admin, $other]);
    });

    it('invites a new user to set their own password', function () {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Maria', 'email' => 'maria@example.com'])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'maria@example.com')->firstOrFail();

        expect($user->name)->toBe('Maria');
        Notification::assertSentTo($user, SetPassword::class, function (SetPassword $notification) use ($user) {
            return Password::broker()->tokenExists($user, $notification->token);
        });
    });

    it('rejects a duplicate email', function () {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Copy', 'email' => $this->admin->email])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    });

    it('creates a user with a password set by the admin', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Nikos',
                'email' => 'nikos@example.com',
                'password' => 'Secret-123',
                'password_confirmation' => 'Secret-123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'nikos@example.com')->firstOrFail();

        expect(Hash::check('Secret-123', $user->password))->toBeTrue();
        Notification::assertNothingSent();
    });

    it('requires the password confirmation to match', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Nikos',
                'email' => 'nikos@example.com',
                'password' => 'Secret-123',
                'password_confirmation' => 'Other-123',
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'confirmed']);
    });

    it('changes a password from the edit page', function () {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['password' => 'Changed-123', 'password_confirmation' => 'Changed-123'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(Hash::check('Changed-123', $user->fresh()->password))->toBeTrue();
    });

    it('keeps the password when the field is left empty', function () {
        $user = User::factory()->create();
        $hash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Renamed'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($user->fresh())
            ->name->toBe('Renamed')
            ->password->toBe($hash);
    });

    it('sends a password link on request', function () {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->callAction(TestAction::make('sendPasswordLink')->table($user));

        Notification::assertSentTo($user, SetPassword::class);
    });

    it('does not let users delete themselves', function () {
        $other = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertActionHidden(TestAction::make('delete')->table($this->admin))
            ->callAction(TestAction::make('delete')->table($other));

        expect(User::find($other->id))->toBeNull();

        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertActionHidden('delete');
    });

    it('skips the current user in bulk delete', function () {
        $others = User::factory()->count(2)->create();

        Livewire::test(ListUsers::class)
            ->selectTableRecords([$this->admin, ...$others])
            ->callAction(TestAction::make('delete')->table()->bulk());

        expect(User::pluck('id')->all())->toBe([$this->admin->id]);
    });
});
