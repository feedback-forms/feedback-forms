<?php

namespace App\Services;
use App\Repositories\{
    SubjectRepository, 
    DepartmentRepository, 
    SchoolClassRepository,
    SchoolYearRepository,
    GradeLevelRepository
};

class OptionService {
    public $subject_repository;
    public $department_repository;
    public $school_class_repository;
    public $school_year_repository;
    public $grade_level_repository;

    public function __construct(
        SubjectRepository $subject_repository,
        DepartmentRepository $department_repository,
        SchoolClassRepository $school_class_repository,
        SchoolYearRepository $school_year_repository,
        GradeLevelRepository $grade_level_repository
    )
    {
        $this->subject_repository = $subject_repository;
        $this->department_repository = $department_repository;
        $this->school_class_repository = $school_class_repository;
        $this->school_year_repository = $school_year_repository;
        $this->grade_level_repository = $grade_level_repository;
    }

    public function get()
    {
        $combined = [];
        $combined[__('options.subject.name_plural')] = $this->subject_repository->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code
            ];
        })->toArray();
        
        $combined[__('options.department.name_plural')] = $this->department_repository->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code
            ];
        })->toArray();
        
        $combined[__('options.school_class.name_plural')] = $this->school_class_repository->get()->toArray();
        $combined[__('options.school_year.name_plural')] = $this->school_year_repository->get()->toArray();
        $combined[__('options.grade_level.name_plural')] = $this->grade_level_repository->get()->toArray();
        
        return $combined;
    }

    public function handleModalRequest($editingKey, $editingId, $editingName, $editingCode)
    {
        switch ($editingKey) {
            case __('options.subject.name_plural'):
                return $this->subject_repository->update([
                    'id' => $editingId,
                    'name' => $editingName,
                    'code' => $editingCode
                ]);
            case __('options.department.name_plural'):
                return $this->department_repository->update([
                    'id' => $editingId,
                    'name' => $editingName,
                    'code' => $editingCode
                ]);
            case __('options.school_class.name_plural'):
                $this->school_class_repository->update([
                    'id' => $editingId,
                    'name' => $editingName,
                ]);
                break;
            case __('options.school_year.name_plural'):
                $this->school_year_repository->update([
                    'id' => $editingId,
                    'name' => $editingName,
                ]);
                break;
            case __('options.grade_level.name_plural'):
                $this->grade_level_repository->update([
                    'id' => $editingId,
                    'name' => $editingName,
                ]);
                break;
        }
    }

    public function handleDeleteRequest($editingKey, $editingId)
    {
        switch ($editingKey) {
            case __('options.subject.name_plural'):
                $this->subject_repository->delete([
                    'id' => $editingId,
                ]);
                break;
            case __('options.department.name_plural'):
                $this->department_repository->delete([
                    'id' => $editingId,
                ]);
                break;
            case __('options.school_class.name_plural'):
                $this->school_class_repository->delete([
                    'id' => $editingId,
                ]);
                break;
            case __('options.school_year.name_plural'):
                $this->school_year_repository->delete([
                    'id' => $editingId,
                ]);
                break;
            case __('options.grade_level.name_plural'):
                $this->grade_level_repository->delete([
                    'id' => $editingId,
                ]);
                break;
        }
    }

    public function createSubject($data)
    {
        return $this->subject_repository->create([
            'name' => $data['name'],
            'code' => $data['code']
        ]);
    }

    public function createDepartment($data)
    {
        return $this->department_repository->create([
            'name' => $data['name'],
            'code' => $data['code']
        ]);
    }

    public function createSchoolYear($name)
    {
        return $this->school_year_repository->create(['name' => $name]);
    }

    public function createGradeLevel($name)
    {
        return $this->grade_level_repository->create(['name' => $name]);
    }

    public function createSchoolClass($name, $gradeLevelId)
    {
        return $this->school_class_repository->create([
            'name' => $name,
            'grade_level_id' => $gradeLevelId
        ]);
    }
}
