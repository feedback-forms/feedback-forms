<?php

namespace App\ViewModels;

use App\Models\Feedback;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SurveyStatisticsViewModel
{
    /**
     * The survey being viewed
     *
     * @var Feedback
     */
    protected Feedback $survey;

    /**
     * The statistics data for the survey
     *
     * @var array
     */
    protected array $statisticsData;

    /**
     * Flag indicating if this is a table survey
     *
     * @var bool
     */
    protected bool $isTableSurvey = false;

    /**
     * Flag indicating if this is a target template
     *
     * @var bool
     */
    protected bool $isTargetTemplate = false;

    /**
     * Flag indicating if this is a smiley template
     *
     * @var bool
     */
    protected bool $isSmileyTemplate = false;

    /**
     * Flag indicating if this is a checkbox template
     *
     * @var bool
     */
    protected bool $isCheckboxTemplate = false;

    /**
     * Table categories for table surveys
     *
     * @var array
     */
    protected array $tableCategories = [];

    /**
     * Create a new view model instance.
     *
     * @param Feedback $survey
     * @param array $statisticsData
     */
    public function __construct(Feedback $survey, array $statisticsData)
    {
        $this->survey = $survey;
        $this->statisticsData = $statisticsData;

        $this->processTemplateTypes();
        $this->processTableCategories();
    }

    /**
     * Get the data for the view.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'survey' => $this->survey,
            'statisticsData' => $this->isTargetTemplate
                ? $this->filterStatisticsDataForTargetTemplate()
                : $this->statisticsData,
            'isTableSurvey' => $this->isTableSurvey,
            'isTargetTemplate' => $this->isTargetTemplate,
            'isSmileyTemplate' => $this->isSmileyTemplate,
            'isCheckboxTemplate' => $this->isCheckboxTemplate,
            'tableCategories' => $this->tableCategories,
            'submissionCount' => $this->survey->submission_count
        ];
    }

    /**
     * Process the template types from the statistics data.
     *
     * @return void
     */
    protected function processTemplateTypes(): void
    {
        // Check the template name
        $templateName = $this->survey->feedback_template->name ?? '';

        // Set checkbox template flag based on template name
        if (str_contains($templateName, 'templates.feedback.checkbox')) {
            $this->isCheckboxTemplate = true;
        }

        // Process the statistics data for template types
        foreach ($this->statisticsData as $stat) {
            // Check for table type statistics
            if ($stat['template_type'] === 'table' &&
                isset($stat['data']['table_survey']) &&
                $stat['data']['table_survey'] === true) {
                $this->isTableSurvey = true;

                if (isset($stat['data']['table_categories']) && is_array($stat['data']['table_categories'])) {
                    $this->tableCategories = $stat['data']['table_categories'];
                }
            }

            // Check for target type statistics
            if ($stat['template_type'] === 'target') {
                $this->isTargetTemplate = true;
            }

            // Check for smiley type statistics
            if ($stat['template_type'] === 'smiley') {
                $this->isSmileyTemplate = true;
            }

            // Check for checkbox type statistics
            if ($stat['template_type'] === 'checkbox') {
                $this->isCheckboxTemplate = true;
            }
        }
    }

    /**
     * Process table categories to add hasResponses flag.
     *
     * @return void
     */
    protected function processTableCategories(): void
    {
        if (empty($this->tableCategories)) {
            return;
        }

        // Initialize categories response tracking
        $categoryHasResponses = [];

        // Go through each category and check for responses
        foreach ($this->tableCategories as $catKey => $category) {
            $categoryHasResponses[$catKey] = false;

            // Skip if no questions in this category
            if (empty($category['questions'])) {
                continue;
            }

            // Check each question for responses
            foreach ($category['questions'] as $stat) {
                // Range questions with numeric responses
                if ($stat['template_type'] === 'range' &&
                    isset($stat['data']['average_rating']) &&
                    is_numeric($stat['data']['average_rating'])) {
                    $categoryHasResponses[$catKey] = true;
                    break;
                }
                // Text questions with responses
                elseif (($stat['template_type'] === 'text' || $stat['template_type'] === 'textarea') &&
                        isset($stat['data']['response_count']) &&
                        $stat['data']['response_count'] > 0) {
                    $categoryHasResponses[$catKey] = true;
                    break;
                }
            }
        }

        // Add hasResponses flag to each category
        foreach ($this->tableCategories as $catKey => $category) {
            $this->tableCategories[$catKey]['hasResponses'] = $categoryHasResponses[$catKey] ?? false;
        }

        // Log if no categories have responses
        $hasAnyResponses = collect($categoryHasResponses)->contains(true);
        if (!$hasAnyResponses) {
            Log::warning('Table survey has no categories with responses', [
                'survey_id' => $this->survey->id,
                'categories' => array_keys($this->tableCategories)
            ]);
        }
    }

    /**
     * Filter out Open Feedback from statistics data for target templates.
     *
     * @return array
     */
    protected function filterStatisticsDataForTargetTemplate(): array
    {
        if (!$this->isTargetTemplate) {
            return $this->statisticsData;
        }

        return collect($this->statisticsData)
            ->filter(function($stat) {
                // Skip Open Feedback questions for target templates since they're shown in the tab
                return !(isset($stat['question']) && $stat['question'] &&
                        $stat['template_type'] === 'text' &&
                        $stat['question']->question === 'Open Feedback');
            })
            ->toArray();
    }
}