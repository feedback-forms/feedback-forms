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
    public ?string $school_year;
    public ?string $department;
    public ?string $grade_level;
    public ?string $class;
    public ?string $subject;

    protected $rules = [
        'name' => 'required|string|max:255',
        'expire_date' => 'required|date|after:now',
        'response_limit' => 'nullable|integer|min:-1',
        'school_year' => 'required|string',
        'department' => 'required|string',
        'grade_level' => 'required|string',
        'class' => 'required|string',
        'subject' => 'required|string',
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
        $this->schoolYears = SchoolYear::active()->get();
        $this->departments = Department::active()->get();
        $this->gradeLevels = GradeLevel::active()->orderBy('level')->get();
        $this->schoolClasses = SchoolClass::active()->get();
        $this->subjects = Subject::active()->get();

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
        $this->school_year = $this->survey->school_year;
        $this->department = $this->survey->department;
        $this->grade_level = $this->survey->grade_level;
        $this->class = $this->survey->class;
        $this->subject = $this->survey->subject;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->survey->update([
                'name' => $this->name,
                'expire_date' => Carbon::parse($this->expire_date),
                'limit' => $this->response_limit,
                'school_year' => $this->school_year,
                'department' => $this->department,
                'grade_level' => $this->grade_level,
                'class' => $this->class,
                'subject' => $this->subject,
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
