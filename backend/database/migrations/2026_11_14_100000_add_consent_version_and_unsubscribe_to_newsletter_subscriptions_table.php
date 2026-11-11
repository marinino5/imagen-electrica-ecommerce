<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suscriptores del boletín: versión del texto de consentimiento que aceptó la persona (para probar qué leyó y cuándo) y fecha de baja.
 * Las suscripciones anteriores quedan con la versión «2026-11-v1», el texto que ya mostraba el pie de la tienda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('newsletter_subscriptions', 'consent_version')) {
                $table->string('consent_version', 20)->default('2026-11-v1')->after('consent');
            }
            if (! Schema::hasColumn('newsletter_subscriptions', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('consented_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['consent_version', 'unsubscribed_at']);
        });
    }
};
