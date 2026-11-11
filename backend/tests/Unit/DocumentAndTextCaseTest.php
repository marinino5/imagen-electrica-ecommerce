<?php

namespace Tests\Unit;

use App\Enums\IdType;
use App\Support\Document;
use App\Support\TextCase;
use PHPUnit\Framework\TestCase;

class DocumentAndTextCaseTest extends TestCase
{
    public function test_a_cedula_is_shown_as_cc_and_a_nit_with_its_check_digit(): void
    {
        $this->assertSame('CC 1097490864', Document::describe(IdType::CC, '1097490864')['full']);
        $this->assertSame('NIT 860005224-6', Document::describe(IdType::NIT, '860005224', '6')['full']);
        $this->assertSame('NIT 860005224-6', Document::describe(IdType::NIT, '860005224')['full'], 'completa el dígito que falta');
        $this->assertSame('NIT 860005224-6', Document::describe(null, '860005224-6')['full']);
    }

    public function test_old_records_without_id_type_use_the_customer_type(): void
    {
        $natural = Document::describe(null, '900123456', null, 'natural');
        $this->assertSame('CC 900123456', $natural['full']);
        $this->assertSame('Persona natural', $natural['kind']);
        $this->assertSame('Empresa', Document::describe(null, '1097490864', null, 'company')['kind']);
    }

    public function test_without_any_hint_nine_digits_is_a_nit_and_ten_a_cedula(): void
    {
        $this->assertSame('NIT', Document::describe(null, '860005224')['short']);
        $this->assertSame('CC', Document::describe(null, '1097490864')['short']);
        $this->assertNull(Document::describe(null, '  '));
    }

    public function test_names_and_cities_get_capital_letters_and_common_accents(): void
    {
        $this->assertSame('María Hernández', TextCase::person('maria hernandez'));
        $this->assertSame('Juan de la Cruz', TextCase::person('JUAN DE LA CRUZ'));
        $this->assertSame('Bucaramanga', TextCase::place('bucaramanga'));
        $this->assertSame('San Gil', TextCase::place('SAN GIL'));
    }

    public function test_text_already_well_written_is_respected(): void
    {
        $this->assertSame('Mariana Nino', TextCase::person('Mariana Nino'));
        $this->assertSame('Ana María', TextCase::person('Ana María'));
        $this->assertSame('ABC Ltda', TextCase::company('ABC Ltda'));
        $this->assertSame('Ferretería Central S.A.S.', TextCase::company('ferretería central s.a.s.'));
        $this->assertNull(TextCase::person(null));
    }
}
