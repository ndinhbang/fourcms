<?php

namespace Abi\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class IndexRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if ($filters = $this->filters) {
            $this->merge([
                'filters' => collect(json_decode(base64_decode($filters), true)),
            ]);
        }
    }

    public function authorize()
    {
        return User::current()->hasPermission("View articles")
            || User::current()->isSuper();
    }

    public function rules()
    {
        return [];
    }
}
