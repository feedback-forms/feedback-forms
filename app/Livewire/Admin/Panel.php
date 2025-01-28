<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Panel extends Component
{
    public function render()
    {
        // Mock data for frontend development
        $users = collect([
            ['name' => 'John Doe'],
            ['name' => 'Jane Smith'],
            ['name' => 'Alice Johnson'],
            ['name' => 'Bob Wilson'],
        ]);

        return view('livewire/admin/panel', [
            'users' => $users,
        ]);
    }
}