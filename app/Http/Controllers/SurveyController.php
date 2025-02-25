<?php

namespace App\Http\Controllers;
use App\Http\Requests\SmileyRequest;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function showSmiley()
    {
        return view('survey_templates.smiley');
    }

    public function showTable()
    {
        return view('survey_templates.table');
    }

    public function showTarget()
    {
        return view('survey_templates.target');
    }

    public function retrieveSmiley(SmileyRequest $request)
    {
        dd($request);
    }
}
