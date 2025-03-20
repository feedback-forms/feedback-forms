<?php

namespace App\Livewire\Admin;
use App\Services\OptionService;
use Livewire\Component;
use App\Models\{Subject, Department};
use Illuminate\Support\Facades\Request;

class Option extends Component
{
    protected $option_service;
    public $options = [];
    public $editingOption = '';
    public $editingId = null;
    public $editingKey = '';
    public $editingName = '';
    public $editingCode = '';
    public $newSubject = '';
    public $newDepartment = '';
    public $newSchoolYear = '';
    public $newGradeLevel = '';
    public $newSchoolClass = '';
    public $selectedGradeLevel = '';
    public $newSubjectCode = '';
    public $newDepartmentCode = '';

    public function boot(OptionService $option_service)
    {
        $this->option_service = $option_service;
    }

    public function mount()
    {
        $this->options = $this->option_service->get();
    }

    public function render()
    {
        return view('livewire/admin/options')->title(__('title.admin.options'));
    }

    public function editOption($key, $id)
    {
        $option = collect($this->options[$key])->firstWhere('id', $id);
        if (array_key_exists('code', $option)) {
            $this->editingCode = $option['code'];
        } else {
            $this->editingCode = '';
        }
        $this->editingKey = $key;
        $this->editingId = $id;
        $this->editingName = $option['name'];
        $this->editingOption = $option['name'];
    }

    public function updateOption()
    {
        $this->validate([
            'editingOption' => 'required',
            'editingId' => 'required',
            'editingKey' => 'required',
        ]);

        $this->option_service->handleModalRequest($this->editingKey, $this->editingId, $this->editingOption, $this->editingCode);
        
        $this->options = $this->option_service->get();
        return $this->dispatch('close-modal', 'edit-option');
    }

    public function deleteOption($key, $id)
    {
        $this->option_service->handleDeleteRequest($key, $id);
        $this->options = $this->option_service->get();
    }

    public function addSubject()
    {
        $this->validate([
            'newSubject' => 'required|min:2',
            'newSubjectCode' => 'required|min:2|max:10'
        ]);

        $this->option_service->createSubject([
            'name' => $this->newSubject,
            'code' => $this->newSubjectCode
        ]);

        $this->newSubject = '';
        $this->newSubjectCode = '';
        $this->options = $this->option_service->get();
    }

    public function addDepartment()
    {
        $this->validate([
            'newDepartment' => 'required|min:2',
            'newDepartmentCode' => 'required|min:2|max:10'
        ]);

        $this->option_service->createDepartment([
            'name' => $this->newDepartment,
            'code' => $this->newDepartmentCode
        ]);

        $this->newDepartment = '';
        $this->newDepartmentCode = '';
        $this->options = $this->option_service->get();
    }

    public function addSchoolYear()
    {
        $this->validate(['newSchoolYear' => 'required|min:2']);
        $this->option_service->createSchoolYear($this->newSchoolYear);
        $this->newSchoolYear = '';
        $this->options = $this->option_service->get();
    }

    public function addGradeLevel()
    {
        $this->validate(['newGradeLevel' => 'required|min:1']);
        $this->option_service->createGradeLevel($this->newGradeLevel);
        $this->newGradeLevel = '';
        $this->options = $this->option_service->get();
    }

    public function addSchoolClass()
    {
        $this->validate([
            'newSchoolClass' => 'required|min:1',
            'selectedGradeLevel' => 'required|exists:grade_levels,id'
        ]);
        
        $this->option_service->createSchoolClass($this->newSchoolClass, $this->selectedGradeLevel);
        $this->newSchoolClass = '';
        $this->selectedGradeLevel = '';
        $this->options = $this->option_service->get();
    }
}