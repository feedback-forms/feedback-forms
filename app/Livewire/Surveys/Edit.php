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
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class Edit extends Component
{
    public Feedback $survey;
    public Collection $schoolYears;
    public Collection $departments;
    public Collection $gradeLevels;
    public Collection $schoolClasses;
    public Collection $subjects;

    // Form fields
    public ?string $name;
    public ?string $expire_date;
    public ?int $response_limit;
    public int $school_year;
    public int $department;
    public int $grade_level;
    public int $class;
    public int $subject;

    protected $rules = [
        'name' => 'required|string|max:255',
        'expire_date' => 'required|date|after:now',
        'response_limit' => 'nullable|integer|min:-1',
        'school_year' => 'required|exists:school_years,id',
        'department' => 'required|exists:departments,id',
        'grade_level' => 'required|exists:grade_levels,id',
        'class' => 'required|exists:school_classes,id',
        'subject' => 'required|exists:subjects,id',
    ];

    public function mount($id)
    {
        // Initialize collections
        $this->schoolYears = new Collection();
        $this->departments = new Collection();
        $this->gradeLevels = new Collection();
        $this->schoolClasses = new Collection();
        $this->subjects = new Collection();

        // Load the survey
        $this->survey = Feedback::findOrFail($id);

        // Ensure the user can only edit their own surveys
        if ($this->survey->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load options from database
        $this->schoolYears = SchoolYear::get();
        $this->departments = Department::get();
        $this->gradeLevels = GradeLevel::orderBy('id')->get();
        $this->schoolClasses = SchoolClass::get();
        $this->subjects = Subject::get();

        // Set form values from the survey
        // Handle expire_date safely, ensuring it's a Carbon instance
        $this->name = $this->survey->name;
        if ($this->survey->expire_date instanceof Carbon) {
            $this->expire_date = $this->survey->expire_date->format('Y-m-d\TH:i');
        } else {
            // If it's a string (for existing records), convert it to Carbon
            $this->expire_date = Carbon::parse($this->survey->expire_date)->format('Y-m-d\TH:i');
        }

        $this->response_limit = $this->survey->limit;
        $this->school_year = $this->survey->year->id;
        $this->department = $this->survey->department->id;
        $this->grade_level = $this->survey->grade_level->id;
        $this->class = $this->survey->class->id;
        $this->subject = $this->survey->subject->id;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->survey->update([
                'name' => $this->name,
                'expire_date' => Carbon::parse($this->expire_date),
                'limit' => $this->response_limit,
                'school_year_id' => $this->school_year,
                'department_id' => $this->department,
                'grade_level_id' => $this->grade_level,
                'school_class_id' => $this->class,
                'subject_id' => $this->subject,
            ]);

            session()->flash('success', __('surveys.updated_successfully'));
            return redirect()->route('surveys.list');

        } catch (\Exception $e) {
            Log::error('Survey update failed: ' . $e->getMessage());
            session()->flash('error', __('surveys.update_failed') . ' ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.surveys.edit');
    }
}
