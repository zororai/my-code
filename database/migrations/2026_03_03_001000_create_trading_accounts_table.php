<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradingAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trading_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            
            // Account Type
            $table->enum('account_type', ['stock_broker', 'crypto_exchange', 'investment_platform'])->default('stock_broker');
            
            // Location & Market
            $table->string('country');
            $table->string('asset_type'); // stocks, bonds, commodities, real estate, cryptocurrency
            $table->string('financial_market')->nullable();
            
            // Broker/Platform Details
            $table->string('broker_name');
            $table->string('broker_code')->nullable();
            $table->string('broker_logo_url')->nullable();
            
            // Account Details
            $table->string('account_holder_name');
            $table->string('trading_account_number');
            $table->string('account_status')->default('active'); // active, inactive, suspended
            
            // Connection Details
            $table->enum('connection_type', ['linked', 'created'])->default('linked');
            $table->boolean('is_connected')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            
            // Portfolio Data
            $table->decimal('total_value', 20, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->json('holdings')->nullable(); // Array of assets
            
            // API/Integration
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->json('api_credentials')->nullable();
            
            // Consent & Terms
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('account_type');
            $table->index('broker_name');
            $table->index('is_connected');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_accounts');
    }
}
