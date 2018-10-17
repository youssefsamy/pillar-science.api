<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->increments('id');
            $table->nestedSet();
            $table->unsignedInteger('size')->nullable();
            $table->string('type')->index();
            $table->unsignedInteger('team_id')->nullable();
            // Used to represent a user's personal folder in a specific team.
            $table->unsignedInteger('owner_id')->nullable();
            // Root directory of a project
            $table->unsignedInteger('project_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams');

            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
        });

        Schema::create('dataset_versions', function (Blueprint $table) {
            $table->increments('id');
            // The dataset to which this version belongs
            $table->unsignedInteger('dataset_id');
            // Defines the parent DatasetVersion at a specific version when this version was created
            $table->unsignedInteger('parent_version_id')->nullable();

            $table->string('name'); // Display name
            $table->string('path')->nullable(); // Underlying resource path in s3 or local for example
            // Reference to the dataset ('id') from which it was forked (copied)
            // $table->unsignedInteger('copied_from_dataset_id')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->boolean('deleted')->default(false);
            $table->unsignedInteger('originator_id')->nullable(); // Reference to the user who caused this version to be created
            $table->timestamps();

            $table->foreign('dataset_id')
                ->references('id')
                ->on('datasets');

            $table->foreign('parent_version_id')
                ->references('id')
                ->on('dataset_versions');

            $table->foreign('originator_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dataset_versions');
        Schema::dropIfExists('datasets');
    }
}
