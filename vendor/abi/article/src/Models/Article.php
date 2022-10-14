<?php

namespace Abi\Article\Models;

use Abi\Article\Entries\ArticleEntryQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Article extends Model
{
    protected $table = 'articles';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'data' => 'json',
        'published' => 'bool',
    ];

    public function origin()
    {
        return $this->belongsTo(static::class);
    }

    public function getAttribute($key)
    {
        // Because the import script was importing `updated_at` into the
        // json data column, we will explicitly reference other SQL
        // columns first to prevent errors with that bad data.
        if (in_array($key, ArticleEntryQueryBuilder::COLUMNS)) {
            return parent::getAttribute($key);
        }

        return Arr::get($this->getAttributeValue('data'), $key, parent::getAttribute($key));
    }
}
