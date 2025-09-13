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
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->enum('recipient_type', ['user', 'cabinet', 'all']);
            $table->unsignedBigInteger('recipient_id')->nullable(); // ID пользователя или кабинета
            $table->timestamps();
            
            $table->index('message_id');
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_recipients');
    }
};
