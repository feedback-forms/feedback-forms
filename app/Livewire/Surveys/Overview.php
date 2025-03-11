<?php

namespace App\Livewire\Surveys;

use App\Models\Feedback;
use App\Models\SchoolYear;
use App\Models\Department;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Overview extends Component
{
    public array $filterState = [
        'expired' => false,
        'running' => true,
        'cancelled' => false,
    ];

    public $surveys = [];
    public $schoolYears = [];
    public $departments = [];
    public $selectedSchoolYear = '';
    public $selectedDepartment = '';

    public function mount()
    {
        $this->loadSchoolYears();
        $this->loadDepartments();
        $this->loadSurveys();
    }

    public function loadSchoolYears()
    {
        // Get active school years from the SchoolYear model
        $this->schoolYears = SchoolYear::active()->orderBy('name', 'desc')->get();
    }

    public function loadDepartments()
    {
        // Get active departments from the Department model
        $this->departments = Department::active()->orderBy('name')->get();
    }

    public function filter(string $filter): void
    {
        $this->filterState[$filter] = !$this->filterState[$filter];
        $this->loadSurveys();
    }

    public function filterBySchoolYear($schoolYear)
    {
        $this->selectedSchoolYear = $schoolYear;
        $this->loadSurveys();
    }

    public function filterByDepartment($department)
    {
        $this->selectedDepartment = $department;
        $this->loadSurveys();
    }

    public function clearSchoolYearFilter()
    {
        $this->selectedSchoolYear = '';
        $this->loadSurveys();
    }

    public function clearDepartmentFilter()
    {
        $this->selectedDepartment = '';
        $this->loadSurveys();
    }

    public function clearFilters()
    {
        $this->selectedSchoolYear = '';
        $this->selectedDepartment = '';
        $this->loadSurveys();
    }

    protected function loadSurveys()
    {
        // Start with a base query for the authenticated user
        $query = Feedback::with(['feedback_template', 'user'])
            ->where('user_id', auth()->id());

        // Apply school year filter if selected
        if ($this->selectedSchoolYear) {
            $query->where('school_year', $this->selectedSchoolYear);
        }

        // Apply department filter if selected
        if ($this->selectedDepartment) {
            $query->where('department', $this->selectedDepartment);
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
            'surveys' => $this->surveys,
            'schoolYears' => $this->schoolYears,
            'departments' => $this->departments,
        ]);
    }
}
