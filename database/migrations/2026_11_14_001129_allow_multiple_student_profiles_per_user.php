<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropUnique(['user_id']);
            $table->string('student_name')->nullable();
        });

        DB::table('student_profiles')
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->select('student_profiles.id as profile_id', 'users.name as user_name')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    DB::table('student_profiles')
                        ->where('id', $profile->profile_id)
                        ->whereNull('student_name')
                        ->update(['student_name' => $profile->user_name]);
                }
            }, 'student_profiles.id', 'profile_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasMultipleProfiles = DB::table('student_profiles')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleProfiles) {
            throw new RuntimeException('Cannot restore one profile per account while accounts have multiple student profiles.');
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->unique('user_id');
            $table->dropIndex(['user_id']);
            $table->dropColumn('student_name');
        });
    }
};
