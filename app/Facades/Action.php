<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Actions\ActionRepository;

/**
 * @method static mixed get($action)
 * @method static mixed all()
 * @method static mixed for($item, $context = [])
 * @method static mixed forBulk($items, $context = [])
 *
 * @see \App\Actions\ActionRepository
 */
class Action extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionRepository::class;
    }
}
