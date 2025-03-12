<?php

use App\Livewire\Forms\RegisterForm;
use App\Models\Registerkey;
use App\Models\User;
use App\Rules\RegisterKeyExpired;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $register_key = '';
    public RegisterForm $registerForm;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'register_key' => ['required', 'string', 'max:9', 'min:9', 'exists:registerkeys,code', 'regex:' . Registerkey::KEY_REGEX, new RegisterKeyExpired()]
        ], [
            'register_key.regex' => __('register.invalid_code_format'),
            'register_key.exists' => __('register.invalid_code'),
        ]);

        $validated['registerkey_id'] = $this->registerForm->getRegisterKey($this->register_key)->id;
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('register.name')"/>
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required
                          autofocus autocomplete="name"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2"/>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('register.email')"/>
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email"
                          autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2"/>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('register.password')"/>

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="new-password"/>

            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('register.confirm-password')"/>

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password"/>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
        </div>

        <!-- Invite Token -->
        <div class="mt-4">
            <x-input-label for="register_key" :value="__('register.register_key')"/>

            <x-text-input wire:model="register_key" id="register_key" class="block mt-1 w-full"
                          type="text" required maxlength="9"
                          name="register_key" required autocomplete="off"/>

            <x-input-error :messages="$errors->get('register_key')" class="mt-2"/>
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
               href="{{ route('login') }}" title="Zum Login" wire:navigate>
                {{ __('register.already-registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('register.register') }}
            </x-primary-button>
        </div>
    </form>
</div>
