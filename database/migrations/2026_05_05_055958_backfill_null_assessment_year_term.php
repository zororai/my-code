<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BackfillNullAssessmentYearTerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // All 259 assessments created Feb–Mar 2026 belong to first term 2026.
        // Back-fill so the strict year/term filter on the dashboard can find them.
        DB::table('assessments')
            ->whereNull('academic_year')
            ->whereNull('term')
            ->update([
                'academic_year' => 2026,
                'term'          => 'first',
            ]);
    }

    public function down()
    {
        DB::table('assessments')
            ->where('academic_year', 2026)
            ->where('term', 'first')
            ->update([
                'academic_year' => null,
                'term'          => null,
            ]);
    }
}
