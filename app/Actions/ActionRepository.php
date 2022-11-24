<?php

namespace App\Actions;

use Statamic\Facades\User;

/**
 * @see \Statamic\Actions\ActionRepository
 */
class ActionRepository
{
    protected $actions = [
        'delete' => Delete::class,
        'delete_multisite_entry' => DeleteMultisiteEntry::class,
        'publish' => Publish::class,
        'unpublish' => Unpublish::class,
    ];

    public function get($action)
    {
        if ($class = collect($this->actions)->get($action)) {
            return app($class);
        }
    }

    /**
     * Các hành động có thể thực hiện trên từng bản ghi trong danh sách
     * @return mixed
     */
    public function all()
    {
        return collect($this->actions)->map(function ($class) {
            return app($class);
        })->values();
    }

    public function for($item, $context = [])
    {
        return $this->all()
            ->each->context($context)
            ->filter->visibleTo($item)
            ->filter->authorize(User::current(), $item)
            ->values();
    }

    public function forBulk($items, $context = [])
    {
        return $this->all()
            ->each->context($context)
            ->filter->visibleToBulk($items)
            ->filter->authorizeBulk(User::current(), $items)
            ->values();
    }
}
