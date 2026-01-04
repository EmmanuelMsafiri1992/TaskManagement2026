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
        // Table to store user activity heartbeats
        if (!Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('page_url', 500);
                $table->string('page_title', 255)->nullable();
                $table->timestamp('last_activity_at')->useCurrent();
                $table->boolean('is_active')->default(true);
                $table->string('session_id', 100)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'last_activity_at']);
                $table->index(['user_id', 'is_active']);
            });
        }

        // Table to store inactivity reports requiring user explanation
        if (!Schema::hasTable('inactivity_reports')) {
            Schema::create('inactivity_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('inactive_from')->useCurrent();
                $table->timestamp('inactive_until')->nullable();
                $table->timestamp('detected_at')->useCurrent();
                $table->enum('reason_type', [
                    'same_page',           // User stayed on same page too long
                    'computer_inactive',   // No mouse/keyboard activity
                    'power_outage',        // System detected gap in activity (power cut)
                    'session_gap'          // Gap between sessions
                ]);
                $table->string('page_url', 500)->nullable();
                $table->string('page_title', 255)->nullable();
                $table->integer('inactive_duration_minutes')->default(0);
                $table->text('user_explanation')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->boolean('is_pending')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'is_pending']);
                $table->index(['user_id', 'detected_at']);
            });
        }

        // Table to track user activity sessions for power outage detection
        if (!Schema::hasTable('user_activity_sessions')) {
            Schema::create('user_activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('session_id', 100);
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('last_heartbeat_at')->useCurrent();
                $table->timestamp('ended_at')->nullable();
                $table->boolean('graceful_logout')->default(false);
                $table->string('last_page_url', 500)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'session_id']);
                $table->index(['user_id', 'ended_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop tables that don't have conflicts with other migrations
        Schema::dropIfExists('inactivity_reports');
        Schema::dropIfExists('user_activity_logs');
        // user_sessions might be shared, so we don't drop it
    }
};
