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
            $table->dropColumn('estimated_hours_optimistic')->nullable();
            $table->dropColumn('estimated_hours_pessimistic')->nullable();
            $table->dropColumn('estimated_hours_average')->nullable();

            $table->decimal('dev_estimated_hours_optimistic', 8, 2)->nullable();
            $table->decimal('dev_estimated_hours_pessimistic', 8, 2)->nullable();
            $table->decimal('dev_estimated_hours_average', 8, 2)->nullable();

            $table->decimal('qa_estimated_hours_optimistic', 8, 2)->nullable();
            $table->decimal('qa_estimated_hours_pessimistic', 8, 2)->nullable();
            $table->decimal('qa_estimated_hours_average', 8, 2)->nullable();

            $table->decimal('design_estimated_hours_optimistic', 8, 2)->nullable();
            $table->decimal('design_estimated_hours_pessimistic', 8, 2)->nullable();
            $table->decimal('design_estimated_hours_average', 8, 2)->nullable();

            $table->decimal('avg_estimated_hours_optimistic', 8, 2)->nullable();
            $table->decimal('avg_estimated_hours_pessimistic', 8, 2)->nullable();
            $table->decimal('avg_estimated_hours_average', 8, 2)->nullable();


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
