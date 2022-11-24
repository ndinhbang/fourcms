<?php

namespace Abi\Article\Http\Controllers;

use Abi\Article\Facades\Action;
use Abi\Article\Facades\Article;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\ActionController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see \Statamic\Http\Controllers\CP\Collections\EntryActionController
 */
class ArticleActionController extends ActionController
{
    /**
     * @see \Statamic\Http\Controllers\CP\Collections\EntryActionController::run
     */
    public function run(Request $request)
    {
        $data = $request->validate([
            'action' => 'required',
            'selections' => 'required|array',
            'context' => 'sometimes',
        ]);

        $context = $data['context'] ?? [];

        $action = Action::get($request->action)->context($context);

        $validation = $action->fields()->validator();

        $request->replace($request->values)->validate($validation->rules());

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        $unauthorized = $items->reject(function ($item) use ($action) {
            return $action->authorize(User::current(), $item);
        });

        abort_unless($unauthorized->isEmpty(), 403, __('You are not authorized to run this action.'));

        $values = $action->fields()->addValues($request->all())->process()->values()->all();

        $response = $action->run($items, $values);

        if ($redirect = $action->redirect($items, $values)) {
            return ['redirect' => $redirect];
        } elseif ($download = $action->download($items, $values)) {
            return $download instanceof Response ? $download : response()->download($download);
        }

        if (is_string($response)) {
            return ['message' => $response];
        }

        return $response ?: [];
    }

    public function bulkActions(Request $request)
    {
        $data = $request->validate([
            'selections' => 'required|array',
            'context' => 'sometimes',
        ]);

        $context = $data['context'] ?? [];

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        return Action::forBulk($items, $context);
    }

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Article::find($item);
        });
    }
}
