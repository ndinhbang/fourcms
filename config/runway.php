<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Configure the resources (models) you'd like to be available in Runway.
    |
    */

    'resources' => [
        \App\Models\Test::class => [
            'name' => 'Tests',
            'handle' => 'tests',
            'collection' => 'tests',
            'blueprint' => 'collections.tests.test',
            'nav' => [
                'section' => 'Bundles',
            ],
        ],
        \App\Models\ArticleCategory::class => [
            'name' => 'Article Categories',
            'handle' => 'article_categories',
            'blueprint' => 'collections.article_categories.article_category',
        ],
        \App\Models\Article::class => [
            'name' => 'Articles',
            'handle' => 'articles',
            'blueprint' => 'collections.articles.article',
        ],

        // \App\Models\Order::class => [
        //     'name' => 'Orders',

        //     'blueprint' => [
        //         'sections' => [
        //             'main' => [
        //                 'fields' => [
        //                     [
        //                         'handle' => 'price',
        //                         'field' => [
        //                             'type' => 'number',
        //                             'validate' => 'required',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disable Migrations?
    |--------------------------------------------------------------------------
    |
    | Should Runway's migrations be disabled?
    | (eg. not automatically run when you next vendor:publish)
    |
    */

    'disable_migrations' => false,

];
