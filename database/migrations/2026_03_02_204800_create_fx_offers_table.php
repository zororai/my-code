<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFxOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fx_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('provider_name');
            
            // Source and Destination Accounts (stored as JSON arrays)
            $table->json('source_accounts')->nullable();
            $table->json('destination_accounts')->nullable();
            
            // Exchange Rates
            $table->decimal('buy_rate', 15, 6);
            $table->decimal('sell_rate', 15, 6);
            
            // Settlement Methods (stored as JSON array)
            $table->json('settlement_methods')->nullable();
            
            // Trade Value Limits
            $table->decimal('min_trade_value', 15, 2);
            $table->decimal('max_trade_value', 15, 2);
            
            // Available Amounts (stored as JSON array of {amount, currency} objects)
            $table->json('available_amounts')->nullable();
            
            // Trading Hours
            $table->time('open_time');
            $table->time('close_time');
            
            // Permissible Trading Currencies (stored as JSON array)
            $table->json('trading_currencies')->nullable();
            
            // Processing Fee
            $table->decimal('processing_fee_percentage', 5, 2);
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fx_offers');
    }
}
