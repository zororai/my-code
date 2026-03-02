<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFxProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fx_providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            
            // Provider details
            $table->string('provider_name');
            $table->string('company_registration')->nullable();
            $table->string('license_number')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            
            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            
            // Status and verification
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Ratings and performance
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_volume', 20, 2)->default(0);
            
            // Processing details
            $table->string('average_processing_time')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->json('settlement_methods')->nullable();
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('is_active');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fx_providers');
    }
}
