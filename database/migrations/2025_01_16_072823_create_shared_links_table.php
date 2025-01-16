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
        Schema::create('shared_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_chart_id')->constrained('org_charts')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('org_chart_id');
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_links');
    }
};
