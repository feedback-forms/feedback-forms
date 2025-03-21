# Model Naming Convention Migration

This directory contains scripts to help with migrating the codebase from mixed naming conventions to consistent Laravel naming conventions.

## Background

The Feedback Forms application currently maintains two parallel naming conventions for models:

1. Snake case: `Feedback_template`, `Question_template`
2. Camel case: `FeedbackTemplate`, `QuestionTemplate`

Similarly, relationship methods in models have inconsistent naming:

- `feedback_template()` and `feedbackTemplate()`
- `grade_level()` and `gradeLevel()`

This inconsistency creates technical debt and confusion, forcing developers to remember which naming style to use.

## Migration Scripts

### 1. Model Class Name Migration (`fix_model_naming_conventions.php`)

This script scans PHP files throughout the project and updates import statements and class references to use the camelCase version of the model names.

**Usage:**

```bash
cd /path/to/project
php scripts/fix_model_naming_conventions.php
```

The script will:
- Update `use App\Models\Feedback_template;` to `use App\Models\FeedbackTemplate;`
- Update static method calls like `Feedback_template::` to `FeedbackTemplate::`
- Update type hints in function parameters

### 2. Relationship Method Name Migration (`fix_relationship_methods.php`)

This script helps in migrating from snake_case relationship methods to camelCase by updating method call references.

**Usage:**

```bash
cd /path/to/project
php scripts/fix_relationship_methods.php
```

The script will:
- Update method calls like `$model->feedback_template()` to `$model->feedbackTemplate()`
- Update method calls like `$model->grade_level()` to `$model->gradeLevel()`

## Migration Plan

1. **Phase 1: Update Code References**
   - Run `fix_model_naming_conventions.php` to update all model class references
   - Run `fix_relationship_methods.php` to update all relationship method calls
   - Manually review the changes with `git diff`
   - Run tests to ensure backward compatibility

2. **Phase 2: Deprecation Period**
   - Keep legacy classes (`Feedback_template`, `Question_template`) as extending from the new classes
   - Mark them as `@deprecated` (already done)
   - Mark old relationship methods as `@deprecated` (already done)
   - Ensure all new code uses the new naming conventions

3. **Phase 3: Removal**
   - After sufficient time has passed (e.g., 3-6 months)
   - Remove the legacy classes and relationship methods
   - Update any remaining references

## Status

- [x] Created migration scripts
- [x] Updated `@deprecated` annotations with migration plan
- [ ] Run scripts on codebase
- [ ] Verify changes with tests
- [ ] Complete deprecation period
- [ ] Remove legacy code

## Best Practices Moving Forward

1. Follow Laravel naming conventions consistently:
   - Use camelCase for variables and methods
   - Use PascalCase (StudlyCase) for classes
   - Use snake_case for database columns

2. Avoid creating new aliases or compatibility layers
   - Use the standard Laravel naming conventions directly

3. Document with PHPDoc
   - Include proper type hints and return types
   - Use `@see` to reference related classes or methods