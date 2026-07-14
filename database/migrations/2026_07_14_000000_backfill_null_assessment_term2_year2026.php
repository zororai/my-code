<?php

use Illuminate\Database\Migrations\Migration;

class BackfillNullAssessmentTerm2Year2026 extends Migration
{
    public function up()
    {
        DB::table('assessments')
            ->whereNull('academic_year')
            ->whereNull('term')
            ->update([
                'academic_year' => 2026,
                'term'          => 'second',
            ]);
    }

    public function down()
    {
        DB::table('assessments')
            ->where('academic_year', 2026)
            ->where('term', 'second')
            ->update([
                'academic_year' => null,
                'term'          => null,
            ]);
    }
}
