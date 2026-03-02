<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFxProviderAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fx_provider_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fx_provider_id');
            
            // Account details
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('currency', 10);
            $table->string('bank_name');
            $table->string('account_type')->default('exchange'); // exchange, settlement, reserve
            
            // Balance information
            $table->decimal('current_balance', 20, 2)->default(0);
            $table->decimal('available_balance', 20, 2)->default(0);
            $table->decimal('reserved_balance', 20, 2)->default(0);
            
            // Limits
            $table->decimal('daily_limit', 15, 2)->nullable();
            $table->decimal('monthly_limit', 15, 2)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('fx_provider_id')->references('id')->on('fx_providers')->onDelete('cascade');
            
            // Indexes
            $table->index('fx_provider_id');
            $table->index('currency');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fx_provider_accounts');
    }
}
