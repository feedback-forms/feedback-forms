<?php

namespace App\Livewire\Admin;

use App\Services\RegisterKeyService;
use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class Panel extends Component
{
    public function render(RegisterKeyService $keyService)

    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire/admin/panel', [
            'users' => $this->users,
            'registerKeys' => $keyService->getLatestRegisterKeys(),
        ]);
    }
}
