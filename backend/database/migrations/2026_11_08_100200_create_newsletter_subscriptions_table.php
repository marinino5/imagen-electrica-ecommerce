<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Suscripciones al boletín desde el pie de la tienda, con la prueba del consentimiento (Ley 1581 de 2012). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            // Autorización de tratamiento de datos: casilla marcada y cuándo se marcó
            $table->boolean('consent')->default(false);
            $table->timestamp('consented_at');
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
