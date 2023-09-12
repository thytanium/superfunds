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
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('start_year');
            $table
                ->foreignId('manager_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table
                ->foreignId('company_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->json('aliases')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
