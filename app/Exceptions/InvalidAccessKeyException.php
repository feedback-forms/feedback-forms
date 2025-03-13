<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class InvalidAccessKeyException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function render(Request $request): RedirectResponse
    {
        return redirect()->route('welcome')
            ->with('error', __('surveys.invalid_access_key'));
    }
}