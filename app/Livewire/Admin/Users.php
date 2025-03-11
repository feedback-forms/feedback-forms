<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Users extends Component
{
    public Collection $users;
    public $temporaryPassword = null;
    public $temporaryPasswordUserId = null;

    public function mount(): void
    {
        $this->users = User::all();
    }

    public function generateTemporaryPassword($userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            return;
        }

        // Generate a random 10-character password
        $temporaryPassword = Str::random(10);

        // Hash the password and save it for the user
        $user->password = Hash::make($temporaryPassword);
        $user->save();

        // Flash a success message with the temporary password
        session()->flash('temporary_password_success', [
            'userId' => $userId,
            'userName' => $user->name,
            'password' => $temporaryPassword
        ]);

        // Log the action for security auditing
        Log::info('Temporary password generated', [
            'admin_user_id' => auth()->id(),
            'target_user_id' => $userId,
            'target_user_name' => $user->name
        ]);
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => $this->users,
        ]);
    }
}