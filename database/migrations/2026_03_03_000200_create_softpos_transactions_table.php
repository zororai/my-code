<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftposTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('softpos_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('merchant_terminal_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            
            // Transaction Details
            $table->string('transaction_id')->unique();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            
            // Payment Method
            $table->enum('payment_method', ['card', 'mobile_money', 'qr_code', 'nfc'])->default('card');
            $table->string('payment_provider')->nullable(); // Visa, Mastercard, EcoCash, etc.
            
            // Card Details (masked)
            $table->string('card_type')->nullable(); // Visa, Mastercard, etc.
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable();
            
            // Mobile Money Details
            $table->string('mobile_number')->nullable();
            $table->string('mobile_network')->nullable();
            
            // Transaction Status
            $table->enum('status', ['pending', 'processing', 'approved', 'declined', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('status_message')->nullable();
            $table->string('authorization_code')->nullable();
            
            // Fees
            $table->decimal('merchant_fee', 10, 2)->default(0.00);
            $table->decimal('processing_fee', 10, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            
            // Customer Information
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Receipt Information
            $table->text('receipt_data')->nullable();
            $table->string('receipt_number')->nullable();
            $table->boolean('receipt_sent')->default(false);
            
            // Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('merchant_terminal_id')->references('id')->on('merchant_terminals')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('merchant_terminal_id');
            $table->index('transaction_id');
            $table->index('reference_number');
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
        Schema::dropIfExists('softpos_transactions');
    }
}
