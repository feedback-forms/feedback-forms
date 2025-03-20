<?php

namespace App\Services\Templates;

use Illuminate\Support\Facades\Log;

class TemplateStrategyFactory
{
    /**
     * @var TemplateStrategy[]
     */
    protected array $strategies = [];

    /**
     * @var DefaultTemplateStrategy
     */
    protected DefaultTemplateStrategy $defaultStrategy;

    /**
     * TemplateStrategyFactory constructor.
     */
    public function __construct(
        TargetTemplateStrategy $targetStrategy,
        SmileyTemplateStrategy $smileyStrategy,
        TableTemplateStrategy $tableStrategy,
        DefaultTemplateStrategy $defaultStrategy
    ) {
        // Register all available strategies
        $this->strategies = [
            $targetStrategy,
            $smileyStrategy,
            $tableStrategy,
        ];

        $this->defaultStrategy = $defaultStrategy;
    }

    /**
     * Get the appropriate strategy for the given template name
     *
     * @param string $templateName The name of the template
     * @return TemplateStrategy
     */
    public function getStrategy(string $templateName): TemplateStrategy
    {
        // Try to find a strategy that can handle this template
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($templateName)) {
                Log::info("Using " . get_class($strategy) . " for template: " . $templateName);
                return $strategy;
            }
        }

        // Fallback to default strategy
        Log::info("Using default strategy for template: " . $templateName);
        return $this->defaultStrategy;
    }
}