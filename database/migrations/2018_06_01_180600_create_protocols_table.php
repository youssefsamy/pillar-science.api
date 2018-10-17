<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProtocolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('content')->nullable();
            $table->unsignedInteger('user_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::create('dataset_protocol', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dataset_id');
            $table->unsignedInteger('protocol_id');
            $table->timestamps();

            $table->foreign('dataset_id')
                ->references('id')
                ->on('datasets');

            $table->foreign('protocol_id')
                ->references('id')
                ->on('protocols');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dataset_protocol');
        Schema::dropIfExists('protocols');
    }
}
