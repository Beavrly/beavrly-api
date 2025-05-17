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
        Schema::create('scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcript_id')->nullable()->constrained()->onDelete('set null');
            $table->text('content');
            $table->string('source_file')->nullable();
            $table->enum('type', ['site', 'system', 'app'])->default('system');
            $table->enum('approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->tinyInteger('status')->default(1);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scopes');
    }
};
