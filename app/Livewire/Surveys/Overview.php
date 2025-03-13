<?php

namespace App\Livewire\Surveys;

use App\Models\Feedback;
use App\Models\SchoolYear;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Subject;
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
        $this->loadSurveys();
    }

    public function updateFilter(): void
    {
        $this->loadSurveys();
    }

    protected function loadSurveys()
    {
        // Start with a base query for the authenticated user
        $query = Feedback::with(['feedback_template', 'user'])
            ->where('user_id', auth()->id());

        // Apply dropdown filters if selected
        if ($this->selectedSchoolYear) {
            $query->where('school_year', $this->selectedSchoolYear);
        }

        if ($this->selectedDepartment) {
            $query->where('department', $this->selectedDepartment);
        }

        if ($this->selectedGradeLevel) {
            $query->where('grade_level', $this->selectedGradeLevel);
        }

        if ($this->selectedClass) {
            $query->where('class', $this->selectedClass);
        }

        if ($this->selectedSubject) {
            $query->where('subject', $this->selectedSubject);
        }

        // Use a separate array to track conditions
        $conditions = [];

        // Add filter conditions
        if ($this->filterState['expired']) {
            $conditions[] = function($query) {
                $query->where('expire_date', '<', Carbon::now());
            };
        }

        if ($this->filterState['running']) {
            $conditions[] = function($query) {
                // For running surveys, we need two conditions:
                // 1. The survey hasn't expired yet (expire_date >= now)
                // 2. The survey either has unlimited responses (limit = -1)
                //    OR hasn't reached its response limit yet (already_answered < limit)
                $query->where('expire_date', '>=', Carbon::now())
                      ->where(function($q) {
                          $q->where('limit', -1)
                            ->orWhereColumn('already_answered', '<', 'limit');
                      });
            };
        }

        // Apply conditions with orWhere if any exist
        if (count($conditions) > 0) {
            $query->where(function($q) use ($conditions) {
                foreach ($conditions as $index => $condition) {
                    if ($index === 0) {
                        $q->where(function($subQ) use ($condition) {
                            $condition($subQ);
                        });
                    } else {
                        $q->orWhere(function($subQ) use ($condition) {
                            $condition($subQ);
                        });
                    }
                }
            });
        }

        // Order by creation date
        $query->orderBy('created_at', 'desc');

        // Execute query and store results
        $this->surveys = $query->get();
    }

    public function render()
    {
        return view('livewire.surveys.overview', [
            'surveys' => $this->surveys
        ]);
    }
}
