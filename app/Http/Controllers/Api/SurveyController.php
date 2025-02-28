<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Create a new survey
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:feedback_templates,id',
            'expire_date' => 'required|date|after:now',
            'response_limit' => 'nullable|integer|min:-1',
            'questions' => 'required|array|min:1',
            'questions.*.template_id' => 'required|exists:question_templates,id',
            'questions.*.text' => 'required|string|max:255',
        ]);

        try {
            $survey = $this->surveyService->createFromTemplate(
                $validated,
                auth()->id()
            );

            return response()->json([
                'message' => 'Survey created successfully',
                'survey' => $survey->load('questions'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create survey',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}