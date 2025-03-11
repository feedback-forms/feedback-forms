<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class Panel extends Component
{
    public Collection $users;

    public function mount(): void
    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire/admin/panel', [
            'users' => $this->users,
        ]);
    }
}