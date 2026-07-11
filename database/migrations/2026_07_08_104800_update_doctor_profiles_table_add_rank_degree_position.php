<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropColumn('academic_title');
            $table->enum('academic_rank', ['none', 'PGS', 'GS'])->default('none')->after('doctor_code');
            $table->enum('degree', ['BS', 'ThS', 'TS', 'BSCK1', 'BSCK2', 'BSNT'])->default('BS')->after('academic_rank');
            $table->enum('current_position', ['INTERN', 'ATTENDING', 'CONSULTANT', 'DEPARTMENT_HEAD', 'EXPERT'])->default('ATTENDING')->after('degree');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->string('academic_title', 100)->nullable();
            $table->dropColumn(['academic_rank', 'degree', 'current_position']);
        });
    }
};
