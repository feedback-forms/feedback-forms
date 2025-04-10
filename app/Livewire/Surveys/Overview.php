<?php

namespace App\Livewire\Surveys;

use App\Models\{Feedback, SchoolYear, Department, GradeLevel, SchoolClass, Subject};
use Carbon\Carbon;
use Livewire\Component;

class Overview extends Component
{
    public array $filterState = [
        'expired' => false,
        'running' => true,
    ];

    public $surveys = [];
    public $schoolYears = [];
    public $departments = [];
    public $gradeLevels = [];
    public $schoolClasses = [];
    public $subjects = [];

    // Selected filter values
    public $selectedSchoolYear = null;
    public $selectedDepartment = null;
    public $selectedGradeLevel = null;
    public $selectedClass = null;
    public $selectedSubject = null;

    public function mount()
    {
        $this->loadFilterOptions();
        $this->loadSurveys();
    }

    protected function loadFilterOptions()
    {
        // Load all filter options from database
        $this->schoolYears = SchoolYear::orderBy('name', 'desc')->get();
        $this->departments = Department::orderBy('name')->get();
        $this->gradeLevels = GradeLevel::orderBy('name')->get();
        $this->schoolClasses = SchoolClass::orderBy('name')->get();
        $this->subjects = Subject::orderBy('name')->get();
    }

    public function filter(string $filter): void
    {
        $this->filterState[$filter] = !$this->filterState[$filter];
        // No need to reload surveys, as filtering is now done on the frontend
    }

    public function updateFilter(): void
    {
        // No need to reload surveys, as filtering is now done on the frontend
    }

    protected function loadSurveys()
    {
        // Start with a base query for the authenticated user
        $query = Feedback::with(['feedback_template', 'user', 'year', 'department', 'grade_level', 'class', 'subject'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        // Execute query and store results
        $surveys = $query->get();

        // Add computed properties for frontend filtering
        $now = Carbon::now();
        foreach ($surveys as $survey) {
            // Add isExpired flag
            $survey->isExpired = $survey->expire_date < $now;

            // Add isRunning flag
            $survey->isRunning =
                $survey->expire_date >= $now &&
                ($survey->limit == -1 || $survey->already_answered < $survey->limit);

            // Add formatted updated_at for display
            $survey->updated_at_diff = $survey->updated_at->diffForHumans();
        }

        $this->surveys = $surveys;
    }

    public function render()
    {
        return view('livewire.surveys.overview', [
            'surveys' => $this->surveys
        ])->title(__('title.survey.overview'));
    }
}
