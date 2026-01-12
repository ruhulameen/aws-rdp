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
        Schema::create('aws_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name'); // e.g., "Marketing Team", "Client-A"
            $table->text('access_key');     // Encrypted
            $table->text('secret_key');     // Encrypted
            $table->string('default_region')->default('us-east-1');
            $table->string('status')->default('active'); // active, suspended, invalid
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_accounts');
    }
};
