<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->renameColumn('name', 'name_ar');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('name_en')->after('name_ar');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->renameColumn('name', 'name_ar');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->string('name_en')->after('name_ar');
            $table->string('logo')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'logo']);
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->renameColumn('name_ar', 'name');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->renameColumn('name_ar', 'name');
        });
    }
};
