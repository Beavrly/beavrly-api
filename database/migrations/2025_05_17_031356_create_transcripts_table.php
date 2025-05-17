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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['site', 'system', 'app'])->default('system');
            $table->text('content');
            $table->string('source_file')->nullable();
            $table->timestamps();
            $table->tinyInteger('status')->default(1);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
