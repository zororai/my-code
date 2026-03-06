<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeIsbnNullableInBookCopies extends Migration
{
    public function up()
    {
        // Check if the unique index exists before dropping
        $indexExists = DB::select("SHOW INDEX FROM book_copies WHERE Key_name = 'book_copies_isbn_unique'");
        
        Schema::table('book_copies', function (Blueprint $table) use ($indexExists) {
            if (!empty($indexExists)) {
                $table->dropUnique(['isbn']);
            }
            $table->string('isbn')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('book_copies', function (Blueprint $table) {
            $table->string('isbn')->nullable(false)->change();
            $table->unique('isbn');
        });
    }
}
