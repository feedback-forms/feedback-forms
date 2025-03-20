<?php

namespace App\Services;

use App\Models\{Question, FeedbackTemplate, QuestionTemplate};
use App\Models\Feedback;
use App\Repositories\FeedbackRepository;
use App\Exceptions\ServiceException;
use App\Exceptions\SurveyNotAvailableException;
use App\Services\ErrorLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurveyService
{
    /**
     * @var Templates\TemplateStrategyFactory
     */
    protected $templateStrategyFactory;

    /**
     * @var SurveyResponseService
     */
    protected $surveyResponseService;

    /**
     * @var StatisticsService
     */
    protected $statisticsService;

    /**
     * @var FeedbackRepository
     */
    protected $feedbackRepository;

    /**
     * Constructor to initialize dependencies
     *
     * @param StatisticsService $statisticsService
     * @param Templates\TemplateStrategyFactory $templateStrategyFactory
     * @param SurveyResponseService $surveyResponseService
     * @param FeedbackRepository $feedbackRepository
     */
    public function __construct(
        StatisticsService $statisticsService,
        Templates\TemplateStrategyFactory $templateStrategyFactory,
        SurveyResponseService $surveyResponseService,
        FeedbackRepository $feedbackRepository
    ) {
        $this->statisticsService = $statisticsService;
        $this->templateStrategyFactory = $templateStrategyFactory;
        $this->surveyResponseService = $surveyResponseService;
        $this->feedbackRepository = $feedbackRepository;
    }
    /**
     * Create a new survey from template
     */
    public function createFromTemplate(array $surveyConfig, int $userId): Feedback
    {
        try {
            return DB::transaction(function () use ($surveyConfig, $userId) {
                try {
                    // Create the feedback/survey
                    $survey = $this->feedbackRepository->create([
                        'name' => $surveyConfig['name'] ?? null,
                        'user_id' => $userId,
                        'feedback_template_id' => $surveyConfig['template_id'],
                        'accesskey' => $this->feedbackRepository->generateUniqueAccessKey(),
                        'limit' => $surveyConfig['response_limit'] ?? -1,
                        'expire_date' => Carbon::parse($surveyConfig['expire_date']),
                        'school_year_id' => $surveyConfig['school_year_id'] ?? null,
                        'department_id' => $surveyConfig['department_id'] ?? null,
                        'grade_level_id' => $surveyConfig['grade_level_id'] ?? null,
                        'school_class_id' => $surveyConfig['school_class_id'] ?? null,
                        'subject_id' => $surveyConfig['subject_id'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    throw ServiceException::database(
                        'Failed to create survey from template',
                        ['user_id' => $userId, 'template_id' => $surveyConfig['template_id'] ?? null],
                        $e
                    );
                }

                try {
                    // Get the template
                    $template = FeedbackTemplate::findOrFail($surveyConfig['template_id']);
                    $templateName = $template->name ?? '';

                    // Get the appropriate template strategy for this template
                    $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);

                    // Use the strategy to create questions
                    $templateStrategy->createQuestions($survey, $surveyConfig);
                } catch (\Exception $e) {
                    throw ServiceException::businessLogic(
                        'Failed to initialize template strategy or create template questions',
                        [
                            'survey_id' => $survey->id ?? null,
                            'template_id' => $surveyConfig['template_id'] ?? null,
                            'template_name' => $templateName ?? 'unknown'
                        ],
                        $e
                    );
                }

                // If the template has predefined questions and the strategy didn't create any,
                // create them from the template (this handles the case where template questions
                // are defined but we don't have a specialized strategy for this template type)
                if ($survey->questions()->count() === 0) {
                    try {
                        // Reload template with questions to ensure we have the latest data
                        $template = FeedbackTemplate::with('questions.question_template')->findOrFail($surveyConfig['template_id']);

                        if ($template->questions->count() > 0) {
                            foreach ($template->questions as $index => $templateQuestion) {
                                Question::create([
                                    'feedback_template_id' => $surveyConfig['template_id'],
                                    'feedback_id' => $survey->id,
                                    'question_template_id' => $templateQuestion->question_template_id ?? null,
                                    'question' => $templateQuestion->question,
                                    'order' => $templateQuestion->order ?? ($index + 1),
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        throw ServiceException::database(
                            'Failed to create default questions from template',
                            [
                                'survey_id' => $survey->id,
                                'template_id' => $surveyConfig['template_id'],
                                'questions_count' => $template->questions->count() ?? 0
                            ],
                            $e
                        );
                    }
                }

                return $survey;
            });
        } catch (ServiceException $e) {
            // Re-throw ServiceExceptions as they're already properly formatted
            throw $e;
        } catch (\Exception $e) {
            // Wrap any other exceptions in our ServiceException
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['user_id' => $userId, 'template_id' => $surveyConfig['template_id'] ?? null]
            );
        }
    }


    /**
     * Validate if survey can be answered (not expired, within limits)
     *
     * @param Feedback $survey The survey to check
     * @return bool True if the survey can be answered
     * @throws SurveyNotAvailableException If the survey cannot be answered due to expiration or limits
     * @throws ServiceException If there's an unexpected error during validation
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        try {
            return $this->feedbackRepository->canBeAnswered($survey);
        } catch (SurveyNotAvailableException $e) {
            // Log the exception with additional context
            Log::warning($e->getMessage(), [
                'survey_id' => $survey->id,
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'submission_count' => $survey->submission_count
            ]);

            // Re-throw the exception
            throw $e;
        } catch (\Exception $e) {
            // Wrap any unexpected exceptions
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
    }

    /**
     * Store survey responses
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully
     * @throws ServiceException If there's an error during response storage
     */
    public function storeResponses(Feedback $survey, array $responses): bool
    {
        return $this->surveyResponseService->storeResponses($survey, $responses);
    }


    /**
     * Calculate statistics for a survey
     *
     * Delegates statistics calculation to the StatisticsService.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        return $this->statisticsService->calculateStatisticsForSurvey($survey);
    }
}
