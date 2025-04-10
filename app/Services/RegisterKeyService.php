<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Registerkey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class RegisterKeyService
{
    public function getLatestRegisterKeys(): Collection
    {
        return Registerkey::orderBy('created_at', 'desc')
            ->take(3)
            ->get();
    }

    public function getAll(): Collection
    {
        return Registerkey::orderBy('created_at', 'desc')
            ->orderBy('expire_at', 'desc')
            ->get();
    }

    public function getByCode(string $code): ?Registerkey
    {
        return Registerkey::where('code', $code)->first();
    }

    public function updateRegisterKey(int $id, array $data)
    {
        return Registerkey::find($id)->update($data);
    }

    public function getRegisterKeyById(int $id): ?Registerkey
    {
        return Registerkey::find($id);
    }

    public function delete($id) {
        return Registerkey::find($id)->delete();
    }

    public function generateUniqueRegisterKey(): string
    {
        do {
            $key = strtoupper(substr(md5(uniqid()), 0, 8));
            $registerKey = substr($key, 0, 4) . '-' . substr($key, 4, 4);
        } while ($this->getByCode($registerKey) !== null);

        return $registerKey;
    }
}
