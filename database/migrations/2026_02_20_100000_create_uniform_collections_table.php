<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniformCollectionsTable extends Migration
{
    public function up()
    {
        Schema::create('uniform_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sale_id')->nullable();
            $table->string('product_name');
            $table->string('size')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['pending', 'collected', 'returned'])->default('pending');
            $table->timestamp('collected_at')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_sale_id')->references('id')->on('product_sales')->onDelete('set null');
            $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('uniform_collections');
    }
}
