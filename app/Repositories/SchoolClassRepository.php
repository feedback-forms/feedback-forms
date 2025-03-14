<?php

namespace App\Repositories;
use App\Models\SchoolClass;

class SchoolClassRepository {

    public $school_class;

    public function __construct(SchoolClass $school_class)
    {
        $this->school_class = $school_class;
    }

    public function get()
    {
        return $this->school_class->get();
    }

    public function create($data)
    {
        return $this->school_class->create([
            'name' => $data['name'],
            'grade_level_id' => $data['grade_level_id']
        ]);
    }

    public function update($data)
    {
        return $this->school_class->where('id', $data['id'])->update($data);
    }

    public function delete($id)
    {
        return $this->school_class->where('id', id)->delete();
    }
}