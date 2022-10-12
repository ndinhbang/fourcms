<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->string('slug')->nullable();
            $table->boolean('published')->default(1);
            $table->timestamps();
        });
    }

     /**
      * Reverse the migrations.
      */
     public function down()
     {
         Schema::dropIfExists('article_categories');
     }
}
