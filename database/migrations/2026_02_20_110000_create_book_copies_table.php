<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookCopiesTable extends Migration
{
    public function up()
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('book_id');
            $table->string('isbn')->unique();
            $table->string('copy_number');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->text('condition_notes')->nullable();
            $table->enum('status', ['available', 'borrowed', 'reserved', 'lost', 'damaged'])->default('available');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        });

        // Update library_records to reference book_copy
        Schema::table('library_records', function (Blueprint $table) {
            $table->unsignedBigInteger('book_copy_id')->nullable()->after('book_id');
            $table->string('copy_isbn')->nullable()->after('book_number');
            $table->foreign('book_copy_id')->references('id')->on('book_copies')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('library_records', function (Blueprint $table) {
            $table->dropForeign(['book_copy_id']);
            $table->dropColumn(['book_copy_id', 'copy_isbn']);
        });

        Schema::dropIfExists('book_copies');
    }
}
