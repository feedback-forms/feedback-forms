<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function validate(Request $request): Response
    {
        $validated = $request->validate(['token' => 'required']);

        $token = $validated['token'];

        dd($token);
    }

    public function render(): View
    {
        return view('welcome');
    }
}
