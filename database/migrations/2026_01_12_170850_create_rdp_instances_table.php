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
        Schema::create('rdp_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aws_account_id')->constrained()->onDelete('cascade');
            $table->string('instance_id')->unique();
            $table->string('region')->default('us-east-1');
            $table->string('key_name');
            $table->string('group_id')->nullable();
            $table->string('public_ip')->nullable();
            $table->string('username')->default('Administrator');
            $table->text('password')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rdp_instances');
    }
};
