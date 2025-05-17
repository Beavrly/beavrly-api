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
        Schema::table('scopes', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('estimatives', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
        });
    }
};
