<?php

namespace App\Models;

/**
 * @deprecated Use QuestionTemplate instead
 *
 * As part of the ongoing migration to Laravel naming conventions (CamelCase),
 * this class is being phased out. It extends QuestionTemplate to maintain
 * backward compatibility during the transition, but will be removed in a future release.
 *
 * Migration Plan:
 * 1. All new code should use QuestionTemplate instead of Question_template
 * 2. The included scripts/fix_model_naming_conventions.php script can be used to automatically update references
 * 3. After all references are updated, this class will be removed in a future release
 *
 * @see \App\Models\QuestionTemplate
 * @see scripts/fix_model_naming_conventions.php
 */
class Question_template extends QuestionTemplate
{
    // This class extends QuestionTemplate to maintain backward compatibility
    // while following Laravel naming conventions
}
