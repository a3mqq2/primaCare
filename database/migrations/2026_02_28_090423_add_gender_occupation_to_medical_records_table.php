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
        Schema::table('medical_records', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('phone');
            $table->string('occupation', 255)->nullable()->after('gender');
            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex(['gender']);
            $table->dropColumn(['gender', 'occupation']);
        });
    }
};
