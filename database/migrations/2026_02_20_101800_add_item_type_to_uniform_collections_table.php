<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddItemTypeToUniformCollectionsTable extends Migration
{
    public function up()
    {
        Schema::table('uniform_collections', function (Blueprint $table) {
            $table->string('item_type')->default('uniform')->after('id');
            $table->string('item_name')->nullable()->after('item_type');
            $table->string('academic_year')->nullable()->after('item_name');
            $table->string('term')->nullable()->after('academic_year');
        });

        // Use raw SQL to modify columns without doctrine/dbal
        DB::statement('ALTER TABLE uniform_collections MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE uniform_collections MODIFY product_name VARCHAR(255) NULL');
    }

    public function down()
    {
        Schema::table('uniform_collections', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'item_name', 'academic_year', 'term']);
        });

        DB::statement('ALTER TABLE uniform_collections MODIFY product_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE uniform_collections MODIFY product_name VARCHAR(255) NOT NULL');
    }
}
