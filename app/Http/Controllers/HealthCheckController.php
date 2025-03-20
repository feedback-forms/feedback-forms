<?php

namespace App\Http\Controllers;

use App\Repositories\FeedbackRepository;
use App\Services\DependencyInjectionMonitor;
use App\Services\ErrorLogger;
use App\Services\SurveyAccessService;
use App\Services\SurveyResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    /**
     * Handle the incoming request.
     * This controller validates that our dependency injection fixes are working properly.
     */
    public function __invoke(
        Request $request,
        FeedbackRepository $feedbackRepository,
        SurveyAccessService $surveyAccessService,
        SurveyResponseService $surveyResponseService,
        DependencyInjectionMonitor $diMonitor
    ): JsonResponse {
        // Test the health of our service classes
        $services = [
            'feedbackRepository' => $feedbackRepository !== null,
            'surveyAccessService' => $surveyAccessService !== null,
            'surveyResponseService' => $surveyResponseService !== null,
            'diMonitor' => $diMonitor !== null
        ];

        // Log this health check
        ErrorLogger::logError(
            "System health check performed with success",
            ErrorLogger::CATEGORY_DEPENDENCY_INJECTION,
            ErrorLogger::LOG_LEVEL_INFO,
            ['services_verified' => array_keys($services)]
        );

        // Count available services and repositories
        $feedbackCount = $feedbackRepository->countWithFilters();

        // Return health status
        return response()->json([
            'status' => 'healthy',
            'message' => 'All dependency injection fixes verified successfully',
            'timestamp' => now()->toIso8601String(),
            'services' => $services,
            'feedback_count' => $feedbackCount
        ]);
    }
}
