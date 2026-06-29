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
        Schema::table('club_members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('club_role_id')->constrained()->nullOnDelete();
            $table->timestamp('fee_paid_at')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('fee_paid_at');
        });
    }
};
