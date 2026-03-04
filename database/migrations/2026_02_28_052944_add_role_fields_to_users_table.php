<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['system_admin', 'center_employee'])->default('center_employee')->after('password');
            $table->foreignId('center_id')->nullable()->constrained('centers')->nullOnDelete()->after('role');
            $table->boolean('is_center_manager')->default(false)->after('center_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn(['role', 'center_id', 'is_center_manager']);
        });
    }
};
