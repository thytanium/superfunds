<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('potential_duplicate_funds', function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId('offending_fund_id')
                ->constrained(table: 'funds')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table
                ->foreignId('related_fund_id')
                ->constrained(table: 'funds')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('offending_fund_name');
            $table->string('offending_manager_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potential_duplicate_funds');
    }
};
