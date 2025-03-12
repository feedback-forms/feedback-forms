<?php

namespace App\Livewire\Admin;

use App\Services\RegisterKeyService;
use Livewire\Component;

class Panel extends Component
{
    public function render(RegisterKeyService $keyService)
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
            'registerKeys' => $keyService->getLatestRegisterKeys(),
        ]);
    }
}
