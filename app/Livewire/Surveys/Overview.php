<?php

namespace App\Livewire\Surveys;

use App\Models\Feedback;
use Carbon\Carbon;
use Livewire\Component;

class Overview extends Component
{
    public array $filterState = [
        'expired' => false,
        'running' => true,
        'cancelled' => false,
    ];

    public $surveys = [];

    public function mount()
    {
        $this->loadSurveys();
    }

    public function filter(string $filter): void
    {
        $this->filterState[$filter] = !$this->filterState[$filter];
        $this->loadSurveys();
    }

    protected function loadSurveys()
    {
        // Start with a base query for the authenticated user
        $query = Feedback::with(['feedback_template', 'user'])
            ->where('user_id', auth()->id());

        // Use a separate array to track conditions
        $conditions = [];

        // Add filter conditions
        if ($this->filterState['expired']) {
            $conditions[] = function($query) {
                $query->where('expire_date', '<', Carbon::now());
            };
        }

        if ($this->filterState['running']) {
            $conditions[] = function($query) {
                $query->where('expire_date', '>=', Carbon::now())
                      ->where(function($q) {
                          $q->where('limit', -1)
                            ->orWhereColumn('already_answered', '<', 'limit');
                      });
            };
        }

        // Apply conditions with orWhere if any exist
        if (count($conditions) > 0) {
            $query->where(function($q) use ($conditions) {
                foreach ($conditions as $index => $condition) {
                    if ($index === 0) {
                        $q->where(function($subQ) use ($condition) {
                            $condition($subQ);
                        });
                    } else {
                        $q->orWhere(function($subQ) use ($condition) {
                            $condition($subQ);
                        });
                    }
                }
            });
        }

        // Order by creation date
        $query->orderBy('created_at', 'desc');

        // Execute query and store results
        $this->surveys = $query->get();
    }

    public function render()
    {
        return view('livewire.surveys.overview', [
            'surveys' => $this->surveys
        ])->title(__('title.survey.overview'));
    }
}
