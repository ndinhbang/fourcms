<?php

namespace Abi\Article;

use DoubleThreeDigital\Runway\Runway;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
//        Actions\DeleteModel::class,
    ];

    protected $commands = [
//        Console\Commands\GenerateBlueprint::class,
    ];

    protected $fieldtypes = [
//        Fieldtypes\BelongsToFieldtype::class,
//        Fieldtypes\HasManyFieldtype::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
        'actions' => __DIR__.'/../routes/actions.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $scripts = [
//        __DIR__ . '/../resources/dist/js/cp.js',
    ];

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'article');
        $this->mergeConfigFrom(__DIR__ . '/../config/article.php', 'article');

        $this->publishes([
            __DIR__ . '/../config/article.php' => config_path('abi/article.php'),
        ], 'abi');

//        Statamic::booted(function () {
//            Runway::discoverResources();

            $this->registerPermissions();
            $this->registerNavigation();

//            if (Runway::usesRouting()) {
//                $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
//                    ->setRepository('runway-resources', Routing\ResourceRoutingRepository::class);
//            }
//        });
    }

    protected function registerPermissions()
    {
        Permission::register("View artices", function ($permission) {
            $permission->children([
                Permission::make("Edit artices")->children([
                    Permission::make("Create new artices"),
                    Permission::make("Delete artices"),
                ]),
            ]);
        })->group('article');
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
}
