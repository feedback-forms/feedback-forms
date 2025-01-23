<?php

namespace App\Livewire\Surveys;

use Livewire\Component;

class Overview extends Component
{
    public array $filterState = [
        'expired' => false,
        'running' => false,
        'cancelled' => false,
    ];

    public $items = [];

    public function mount()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->items[] = null;
        }

    }

    public function filter(string $filter): void
    {
        $this->filterState[$filter] = !$this->filterState[$filter];#

        // TODO: search and assign items to query
    }

    public function render()
    {
        return view('livewire.surveys.overview');
    }
}
