<?php

namespace App\Livewire\Admin;

use App\Models\Registerkey;
use App\Services\RegisterKeyService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InviteToken extends Component
{
    #[Validate(['required', 'integer', 'min:1'])]
    public int $duration = 1;

    #[Validate(['required', 'string', 'min:9', 'max:9'])]
    public string $token = '';

    public Collection $registerKeys;

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
        do {
            $generatedToken = fake()->regexify(Registerkey::KEY_REGEX);
        } while ($keyService->getByCode($generatedToken) !== null);

        $this->token = $generatedToken;
    }

    public function revokeToken(int $tokenId, RegisterKeyService $keyService)
    {
        $keyService->updateRegisterKey($tokenId, [
            'expire_at' => Carbon::now()
        ]);

        $this->registerKeys = $keyService->getAll();
    }
}
