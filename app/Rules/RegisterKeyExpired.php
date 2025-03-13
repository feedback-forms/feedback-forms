<?php

namespace App\Rules;

use App\Models\Registerkey;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class RegisterKeyExpired implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $key = Registerkey::where('code', $value)->first();

        if (!$key) {
            $fail('register.invalid_code')->translate();

            return;
        }

        if (!$key->expire_at) {

            return;
        }

        if ($key->expire_at->isPast()) {
            $fail('register.expired_code')->translate();

            return;
        }
    }
}
