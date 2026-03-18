<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLostResolutionToLibraryRecords extends Migration
{
    public function up()
    {
        Schema::table('library_records', function (Blueprint $table) {
            $table->enum('resolution_type', ['replace', 'pay_fee'])->nullable()->after('notes');
            $table->decimal('fee_amount', 10, 2)->nullable()->after('resolution_type');
            $table->boolean('fee_paid')->default(false)->after('fee_amount');
            $table->timestamp('resolved_at')->nullable()->after('fee_paid');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
        });
    }

    public function down()
    {
        Schema::table('library_records', function (Blueprint $table) {
            $table->dropColumn(['resolution_type', 'fee_amount', 'fee_paid', 'resolved_at', 'resolution_notes']);
        });
    }
}
