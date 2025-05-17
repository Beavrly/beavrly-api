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
            $table->integer('hourly_rate_optimistic')->nullable();
            $table->integer('hourly_rate_pessimistic')->nullable();
            $table->integer('hourly_rate_average')->nullable();

            $table->dropColumn('hourly_rate');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
