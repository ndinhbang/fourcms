<?php

namespace Abi\Article\Facades;

use Illuminate\Support\Facades\Facade;
use Abi\Article\Actions\ActionRepository;

/**
 * @method static mixed get($action)
 * @method static mixed all()
 * @method static mixed for($item, $context = [])
 * @method static mixed forBulk($items, $context = [])
 *
 * @see \Abi\Article\Actions\ActionRepository
 */
class Action extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionRepository::class;
    }
}
