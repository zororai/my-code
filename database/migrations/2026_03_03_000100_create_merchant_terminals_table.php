<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantTerminalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_terminals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            
            // Terminal Details
            $table->string('terminal_id')->unique();
            $table->string('terminal_name');
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('merchant_category_code')->nullable();
            
            // Device Information
            $table->string('device_type')->default('mobile'); // mobile, tablet
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            
            // Location
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Zimbabwe');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            
            // Transaction Limits
            $table->decimal('daily_limit', 15, 2)->default(100000.00);
            $table->decimal('transaction_limit', 15, 2)->default(10000.00);
            $table->decimal('daily_processed', 15, 2)->default(0.00);
            
            // Statistics
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_volume', 20, 2)->default(0.00);
            
            // Settings
            $table->json('accepted_payment_methods')->nullable(); // card, mobile_money, qr
            $table->json('settings')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('terminal_id');
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
        Schema::dropIfExists('merchant_terminals');
    }
}
