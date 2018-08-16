<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContainersTable extends Migration
{
    public function up()
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->longText('code')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }
}
