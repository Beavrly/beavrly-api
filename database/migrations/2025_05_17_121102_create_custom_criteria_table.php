<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->nullable()->constrained('criteria')->nullOnDelete();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('estimative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_criteria');
    }
};
