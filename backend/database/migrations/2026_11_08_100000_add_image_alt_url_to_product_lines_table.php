<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Segunda imagen de la línea (otra vista del producto): la tarjeta del catálogo la muestra al pasar el mouse. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_lines', function (Blueprint $table) {
            $table->string('image_alt_url')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('product_lines', function (Blueprint $table) {
            $table->dropColumn('image_alt_url');
        });
    }
};
