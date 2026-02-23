<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassFormatTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_format_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Color Names", "Greek Letters", "A-B-C Format")
            $table->enum('type', ['names', 'numeric', 'custom']); // Format type
            $table->text('values')->nullable(); // Comma-separated values for names/custom, or count for numeric
            $table->text('description')->nullable(); // Optional description
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_format_templates');
    }
}
