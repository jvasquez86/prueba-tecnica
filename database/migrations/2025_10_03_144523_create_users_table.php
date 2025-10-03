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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Nombre del usuario
            $table->string('email')->unique();               // Email único
            $table->string('password');                      // Password hasheado
            $table->decimal('saldo_inicial', 10, 2)->default(0); // Saldo inicial
            $table->rememberToken();                         // Token "remember me"
            $table->timestamps();                            // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
