<?php

namespace App\Livewire\Admin;

use App\Services\SurveyAggregationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Livewire component for survey result aggregation in the admin dashboard
 *
 * This component provides UI interaction for the admin to view aggregated survey
 * results based on different categories (class, department, subject, school year)
 * while ensuring anonymity through threshold enforcement.
 */
class SurveyAggregation extends Component
{
    /** @var string|null */
    public $selectedCategory = null;

    /** @var string|null */
    public $selectedValue = null;

    /** @var array */
    public $availableCategories = [];

    /** @var array */
    public $availableValues = [];

    /** @var array|null */
    public $aggregatedData = null;

    /** @var bool */
    public $loading = false;

    /** @var array */
    public $thresholds = [];

    /** @var string|null */
    public $errorMessage = null;

    /** @var string|null */
    public $activeTab = null;

    /**
     * Initialize the component with available categories and threshold values
     *
     * @param SurveyAggregationService $aggregationService
     * @return void
     */
    public function mount(SurveyAggregationService $aggregationService): void
    {
        // Set up available categories
        $this->availableCategories = [
            'class' => __('admin.class'),
            'department' => __('admin.department'),
            'subject' => __('admin.subject'),
            'school_year' => __('admin.school_year')
        ];

        // Load thresholds for UI display
        $this->thresholds = $aggregationService->getAllThresholds();

        Log::debug("SurveyAggregation component mounted", [
            'availableCategories' => $this->availableCategories,
            'thresholds' => $this->thresholds
        ]);
    }

    /**
     * Handle category selection change
     *
     * @param string|null $value
     * @return void
     */
    public function updatedSelectedCategory(?string $value): void
    {
        Log::debug("Category selected", ['category' => $value]);

        $this->resetExcept(['availableCategories', 'selectedCategory', 'thresholds']);
        $this->selectedCategory = $value;

        if ($this->selectedCategory) {
            $this->loadAvailableValues();
        }
    }

    /**
     * Handle value selection change
     *
     * @param string|null $value
     * @return void
     */
    public function updatedSelectedValue(?string $value): void
    {
        Log::debug("Value selected", ['value' => $value]);

        $this->selectedValue = $value;
        $this->errorMessage = null;
        $this->activeTab = null;

        if ($this->selectedValue) {
            $this->loadAggregatedData();
        } else {
            $this->aggregatedData = null;
        }
    }

    /**
     * Set the active tab
     *
     * @param string $tabName
     * @return void
     */
    public function setActiveTab(string $tabName): void
    {
        $this->activeTab = $tabName;

        Log::debug("Tab selected", ['tab' => $tabName]);
    }

    /**
     * Load available values for the selected category
     *
     * @return void
     */
    public function loadAvailableValues(): void
    {
        if (!$this->selectedCategory) {
            $this->availableValues = [];
            return;
        }

        try {
            $aggregationService = app(SurveyAggregationService::class);
            $this->availableValues = $aggregationService->getCategoryValues($this->selectedCategory);

            Log::debug("Available values loaded", [
                'category' => $this->selectedCategory,
                'values' => $this->availableValues,
                'count' => count($this->availableValues)
            ]);

            if (empty($this->availableValues)) {
                $this->errorMessage = __('admin.no_values_available');
                Log::info("No values available for category", ['category' => $this->selectedCategory]);
            }
        } catch (\Exception $e) {
            Log::error('Error loading category values: ' . $e->getMessage(), [
                'category' => $this->selectedCategory,
                'exception' => $e
            ]);
            $this->errorMessage = __('admin.error_loading_values');
            $this->availableValues = [];
        }
    }

    /**
     * Load aggregated data for the selected category and value
     *
     * @return void
     */
    public function loadAggregatedData(): void
    {
        if (!$this->selectedCategory || !$this->selectedValue) {
            $this->aggregatedData = null;
            return;
        }

        $this->loading = true;
        $this->errorMessage = null;

        try {
            $aggregationService = app(SurveyAggregationService::class);

            Log::debug("Loading aggregated data", [
                'category' => $this->selectedCategory,
                'value' => $this->selectedValue
            ]);

            $this->aggregatedData = $aggregationService->aggregateByCategory(
                $this->selectedCategory,
                $this->selectedValue
            );

            // Set first tab as active if threshold met and categories exist
            if ($this->aggregatedData['threshold_met'] &&
                isset($this->aggregatedData['categories']) &&
                !empty($this->aggregatedData['categories'])) {
                $this->activeTab = array_key_first($this->aggregatedData['categories']);
            }

            Log::debug("Aggregated data loaded", [
                'threshold_met' => $this->aggregatedData['threshold_met'] ?? false,
                'submission_count' => $this->aggregatedData['submission_count'] ?? 0,
                'min_threshold' => $this->aggregatedData['min_threshold'] ?? 0,
                'has_categories' => isset($this->aggregatedData['categories']),
                'categories' => isset($this->aggregatedData['categories']) ? array_keys($this->aggregatedData['categories']) : [],
                'active_tab' => $this->activeTab
            ]);

            if (isset($this->aggregatedData['error'])) {
                $this->errorMessage = __('admin.aggregation_error');
                Log::error("Aggregation error", ['error' => $this->aggregatedData['error']]);
            }
        } catch (\Exception $e) {
            Log::error('Error aggregating survey data: ' . $e->getMessage(), [
                'category' => $this->selectedCategory,
                'value' => $this->selectedValue,
                'exception' => $e
            ]);
            $this->errorMessage = __('admin.aggregation_error');
            $this->aggregatedData = null;
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.admin.survey-aggregation');
    }
}