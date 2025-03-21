<?php

/**
 * This script helps in migrating from snake_case relationship methods to camelCase
 *
 * It scans PHP files throughout the project and updates method call references
 * to use the camelCase version of the relationship methods.
 */

// Set up the base directory
$baseDir = dirname(__DIR__);

// Define the relationship method mappings (snake_case => camelCase)
$methodMappings = [
    'feedback_template()' => 'feedbackTemplate()',
    'grade_level()' => 'gradeLevel()',
    // Add more relationship methods as needed
];

// File patterns to look at
$filePatterns = [
    '*.php',
    '*.blade.php'
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
function processFile($filePath, $methodMappings) {
    echo "Processing: $filePath\n";

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changed = false;

    // Update the content - we need to be careful with the regex to only catch method calls
    foreach ($methodMappings as $oldMethod => $newMethod) {
        // Update method calls like $model->old_method() to $model->newMethod()
        $pattern = '/->(' . str_replace('()', '', $oldMethod) . ')\(/';
        $replacement = '->' . str_replace('()', '', $newMethod) . '(';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $changed = true;
            echo "  - Updated method call: ->$oldMethod -> ->$newMethod\n";
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
echo "Starting relationship method naming convention migration...\n";
echo "Base directory: $baseDir\n";
echo "Method mappings:\n";
foreach ($methodMappings as $old => $new) {
    echo "  - $old -> $new\n";
}
echo "\nScanning files...\n";

// Process all files
scanDirectory($baseDir, $filePatterns, $skipDirs, function($path) use ($methodMappings) {
    processFile($path, $methodMappings);
});

echo "\nMigration complete!\n";
echo "Next steps:\n";
echo "1. Check git diff to verify changes\n";
echo "2. Run tests to ensure backward compatibility\n";
echo "3. Update documentation to indicate migration progress\n";