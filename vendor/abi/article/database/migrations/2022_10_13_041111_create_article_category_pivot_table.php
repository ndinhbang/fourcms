<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_category_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('article_category_id')->index();
            $table->timestamps();

            $table->unique(['article_id', 'article_category_id'], 'unique_idx');

            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->cascadeOnDelete();
            $table->foreign('article_category_id')
                ->references('id')
                ->on('article_categories')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_category_pivot');
    }
};
