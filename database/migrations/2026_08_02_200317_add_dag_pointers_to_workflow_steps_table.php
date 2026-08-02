<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->foreignId('next_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->foreignId('on_failure_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['next_step_id']);
            $table->dropForeign(['on_failure_step_id']);
            $table->dropColumn(['next_step_id', 'on_failure_step_id']);
        });
    }
};