<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function showSmiley()
    {
        return view('survey_templates.smiley');
    }
}
