<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLearningObjectivesToTextInSyllabusTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('syllabus_topics', function (Blueprint $table) {
            $table->text('learning_objectives')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syllabus_topics', function (Blueprint $table) {
            $table->string('learning_objectives')->nullable()->change();
        });
    }
}
