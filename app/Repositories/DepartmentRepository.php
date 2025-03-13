<?php

namespace App\Repositories;
use App\Models\Department;


class DepartmentRepository
{
    private $department;
    public function __construct(Department $department)
    {
        $this->department = $department;
    }

    public function get()
    {
        return $this->department->get();
    }

    public function create($data)
    {
        return $this->department->create([
            'name' => $data['name'],
            'code' => $data['code']
        ]);
    }

    public function update($data)
    {
        return $this->department->where('id', $data['id'])->update($data);
    }

    public function delete($id)
    {
        return $this->department->where('id', $id)->delete();
    }
}