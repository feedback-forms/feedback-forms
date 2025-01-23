<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function store(Request $request): Response
    {
        $validated = $request->validate(['token' => 'required']);

        $token = $validated['token'];

        dd($token);
    }

    public function index(): View
    {
        return view('welcome');
    }
}
