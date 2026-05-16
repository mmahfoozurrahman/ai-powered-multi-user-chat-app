<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('active_model');
            $table->unsignedInteger('context_window');
            $table->unsignedInteger('max_output_tokens');
            $table->unsignedInteger('reserved_completion_tokens')->default(2048);
            $table->unsignedInteger('provider_reference_rpm')->nullable();
            $table->unsignedInteger('provider_reference_rpd')->nullable();
            $table->unsignedInteger('provider_reference_tpm')->nullable();
            $table->unsignedInteger('provider_reference_tpd')->nullable();
            $table->unsignedInteger('free_daily_message_limit')->default(100);
            $table->unsignedInteger('free_daily_token_limit')->default(12000);
            $table->unsignedInteger('free_monthly_token_limit')->default(150000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
