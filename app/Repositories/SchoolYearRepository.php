<?php

namespace App\Repositories;

use App\Models\SchoolYear;

class SchoolYearRepository
{
    public function get()
    {
        return SchoolYear::query()
            ->select('id', 'name')
            ->get()
            ->map(function ($year) {
                return [
                    'id' => $year->id,
                    'name' => $year->name
                ];
            });
    }

    public function update($data)
    {
        $schoolYear = SchoolYear::findOrFail($data['id']);
        return $schoolYear->update([
            'name' => $data['name']
        ]);
    }

    public function delete($data)
    {
        return SchoolYear::findOrFail($data['id'])->delete();
    }

    public function create($data)
    {
        return SchoolYear::create([
            'name' => $data['name']
        ]);
    }
}