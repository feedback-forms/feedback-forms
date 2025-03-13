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
        $users = User::all();
        $registerKeys = $keyService->getLatestRegisterKeys();

        return view('livewire/admin/panel', [
            'users' => $users,
            'registerKeys' => $registerKeys,
        ]);
    }
}
