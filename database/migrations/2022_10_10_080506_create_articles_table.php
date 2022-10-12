<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->string('content')->nullable();
            $table->string('slug')->nullable();
            $table->bigInteger('article_category_id')->nullable();
            $table->timestamps();
        });
    }

     /**
      * Reverse the migrations.
      */
     public function down()
     {
         Schema::dropIfExists('articles');
     }
}
