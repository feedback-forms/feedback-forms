<?php

namespace App\Livewire\Admin;

use App\Models\Registerkey;
use App\Services\RegisterKeyService;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class InviteToken extends Component
{
    #[Validate(['required', 'integer', 'min:1'])]
    public int $duration = 1;

    #[Validate(['required', 'string', 'min:9', 'max:9'])]
    public string $token = '';

    public Collection $registerKeys;

    public ?RegisterKey $registerKey = null;

    public function render(RegisterKeyService $keyService)
    {
        $this->registerKeys = $keyService->getAll();

        return view('livewire.admin.invite-token', [
            'registerKeys' => $this->registerKeys
        ])->title(__('title.admin.invite-token'));
    }

    public function addToken(RegisterKeyService $keyService)
    {
        $this->validate();

        Registerkey::create([
            'code' => $this->token,
            'expire_at' => Carbon::now()->addDays($this->duration)->toDateTimeString()
        ]);

        $this->duration = 1;
        $this->registerKeys = $keyService->getAll();
    }

    public function generateToken(RegisterKeyService $keyService)
    {
        $this->token = $keyService->generateUniqueRegisterKey();
    }

    public function revokeToken(int $tokenId, RegisterKeyService $keyService)
    {
        $keyService->updateRegisterKey($tokenId, [
            'expire_at' => Carbon::now()
        ]);

        $this->registerKeys = $keyService->getAll();
    }

    public function changeToCurrentToken(int $tokenId, RegisterKeyService $keyService)
    {
        $this->registerKey = $keyService->getRegisterKeyById($tokenId);
        $this->token = $this->registerKey->code;

        if (!$this->registerKey->expire_at) {
            $this->duration = 1;

            return;
        }

        $now = Carbon::now();
        $this->duration = $now->diffInDays($this->registerKey->expire_at);
    }

    public function changeToken(RegisterKeyService $keyService)
    {
        $this->validate();

        $keyService->updateRegisterKey($this->registerKey->id, [
            'expire_at' => Carbon::now()->addDays($this->duration)->toDateTimeString()
        ]);

        $this->registerKeys = $keyService->getAll();
    }

    public function deleteToken($id, RegisterKeyService $keyService)
    {
        Log::debug('Delete token with id: ' . $id);
        $keyService->delete($id);

        $this->registerKeys = $keyService->getAll();
    }
}
