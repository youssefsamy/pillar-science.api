<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDeviceToRemoteFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('devices', 'remote_directories');

        Schema::table('remote_directories', function (Blueprint $table) {
            $table->string('computer_id');
            $table->renameColumn('device_secret', 'secret_key');
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->renameColumn('device_id', 'remote_directory_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('remote_directories', 'devices');
    }
}
