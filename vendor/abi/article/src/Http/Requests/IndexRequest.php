<?php

namespace Abi\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class IndexRequest extends FormRequest
{
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
