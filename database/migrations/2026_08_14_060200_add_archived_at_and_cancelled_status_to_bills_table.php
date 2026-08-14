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
            $table->timestamp('archived_at')->nullable()->after('status');
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid')->change();
            $table->dropColumn('archived_at');
        });
    }
};
