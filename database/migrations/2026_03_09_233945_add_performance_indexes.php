<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('center_id');
            $table->index('role');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->index('center_id');
            $table->index('created_by');
            $table->index(['center_id', 'created_at']);
        });

        Schema::table('dispensings', function (Blueprint $table) {
            $table->index('medical_record_id');
            $table->index('medicine_id');
            $table->index('dispensed_by');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['center_id']);
            $table->dropIndex(['role']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex(['center_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['center_id', 'created_at']);
        });

        Schema::table('dispensings', function (Blueprint $table) {
            $table->dropIndex(['medical_record_id']);
            $table->dropIndex(['medicine_id']);
            $table->dropIndex(['dispensed_by']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
        });
    }
};
