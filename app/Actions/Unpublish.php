<?php

namespace App\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Unpublish extends Action
{
    public function visibleTo($item): bool
    {
        return $item instanceof Entry && $item->published();
    }

    public function visibleToBulk($items): bool
    {
        if ($items->whereInstanceOf(Entry::class)->count() !== $items->count()) {
            return false;
        }

        if ($items->reject->published()->count() === $items->count()) {
            return false;
        }

        return true;
    }

    public function authorize($user, $entry): bool
    {
        return $user->can('publish', $entry);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to unpublish this entry?|Are you sure you want to unpublish these :count entries?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Unpublish Entry|Unpublish :count Entries';
    }

    public function run($entries, $values)
    {
        $entries->each(function ($entry) {
            $entry->unpublish(['user' => User::current()]);
        });
    }
}
