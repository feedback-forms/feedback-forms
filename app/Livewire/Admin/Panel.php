<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Registerkey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class Panel extends Component
{
    public Collection $users;
    public Collection $registerkeys;

    // Success message to show after token operations
    public ?string $successMessage = null;

    public function mount(): void
    {
        $this->users = User::all();
        $this->registerkeys = Registerkey::orderBy('created_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire/admin/panel', [
            'users' => $this->users,
            'registerkeys' => $this->registerkeys,
        ]);
    }

    /**
     * Generate a new registration key
     */
    public function createToken(): void
    {
        // Generate a token with format XXXX-XXXX (where X is alphanumeric)
        $code = Str::upper(Str::substr(Str::uuid(), 0, 4) . '-' . Str::substr(Str::uuid(), 0, 4));

        Registerkey::create(['code' => $code]);

        // Refresh the collection of registerkeys
        $this->registerkeys = Registerkey::orderBy('created_at', 'desc')->get();

        $this->successMessage = __('admin.token_created_successfully');
    }

    /**
     * Revoke (delete) a registration key
     */
    public function revokeToken(int $id): void
    {
        $registerkey = Registerkey::find($id);

        if ($registerkey) {
            // Set registerkey_id to NULL for all users associated with this key
            User::where('registerkey_id', $registerkey->id)->update(['registerkey_id' => null]);

            // Now it's safe to delete the registerkey
            $registerkey->delete();
            $this->successMessage = __('admin.token_revoked_successfully');
        }

        // Refresh the collection of registerkeys
        $this->registerkeys = Registerkey::orderBy('created_at', 'desc')->get();
    }

    /**
     * Clear the success message
     */
    public function clearMessage(): void
    {
        $this->successMessage = null;
    }
}