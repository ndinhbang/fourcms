<?php

namespace Abi\Article;

use Abi\Article\Entries\ArticleQueryBuilder;
use Abi\Article\Models\Article;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

/**
 * @see https://statamic.dev/extending/addons
 */
class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'article';

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
        'actions' => __DIR__.'/../routes/actions.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $commands = [
        \Abi\Article\Commands\AssetLinkCommand::class,
    ];

//    protected $stylesheets = [
//        __DIR__ . '/../resources/dist/css/cp.css',
//    ];

    protected $scripts = [
        __DIR__ . '/../resources/dist/js/cp.js',
    ];

//    protected $policies = [
//        Article::class           => ArticlePolicy::class,
//    ];

    public function bootAddon()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'article');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'article');
        $this->mergeConfigFrom(__DIR__ . '/../config/article.php', 'article');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerFieldTypes();
        $this->registerPermissions();
        $this->registerNavigation();


        if ($this->app->runningInConsole()) {
            // config
            $this->publishes([
                __DIR__ . '/../config/article.php' => config_path('abi/article.php'),
            ], 'article-config');

            // Lang
            $this->publishes([
                __DIR__.'/../database/migrations' => base_path('database/migrations'),
            ], 'article-migrations');

            // Assets
//            $this->publishes([
//                __DIR__.'/../resources/dist/css' => public_path('vendor/article/css'),
//            ], 'article-assets');
            $this->publishes([
                __DIR__.'/../resources/dist/js' => public_path('vendor/article/js'),
            ], 'article-assets');

            // Blueprints
            $this->publishes([
                __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
            ], 'article-blueprints');

            // Collections
            $this->publishes([
                __DIR__.'/../resources/collections' => base_path('content/collections'),
            ], 'article-collections');

            // Views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/article'),
            ], 'article-views');

            // Lang
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/article'),
            ], 'article-lang');
        }

//        Statamic::booted(function () {
//            Runway::discoverResources();
//            if (Runway::usesRouting()) {
//                $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
//                    ->setRepository('runway-resources', Routing\ResourceRoutingRepository::class);
//            }
//        });
    }

    protected function registerFieldTypes()
    {
        \Abi\Article\Fieldtypes\Articles::register();
    }

    protected function registerPermissions()
    {
        Permission::group('articles', __('article::cp.single_name'), function () {
            Permission::register('articles.view', function ($permission) {
                $permission
                    ->label(__('article::permissions.view'))
//                    ->description(__('butik::cp.permission_view_orders_description'))
                    ->children([
                        Permission::make('articles.create')
                            ->label(__('article::permissions.create')),
                        Permission::make('articles.update')
                            ->label(__('article::permissions.update')),
                        Permission::make('articles.delete')
                            ->label(__('article::permissions.delete')),
                    ]);
            });
        });
    }

    protected function registerNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->content('Article')
                ->section('Modules')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0.125 0.125 13.75 13.75" stroke-width="0.75" style="background-color: #ffffff0d"><g transform="matrix(0.9,0,0,0.9,0.7000000000000002,0.7000000000000002)"><g><line x1="13.5" y1="1" x2="9" y2="1" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></line><line x1="13.5" y1="4" x2="9" y2="4" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></line><line x1="13.5" y1="7" x2="9" y2="7" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></line><line x1="13.5" y1="13" x2="0.5" y2="13" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></line><line x1="13.5" y1="10" x2="0.5" y2="10" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></line><rect x="0.5" y="1" width="6" height="6" rx="0.5" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"></rect></g></g></svg>')
                ->route('article.index')
                ->can("View artices");
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ArticleQueryBuilder::class, function () {
            return new ArticleQueryBuilder(Article::query());
        });
    }
}
