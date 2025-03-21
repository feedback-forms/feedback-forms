<?php

namespace App\Services;

use App\Models\{Question, FeedbackTemplate, QuestionTemplate};
use App\Models\Feedback;
use App\Repositories\FeedbackRepository;
use App\Exceptions\ServiceException;
use App\Services\SurveyAccessService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service responsible for creating surveys from templates
 *
 * This service handles the creation of surveys from templates, including
 * generating access keys and creating questions based on template strategies.
 */
class SurveyCreationService
{
    /**
     * @var Templates\TemplateStrategyFactory
     */
    protected $templateStrategyFactory;

    /**
     * @var FeedbackRepository
     */
    protected $feedbackRepository;

    /**
     * @var SurveyAccessService
     */
    protected $surveyAccessService;

    /**
     * Constructor to initialize dependencies
     *
     * @param Templates\TemplateStrategyFactory $templateStrategyFactory
     * @param FeedbackRepository $feedbackRepository
     * @param SurveyAccessService $surveyAccessService
     */
    public function __construct(
        Templates\TemplateStrategyFactory $templateStrategyFactory,
        FeedbackRepository $feedbackRepository,
        SurveyAccessService $surveyAccessService
    ) {
        $this->templateStrategyFactory = $templateStrategyFactory;
        $this->feedbackRepository = $feedbackRepository;
        $this->surveyAccessService = $surveyAccessService;
    }

    /**
     * Create a new survey from template
     *
     * @param array $surveyConfig The survey configuration data
     * @param int $userId The ID of the user creating the survey
     * @return Feedback The created survey
     * @throws ServiceException If there's an error during survey creation
     */
    public function createFromTemplate(array $surveyConfig, int $userId): Feedback
    {
        try {
            return DB::transaction(function () use ($surveyConfig, $userId) {
                try {
                    // Generate a unique access key using the access service
                    $accessKey = $this->surveyAccessService->generateAccessKey();

                    // Create the feedback/survey
                    $survey = $this->feedbackRepository->create([
                        'name' => $surveyConfig['name'] ?? null,
                        'user_id' => $userId,
                        'feedback_template_id' => $surveyConfig['template_id'],
                        'accesskey' => $accessKey,
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
}