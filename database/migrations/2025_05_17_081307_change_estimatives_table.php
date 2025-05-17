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
            $table->decimal('estimated_hours_optimistic', 8, 2)->nullable();
            $table->decimal('estimated_hours_pessimistic', 8, 2)->nullable();
            $table->decimal('estimated_hours_average', 8, 2)->nullable();
            $table->integer('total_value_optimistic')->nullable();
            $table->integer('total_value_pessimistic')->nullable();
            $table->integer('total_value_average')->nullable();

            $table->dropColumn('total_value');
            $table->dropColumn('estimated_hours');
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
