<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de facturación con los nombres y valores EXACTOS de la API de clientes de Siigo Nube (POST /v1/customers, developers.siigo.com):
 * person_type (Person / Company), id_type (códigos 13 CC, 31 NIT, 22 CE, 41 pasaporte), identification, check_digit (solo NIT),
 * name (nombres y apellidos, o razón social), commercial_name, vat_responsible, fiscal_responsibilities (R-99-PN, O-13, O-15, O-23, O-47),
 * address (address, city{country_code, state_code, city_code}, postal_code), phones y contacts.
 *
 * Convierte lo que dejó la versión anterior (billing_doc_type «CC», fiscal_responsibility «vat_responsible»…) y la deja idempotente:
 * cada paso solo corre si falta. En orders la ciudad pasa a ser opcional (quien no pide factura y recoge en tienda no la da) y se marca
 * si el pedido pidió factura (needs_invoice).
 */
return new class extends Migration
{
    private const ID_TYPES = ['CC' => '13', 'NIT' => '31', 'CE' => '22', 'PP' => '41'];

    /** responsabilidad anterior => [vat_responsible, códigos de Siigo] */
    private const FISCAL = [
        'vat_responsible' => [true, ['R-99-PN']],
        'not_vat_responsible' => [false, ['R-99-PN']],
        'large_taxpayer' => [true, ['O-13']],
        'self_withholder' => [true, ['O-15']],
        'simple_regime' => [false, ['O-47']],
    ];

    public function up(): void
    {
        $this->orders();
        $this->customers();
    }

    private function orders(): void
    {
        $this->add('orders', [
            'needs_invoice' => ['boolean', null],
            'billing_person_type' => ['string', 7],
            'billing_country_code' => ['string', 2],
            'billing_postal_code' => ['string', 10],
            'billing_commercial_name' => ['string', 100],
            'billing_vat_responsible' => ['boolean', null],
            'billing_fiscal_responsibilities' => ['json', null],
            'billing_contact_first_name' => ['string', 50],
            'billing_contact_last_name' => ['string', 50],
        ]);

        $this->rename('orders', ['billing_doc_type' => 'billing_id_type', 'billing_doc_number' => 'billing_identification', 'billing_doc_dv' => 'billing_check_digit', 'billing_department_code' => 'billing_state_code', 'delivery_department_code' => 'delivery_state_code']);

        if (Schema::hasColumn('orders', 'billing_id_type')) {
            foreach (self::ID_TYPES as $old => $code) {
                DB::table('orders')->where('billing_id_type', $old)->update(['billing_id_type' => $code]);
            }
            DB::table('orders')->whereNotNull('billing_id_type')->update(['needs_invoice' => true, 'billing_country_code' => DB::raw("coalesce(billing_country_code, 'CO')")]);
            DB::table('orders')->whereNotNull('billing_id_type')->whereNull('billing_person_type')->update([
                'billing_person_type' => DB::raw("case when customer_type = 'company' then 'Company' else 'Person' end"),
            ]);
        }

        if (Schema::hasColumn('orders', 'fiscal_responsibility')) {
            foreach (self::FISCAL as $old => [$vat, $codes]) {
                DB::table('orders')->where('fiscal_responsibility', $old)->update(['billing_vat_responsible' => $vat, 'billing_fiscal_responsibilities' => json_encode($codes)]);
            }
            Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('fiscal_responsibility'));
        }

        Schema::table('orders', fn (Blueprint $t) => $t->string('city', 100)->nullable()->change());
    }

    private function customers(): void
    {
        $this->add('customers', [
            'vat_responsible' => ['boolean', null],
            'fiscal_responsibilities' => ['json', null],
            'postal_code' => ['string', 10],
            'commercial_name' => ['string', 100],
            'delivery_addresses' => ['json', null], // direcciones de entrega guardadas (hasta 5), para autocompletar el paso 1
        ]);

        $this->rename('customers', ['doc_type' => 'id_type', 'doc_dv' => 'check_digit', 'department_code' => 'state_code']);

        if (Schema::hasColumn('customers', 'id_type')) {
            foreach (self::ID_TYPES as $old => $code) {
                DB::table('customers')->where('id_type', $old)->update(['id_type' => $code]);
            }
        }

        if (Schema::hasColumn('customers', 'fiscal_responsibility')) {
            foreach (self::FISCAL as $old => [$vat, $codes]) {
                DB::table('customers')->where('fiscal_responsibility', $old)->update(['vat_responsible' => $vat, 'fiscal_responsibilities' => json_encode($codes)]);
            }
            Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('fiscal_responsibility'));
        }
    }

    public function down(): void
    {
        // Columnas con los nombres de Siigo: no se revierten a los anteriores
    }

    /**
     * Agrega solo las columnas que faltan: nombre => [tipo, longitud o null].
     *
     * @param  array<string, array{0: string, 1: ?int}>  $columns
     */
    private function add(string $table, array $columns): void
    {
        foreach ($columns as $name => [$type, $length]) {
            if (Schema::hasColumn($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($name, $type, $length) {
                $column = $length === null ? $t->{$type}($name) : $t->{$type}($name, $length);
                $column->nullable();
                if ($name === 'needs_invoice') {
                    $column->nullable(false)->default(false);
                }
            });
        }
    }

    /** @param  array<string, string>  $map  nombre anterior => nombre nuevo */
    private function rename(string $table, array $map): void
    {
        foreach ($map as $from => $to) {
            if (Schema::hasColumn($table, $from) && ! Schema::hasColumn($table, $to)) {
                Schema::table($table, fn (Blueprint $t) => $t->renameColumn($from, $to));
            }
        }
    }
};
