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
        Schema::table('estimatives', function (Blueprint $table) {
            $table->string('title', 200)->nullable();
        });

        Schema::table('scopes', function (Blueprint $table) {
            $table->string('title', 200)->nullable();
        });

        Schema::table('transcripts', function (Blueprint $table) {
            $table->string('title', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
