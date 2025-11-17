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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('celebration_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->enum('id_type', ['cc', 'ce', 'passport', 'other']);
            $table->string('id_number');
            $table->unique(['celebration_id', 'id_number']);
            $table->string('seat_type')->nullable();   
            $table->string('seat_number')->nullable();  
            $table->string('token')->unique();
            $table->string('qr_path')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
