<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentIdToProductSaleItemsTable extends Migration
{
    public function up()
    {
        Schema::table('product_sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('product_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('product_sale_items', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
}
