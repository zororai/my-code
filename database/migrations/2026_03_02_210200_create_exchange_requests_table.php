<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('fx_provider_id')->nullable();
            
            // Currency details
            $table->string('source_currency', 10);
            $table->string('destination_currency', 10);
            
            // Amount details
            $table->decimal('source_amount', 15, 2);
            $table->decimal('destination_amount', 15, 2)->nullable();
            $table->decimal('exchange_rate', 15, 6)->nullable();
            
            // Fee details
            $table->decimal('processing_fee', 15, 2)->default(0);
            $table->decimal('provider_fee', 15, 2)->default(0);
            $table->decimal('total_fees', 15, 2)->default(0);
            
            // Account details
            $table->string('user_source_account')->nullable();
            $table->string('user_destination_account')->nullable();
            $table->string('provider_source_account')->nullable();
            $table->string('provider_destination_account')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'user_payment_confirmed',
                'provider_payment_confirmed',
                'completed',
                'cancelled'
            ])->default('pending');
            
            // Transaction reference
            $table->string('transaction_reference')->unique()->nullable();
            
            // Payment confirmation details
            $table->timestamp('user_payment_confirmed_at')->nullable();
            $table->timestamp('provider_payment_confirmed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Rejection reason
            $table->text('rejection_reason')->nullable();
            
            // Additional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fx_provider_id')->references('id')->on('fx_providers')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('fx_provider_id');
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
        Schema::dropIfExists('exchange_requests');
    }
}
