<?php

/**
 * This script helps in migrating from snake_case model naming to camelCase (Laravel convention)
 *
 * It scans PHP files throughout the project and updates import statements and class references
 * to use the camelCase version of the model names.
 */

// Set up the base directory
$baseDir = dirname(__DIR__);

// Define the model mappings (snake_case => camelCase)
$modelMappings = [
    'Feedback_template' => 'FeedbackTemplate',
    'Question_template' => 'QuestionTemplate'
];

// File patterns to look at
$filePatterns = [
    '*.php'
];

// Directories to skip
$skipDirs = [
    'vendor',
    'node_modules',
    '.git',
    'storage/framework'
];

// Function to scan directory recursively
function scanDirectory($dir, $patterns, $skipDirs, $callback) {
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;

        // Skip directories in skipDirs
        if (is_dir($path) && in_array($item, $skipDirs)) {
            continue;
        }

        if (is_dir($path)) {
            scanDirectory($path, $patterns, $skipDirs, $callback);
        } else {
            // Check if the file matches any of the patterns
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $item)) {
                    $callback($path);
                    break;
                }
            }
        }
    }
}

// Function to process file
function processFile($filePath, $modelMappings) {
    echo "Processing: $filePath\n";

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changed = false;

    // Update the content
    foreach ($modelMappings as $oldModel => $newModel) {
        // Update use statements
        $pattern = '/use App\\\\Models\\\\' . $oldModel . ';/';
        $replacement = 'use App\\Models\\' . $newModel . ';';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $changed = true;
            echo "  - Updated import: $oldModel -> $newModel\n";
        }

        // Update class references - being careful to only match the class name (not substrings)
        $pattern = '/\b' . $oldModel . '::/';
        $replacement = $newModel . '::';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $changed = true;
            echo "  - Updated static method calls: $oldModel:: -> $newModel::\n";
        }

        // Update other references (such as in function parameters)
        $pattern = '/\(\\\\App\\\\Models\\\\' . $oldModel . ' /';
        $replacement = '(\\App\\Models\\' . $newModel . ' ';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $changed = true;
            echo "  - Updated type hints: \\App\\Models\\$oldModel -> \\App\\Models\\$newModel\n";
        }
    }

    // Only write back if content was changed
    if ($changed) {
        file_put_contents($filePath, $content);
        echo "  ✓ Updated file\n";
    } else {
        echo "  - No changes needed\n";
    }
}

// Main execution
echo "Starting model naming convention migration...\n";
echo "Base directory: $baseDir\n";
echo "Model mappings:\n";
foreach ($modelMappings as $old => $new) {
    echo "  - $old -> $new\n";
}
echo "\nScanning files...\n";

// Process all files
scanDirectory($baseDir, $filePatterns, $skipDirs, function($path) use ($modelMappings) {
    processFile($path, $modelMappings);
});

echo "\nMigration complete!\n";
echo "Next steps:\n";
echo "1. Check git diff to verify changes\n";
echo "2. Run tests to ensure backward compatibility\n";
echo "3. Update documentation to indicate migration progress\n";