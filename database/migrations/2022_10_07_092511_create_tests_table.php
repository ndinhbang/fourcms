<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->string('content')->nullable();
            $table->string('author')->nullable();
            $table->string('template')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });
    }

     /**
      * Reverse the migrations.
      */
     public function down()
     {
         Schema::dropIfExists('tests');
     }
}
