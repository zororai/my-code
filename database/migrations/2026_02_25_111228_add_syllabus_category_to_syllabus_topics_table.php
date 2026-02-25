<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyllabusCategoryToSyllabusTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('syllabus_topics', function (Blueprint $table) {
            $table->string('syllabus_category')->default('zimsec')->after('subject_id');
            $table->index('syllabus_category');
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
            $table->dropIndex(['syllabus_category']);
            $table->dropColumn('syllabus_category');
        });
    }
}
