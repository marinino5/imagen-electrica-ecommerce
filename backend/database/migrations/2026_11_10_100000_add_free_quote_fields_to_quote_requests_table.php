<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cotización libre: la persona describe lo que necesita sin elegir productos del catálogo.
 * Idempotente: cada columna y tabla se crea solo si no existe, así se puede reintentar tras un fallo a medias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('quote_requests', 'type')) {
                $table->string('type', 10)->default('catalog')->after('id'); // App\Enums\QuoteRequestType
            }
            if (! Schema::hasColumn('quote_requests', 'address')) {
                $table->string('address', 255)->nullable()->after('city');
            }
            if (! Schema::hasColumn('quote_requests', 'application')) {
                $table->string('application', 255)->nullable()->after('message');
            }
            if (! Schema::hasColumn('quote_requests', 'attachment_name')) {
                $table->string('attachment_name', 255)->nullable();
                $table->string('attachment_mime', 100)->nullable();
                $table->unsignedInteger('attachment_size')->nullable();
            }
        });

        // El correo es opcional en la cotización libre (basta el teléfono)
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // El adjunto vive en la base de datos (base64): el disco de Render se borra al reiniciar y R2 es fase 2.
        // Tabla aparte para que el listado del panel no cargue archivos de hasta 5 MB.
        if (! Schema::hasTable('quote_request_attachments')) {
            Schema::create('quote_request_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_id')->unique()->constrained()->cascadeOnDelete();
                $table->longText('content_base64');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_attachments');

        Schema::table('quote_requests', function (Blueprint $table) {
            foreach (['attachment_size', 'attachment_mime', 'attachment_name', 'application', 'address', 'type'] as $column) {
                if (Schema::hasColumn('quote_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
