<?php

namespace App\Http\Controllers;
use App\Http\Requests\SmileyRequest;
use Illuminate\Support\Facades\{Log};
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
        try {

            dd($regquest);
        } catch(\Exception $e) {
            Log::debug('Error while retrieving Data from Smiley Template: ' . $e);
        }
    }
}
