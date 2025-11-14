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
        Schema::table('artists', function (Blueprint $table) {
            $table->boolean('claimed')->default(false);
            $table->string('claim_token')->nullable()->unique();
            $table->timestamp('claim_token_expires_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('slug')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_user_id']);
            $table->dropColumn([
                'claimed',
                'claim_token',
                'claim_token_expires_at',
                'claimed_at',
                'claimed_by_user_id',
                'slug'
            ]);
        });
    }
};




