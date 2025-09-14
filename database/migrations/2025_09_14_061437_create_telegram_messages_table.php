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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->unique();
            $table->bigInteger('chat_id');
            $table->bigInteger('user_id');
            $table->text('text')->nullable();
            $table->timestamp('message_date');
            $table->boolean('processed')->default(false);
            $table->timestamps();
            
            $table->index(['chat_id', 'user_id']);
            $table->index('processed');
            $table->index('message_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};