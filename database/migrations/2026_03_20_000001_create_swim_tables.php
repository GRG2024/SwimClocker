<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swim_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // optional session label
            $table->string('team_name'); // auto-generated: "20 mrt - Ploeg 1"
            $table->unsignedInteger('total_time_ms');
            $table->unsignedInteger('total_splits');
            $table->unsignedInteger('total_rounds');
            $table->unsignedInteger('total_distance_m')->default(0); // total meters swum
            $table->json('swimmers'); // ["Renko", "Jan", "Piet"]
            $table->timestamp('started_at');
            $table->timestamps();

            $table->index('team_name');
            $table->index('started_at');
        });

        Schema::create('swim_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swim_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('swimmer_index'); // 0, 1, 2
            $table->string('swimmer_name');
            $table->unsignedInteger('round'); // which round
            $table->unsignedInteger('split_number'); // sequential split #
            $table->unsignedInteger('lap_time_ms'); // individual lap time
            $table->unsignedInteger('total_time_ms'); // cumulative from start
            $table->timestamps();

            $table->index(['swim_session_id', 'swimmer_index']);
            $table->index('swimmer_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swim_splits');
        Schema::dropIfExists('swim_sessions');
    }
};
