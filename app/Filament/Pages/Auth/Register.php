<?php

namespace App\Filament\Pages\Auth;

use App\Enums\CustomerStatusEnum;
use App\Enums\DefaultRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Customer;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Http\Responses\Auth\RegistrationResponse as FilamentRegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class Register extends BaseRegister
{
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('firstname')
            ->label('Firstname')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getLastnameFormComponent(): Component 
    {
        return TextInput::make('lastname')
            ->label('Lastname')
            ->required()
            ->maxLength(255);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email address')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->afterStateUpdated(function (string $state, $set) {
                $this->checkExistingEmail($state);
            })
            ->reactive();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute('password');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Password confirmed')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getLastnameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * E-posta adresinin kayıtlı olup olmadığını kontrol et
     */
    public function checkExistingEmail(string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            // E-posta kayıtlı, session'a bilgiyi kaydet
            Session::put('registered_email', $email);
            Session::put('email_check_time', now()->timestamp);
            
            // Kullanıcıya bilgi mesajı göster
            $redirectUrl = route('packages.redirect', ['email' => $email]);
            
            Notification::make()
                ->title('Bu e-posta adresi kayıtlıdır')
                ->body('Bu e-posta adresi ile devam etmek istiyorsanız lütfen <a href="' . $redirectUrl . '" class="text-primary-500 font-bold underline">tıklayınız</a>.')
                ->warning()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('login')
                        ->label('Giriş Yap')
                        ->url($redirectUrl)
                        ->button(),
                ])
                ->send();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRegistration(array $data): Model
    {
        // Önce kullanıcıyı oluştur
        $user = parent::handleRegistration($data);
        
        // Ardından customer kaydını oluştur
        Customer::create([
            'user_id' => $user->id,
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'email' => $data['email'],
            'status' => CustomerStatusEnum::PENDING,
        ]);
        
        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['name'] = $data['firstname'] . ' ' . $data['lastname'];
        $data['status'] = UserStatusEnum::PENDING; 
        $data['role'] = DefaultRoleEnum::USER; 

        return $data;
    }

    protected function getRegistrationFormAction(): string
    {
        return 'register';
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        // Tam adı oluştur
        $fullName = $data['firstname'] . ' ' . $data['lastname'];

        $user = $this->getUserModel()::create([
            'name' => $fullName,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => UserStatusEnum::PENDING,
            'role' => DefaultRoleEnum::USER,
        ]);

        // Müşteri kaydı oluştur
        Customer::create([
            'user_id' => $user->id,
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'email' => $data['email'],
            'status' => CustomerStatusEnum::PENDING,
        ]);

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        // Özel bir yanıt döndür - doğrudan paket seçim sayfasına yönlendir
        return new class('/packages') implements RegistrationResponse {
            public function __construct(
                protected string $url,
            ) {}
            
            public function toResponse($request)
            {
                return Redirect::to($this->url);
            }
        };
    }
} 