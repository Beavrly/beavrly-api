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
        Schema::create('estimatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scope_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['site', 'system', 'app'])->default('system');
            $table->text('content');
            $table->string('source_file')->nullable();
            $table->decimal('estimated_hours', 8, 2);
            $table->integer('hourly_rate');
            $table->integer('total_value');
            $table->json('additional_context')->nullable();
            $table->timestamps();
            $table->tinyInteger('status')->default(1);
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimatives');
    }
};
