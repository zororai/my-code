<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountTypeToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['individual', 'business'])->default('individual')->after('email');
            $table->string('business_name')->nullable()->after('account_type');
            $table->string('business_registration_number')->nullable()->after('business_name');
            $table->string('tax_id')->nullable()->after('business_registration_number');
            $table->index('account_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'business_name', 'business_registration_number', 'tax_id']);
        });
    }
}
