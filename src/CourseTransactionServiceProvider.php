<?php

namespace admin\course_transactions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CourseTransactionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerViewNamespaces();
        $this->registerMigrations();
        $this->registerConfigs();
        $this->registerPublishables();
        $this->registerAdminRoutes();
    }

    protected function registerViewNamespaces()
    {
        // Transaction views
        $this->loadViewsFrom([
            base_path('Modules/Transactions/resources/views'),
            resource_path('views/admin/transaction'),
            __DIR__ . '/../resources/views'
        ], 'transaction');

        // Purchase views
        $this->loadViewsFrom([
            base_path('Modules/Transactions/resources/views'),
            resource_path('views/admin/purchase'),
            __DIR__ . '/../resources/views'
        ], 'purchase');


        // Extra namespace for explicit usage
        if (is_dir(base_path('Modules/Transactions/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/Transactions/resources/views'), 'transactions-module');
        }
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $publishedMigrations = base_path('Modules/Transactions/database/migrations');
        if (is_dir($publishedMigrations)) {
            $this->loadMigrationsFrom($publishedMigrations);
        }
    }

    protected function registerConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/transaction.php', 'transaction.constants');
        $this->mergeConfigFrom(__DIR__ . '/../config/transaction.php', 'transactions.config');

        $publishedConfig = base_path('Modules/Transactions/config/transaction.php');
        if (file_exists($publishedConfig)) {
            $this->mergeConfigFrom($publishedConfig, 'transactions.config');
        }
    }

    protected function registerPublishables()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => base_path('Modules/Transactions/database/migrations'),
            __DIR__ . '/../resources/views'     => base_path('Modules/Transactions/resources/views/'),
            __DIR__ . '/../config/' => base_path('Modules/Transactions/config/'),
        ], 'transaction');

        $this->publishWithNamespaceTransformation();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();

        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                // Load routes from published module first, then fallback to package
                if (file_exists(base_path('Modules/Transactions/routes/web.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Transactions/routes/web.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                }
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\course_transactions\Console\Commands\PublishCourseTransactionsModuleCommand::class,
                \admin\course_transactions\Console\Commands\CheckModuleStatusCommand::class,
                \admin\course_transactions\Console\Commands\DebugCourseTransactionsCommand::class,
            ]);
        }
    }

    /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        $moduleBase = base_path('Modules/Transactions');
        $srcBase = __DIR__ . '/../src';

        // Define the files that need namespace transformation
        $filesWithNamespaces = [

            // Controllers
            "$srcBase/Controllers/TransactionManagerController.php"     => "$moduleBase/app/Http/Controllers/Admin/TransactionManagerController.php",
            "$srcBase/Controllers/CoursePurchaseManagerController.php"  => "$moduleBase/app/Http/Controllers/Admin/CoursePurchaseManagerController.php",

            // Models
            "$srcBase/Models/Transaction.php"       => "$moduleBase/app/Models/Transaction.php",
            "$srcBase/Models/CoursePurchase.php"    => "$moduleBase/app/Models/CoursePurchase.php",

            // Routes
            "$srcBase/routes/web.php" => "$moduleBase/routes/web.php",
        ];

        foreach ($filesWithNamespaces as $from => $to) {
            if (File::exists($from)) {
                // Ensure the destination directory exists
                $destinationDir = dirname($to);
                if (!File::isDirectory($destinationDir)) {
                    File::makeDirectory($destinationDir, 0755, true);
                }

                // Read the source file
                $content = File::get($from);

                // Transform namespaces based on file type
                if (str_contains($to, '/Controllers/')) {
                    $content = str_replace('namespace admin\transactions\Controllers;', 'namespace Modules\Transactions\app\Http\Controllers\Admin;', $content);
                    $content = str_replace('use admin\transactions\Models\\', 'use Modules\Transactions\app\Models\\', $content);
                } elseif (str_contains($to, '/Models/')) {
                    $content = str_replace('namespace admin\transactions\Models;', 'namespace Modules\Transactions\app\Models;', $content);
                } elseif (str_contains($to, '/routes/')) {
                    $content = str_replace('use admin\transactions\Controllers\\', 'use Modules\Transactions\app\Http\Controllers\Admin\\', $content);
                }

                // Write the transformed content
                File::put($to, $content);
            }
        }
    }

    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\transactions\\Controllers;'    => 'namespace Modules\\Transactions\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\transactions\\Models;'         => 'namespace Modules\\Transactions\\app\\Models;',

            // Use statements transformations
            'use admin\\transactions\\Controllers\\'         => 'use Modules\\Transactions\\app\\Http\\Controllers\\Admin\\',
            'use admin\\transactions\\Models\\'              => 'use Modules\\Transactions\\app\\Models\\',

            // Class references in routes
            'admin\\transactions\\Controllers\\TransactionManagerController' => 'Modules\\Transactions\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\transactions\\Models\\Transaction;',
            'use Modules\\transactions\\app\\Models\\Transaction;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        return str_replace(
            'namespace admin\\transactions\\Models;',
            'namespace Modules\\transactions\\app\\Models;',
            $content
        );
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\transactions\\Controllers\\TransactionManagerController',
            'Modules\\transactions\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
            $content
        );

        return $content;
    }
}
