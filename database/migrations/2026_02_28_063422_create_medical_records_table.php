<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('national_id', 20);
            $table->string('phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('national_id');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
