<?php

namespace App\Repositories;
use App\Models\Subject;

class SubjectRepository {

    public $subject;

    public function __construct(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function get()
    {
        return $this->subject->get();
    }

    public function create($data)
    {
        return Subject::create([
            'name' => $data['name'],
            'code' => $data['code']
        ]);
    }

    public function update($data)
    {
        return $this->subject->where('id', $data['id'])->update($data);
    }

    public function delete($id)
    {
        return $this->subject->where('id', $id)->delete();
    }
}