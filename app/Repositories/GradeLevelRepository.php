<?php

namespace App\Repositories;

use App\Models\GradeLevel;

class GradeLevelRepository
{
    public function get()
    {
        return GradeLevel::query()
            ->with('schoolClasses')
            ->select('id', 'name')
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name
                ];
            });
    }

    public function update($data)
    {
        $gradeLevel = GradeLevel::findOrFail($data['id']);
        return $gradeLevel->update([
            'name' => $data['name']
        ]);
    }

    public function delete($data)
    {
        return GradeLevel::findOrFail($data['id'])->delete();
    }

    public function create($data)
    {
        return GradeLevel::create([
            'name' => $data['name']
        ]);
    }
}