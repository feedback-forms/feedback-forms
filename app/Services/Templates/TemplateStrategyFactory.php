<?php

namespace App\Services\Templates;

use App\Models\FeedbackTemplate;
use Illuminate\Support\Facades\Log;

class TemplateStrategyFactory
{
    /**
     * Maps template types to their corresponding strategy instances
     *
     * @var array<string, TemplateStrategy>
     */
    protected array $strategyMap = [];

    /**
     * @var DefaultTemplateStrategy
     */
    protected DefaultTemplateStrategy $defaultStrategy;

    /**
     * TemplateStrategyFactory constructor.
     *
     * @param TargetTemplateStrategy $targetStrategy Strategy for target template types
     * @param SmileyTemplateStrategy $smileyStrategy Strategy for smiley template types
     * @param TableTemplateStrategy $tableStrategy Strategy for table template types
     * @param DefaultTemplateStrategy $defaultStrategy Default fallback strategy
     */
    public function __construct(
        TargetTemplateStrategy $targetStrategy,
        SmileyTemplateStrategy $smileyStrategy,
        TableTemplateStrategy $tableStrategy,
        DefaultTemplateStrategy $defaultStrategy
    ) {
        // Map each type to its corresponding strategy
        $this->strategyMap = [
            'target' => $targetStrategy,
            'smiley' => $smileyStrategy,
            'table'  => $tableStrategy,
        ];

        $this->defaultStrategy = $defaultStrategy;
    }

    /**
     * Get the appropriate strategy for the given template type
     *
     * @param string|null $templateType The type of the template (from FeedbackTemplate::type)
     * @param string|null $templateName The name of the template (for backward compatibility)
     * @return TemplateStrategy
     */
    public function getStrategy(?string $templateType = null, ?string $templateName = null): TemplateStrategy
    {
        // First try to find by explicit type
        if ($templateType && isset($this->strategyMap[$templateType])) {
            $strategy = $this->strategyMap[$templateType];
            Log::info("Using " . get_class($strategy) . " for template type: " . $templateType);
            return $strategy;
        }

        // For backward compatibility, try to match by name pattern if type is not provided or not found
        if ($templateName) {
            // Check for each template type in the name
            foreach ($this->strategyMap as $type => $strategy) {
                if (preg_match('/templates\.feedback\.' . $type . '$/', $templateName) === 1) {
                    Log::info("Using " . get_class($strategy) . " for template name: " . $templateName);
                    return $strategy;
                }
            }
        }

        // Fallback to default strategy
        Log::info("Using default strategy for template. Type: " . ($templateType ?? 'null') . ", Name: " . ($templateName ?? 'null'));
        return $this->defaultStrategy;
    }

    /**
     * Get a list of all available template types
     *
     * @return array<string>
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->strategyMap);
    }
}