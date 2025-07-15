<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->boolean('has_trial')->default(false)->after('recurrence_period');
            $table->date('trial_start_date')->nullable()->after('has_trial');
            $table->date('trial_end_date')->nullable()->after('trial_start_date');
            $table->enum('trial_status', ['active', 'expired', 'converted'])->nullable()->after('trial_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['has_trial', 'trial_start_date', 'trial_end_date', 'trial_status']);
        });
    }
};