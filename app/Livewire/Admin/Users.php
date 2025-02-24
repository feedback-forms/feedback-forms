<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Users extends Component
{
    public function render()
    {
        // Mock data for frontend development
        $users = collect([
            [
                'name' => 'John Doe',
                'password_changed' => 4,
            ],
            [
                'name' => 'Jane Smith',
                'password_changed' => 1,
            ],
            [
                'name' => 'Alice Johnson',
                'password_changed' => 0,
            ],
            [
                'name' => 'Bob Wilson',
                'password_changed' => 2,
            ],
        ]);

        return view('livewire.admin.users', [
            'users' => $users,
        ]);
    }
}