<?php

namespace App\Http\Controllers;
use App\Http\Requests\{SmileyRequest, StoreSurveyRequest};
use Illuminate\Support\Facades\{Log};

use App\Services\SurveyService;
use App\Models\{Feedback_template, Question_template, Feedback, SchoolYear, Department, GradeLevel, SchoolClass, Subject};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Show the survey creation form
     */
    public function create(Request $request): View|RedirectResponse
    {
        $templates = Feedback_template::all();
        $questionTemplates = Question_template::all();
        $selectedTemplate = $request->route('template') ?? $request->query('template');

        // If no template is selected, redirect to templates page
        if (!$selectedTemplate) {
            return redirect()->route('templates.index')
                ->with('info', __('surveys.select_template_first'));
        }

        // Load options from database
        $schoolYears = SchoolYear::get();
        $departments = Department::get();
        $gradeLevels = GradeLevel::orderBy('id')->get();
        $schoolClasses = SchoolClass::get();
        $subjects = Subject::get();

        // Verify the template exists
        $template = $templates->where('name', 'templates.feedback.' . $selectedTemplate)->first();
        if (!$template) {
            return redirect()->route('templates.index')
                ->with('error', __('surveys.template_not_found'));
        }

        return view('surveys.create', [
            'templates' => $templates,
            'questionTemplates' => $questionTemplates,
            'selectedTemplate' => $selectedTemplate,
            'schoolYears' => $schoolYears,
            'departments' => $departments,
            'gradeLevels' => $gradeLevels,
            'schoolClasses' => $schoolClasses,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Store a new survey
     */
    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Process the survey data if it exists
            if (isset($validated['survey_data'])) {
                $surveyData = json_decode($validated['survey_data'], true);
                // Store the survey data for later use or processing
                session(['survey_data' => $surveyData]);
                unset($validated['survey_data']);
            }

            $survey = $this->surveyService->createFromTemplate(
                $validated,
                auth()->id()
            );

            // Ensure the survey was created successfully
            if (!$survey || !$survey->exists) {
                throw new \Exception('Survey creation failed');
            }

            // Force a redirect to the surveys index page
            return redirect()->to(route('surveys.list'))
                ->with('success', __('surveys.created_successfully'));

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Survey creation failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', __('surveys.creation_failed') . ' ' . $e->getMessage());
        }
    }

    /**
     * Show survey details
     */
    public function show(Feedback $survey): View
    {
        $canBeAnswered = $this->surveyService->canBeAnswered($survey);

        return view('surveys.show', [
            'survey' => $survey->load(['questions.question_template']),
            'canBeAnswered' => $canBeAnswered,
        ]);
    }

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

    public function showCheckbox()
    {
        return view('survey_templates.checkbox');
    }

    public function retrieveSmiley(SmileyRequest $request)
    {
        try {

            dd($request);
        } catch(\Exception $e) {
            Log::debug('Error while retrieving Data from Smiley Template: ' . $e);
        }
    }
}
