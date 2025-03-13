<?php

namespace App\Services;

use App\Models\Registerkey;
use Illuminate\Database\Eloquent\Collection;

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
}
