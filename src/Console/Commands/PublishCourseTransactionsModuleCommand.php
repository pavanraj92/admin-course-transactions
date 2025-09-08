<?php

namespace admin\course_transactions\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishCourseTransactionsModuleCommand extends Command
{
    protected $signature = 'transactions:publish {--force : Force overwrite existing files}';
    protected $description = 'Publish Transactions module files with proper namespace transformation';

    public function handle()
    {
        $this->info('Publishing Transactions module files...');

        // Check if module directory exists
        $moduleDir = base_path('Modules/Transactions');
        if (!File::exists($moduleDir)) {
            File::makeDirectory($moduleDir, 0755, true);
        }

        // Publish with namespace transformation
        $this->publishWithNamespaceTransformation();

        // Publish other files
        $this->call('vendor:publish', [
            '--tag' => 'transaction',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info('Transactions module published successfully!');
        $this->info('Please run: composer dump-autoload');
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // Go up to packages/admin/products/src

        $filesWithNamespaces = [
            // Controllers
            $basePath . '/Controllers/CoursePurchaseManagerController.php' => base_path('Modules/Transactions/app/Http/Controllers/Admin/CoursePurchaseManagerController.php'),
            $basePath . '/Controllers/TransactionManagerController.php' => base_path('Modules/Transactions/app/Http/Controllers/Admin/TransactionManagerController.php'),

            // Models
            $basePath . '/Models/CoursePurchase.php' => base_path('Modules/Transactions/app/Models/CoursePurchase.php'),
            $basePath . '/Models/Transaction.php' => base_path('Modules/Transactions/app/Models/Transaction.php'),

            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/Transactions/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($destination));

                $content = File::get($source);
                $content = $this->transformNamespaces($content, $source);

                File::put($destination, $content);
                $this->info("Published: " . basename($destination));
            } else {
                $this->warn("Source file not found: " . $source);
            }
        }
    }

    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\course_transactions\\Controllers;' => 'namespace Modules\\Transactions\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\course_transactions\\Models;' => 'namespace Modules\\Transactions\\app\\Models;',

            // Use statements transformations
            'use admin\\course_transactions\\Controllers\\' => 'use Modules\\Transactions\\app\\Http\\Controllers\\Admin\\',
            'use admin\\course_transactions\\Models\\' => 'use Modules\\Transactions\\app\\Models\\',

            // Class references in routes
            'admin\\course_transactions\\Controllers\\CoursePurchaseManagerController' => 'Modules\\Transactions\\app\\Http\\Controllers\\Admin\\CoursePurchaseManagerController',
            'admin\\course_transactions\\Controllers\\TransactionManagerController' => 'Modules\\Transactions\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = str_replace(
                'use admin\\course_transactions\\Models\\CoursePurchase;',
                'use Modules\\Transactions\\app\\Models\\CoursePurchase;',
                $content
            );
            $content = str_replace(
                'use admin\\course_transactions\\Models\\Transaction;',
                'use Modules\\Transactions\\app\\Models\\Transaction;',
                $content
            );
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = str_replace(
                'use admin\users\Models\User;',
                'use Modules\\Users\\app\\Models\\User;',
                $content
            );
            $content = str_replace(
                'use admin\courses\Models\Course;',
                'use Modules\\Courses\\app\\Models\\Course;',
                $content
            );
            $content = str_replace(
                'admin\course_transactions\Models\Transaction;',
                'use Modules\\Transactions\\app\\Models\\Transaction;',
                $content
            );
        }

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add module namespace to autoload
        if (!isset($composer['autoload']['psr-4']['Modules\\Transactions\\'])) {
            $composer['autoload']['psr-4']['Modules\\Transactions\\'] = 'Modules/Transactions/app/';

            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated composer.json autoload');
        }
    }
}