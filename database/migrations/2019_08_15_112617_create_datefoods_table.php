<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatefoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datefoods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->unique();
            $table->string('zavtrak',100);
            $table->string('obed',100);
            $table->string('ujin',100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('datefoods');
    }
}
