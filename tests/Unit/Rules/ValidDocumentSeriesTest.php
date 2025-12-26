<?php

use App\Rules\ValidDocumentSeries;

describe('ValidDocumentSeries', function () {
    it('acepta series válidas de facturas (F001-F999)', function () {
        $rule = new ValidDocumentSeries('01');

        // Test cada serie - $failed debe quedar en false si es válida
        $failed1 = false;
        $rule->validate('series', 'F001', function () use (&$failed1) {
            $failed1 = true;  // Se llama solo si falla
        });
        expect($failed1)->toBeFalse('F001 debe ser válida para facturas');

        $failed2 = false;
        $rule->validate('series', 'F123', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeFalse('F123 debe ser válida para facturas');

        $failed3 = false;
        $rule->validate('series', 'F999', function () use (&$failed3) {
            $failed3 = true;
        });
        expect($failed3)->toBeFalse('F999 debe ser válida para facturas');
    });

    it('rechaza series inválidas de facturas', function () {
        $rule = new ValidDocumentSeries('01');

        // Serie con letra incorrecta - debe fallar
        $failed1 = false;
        $rule->validate('series', 'B001', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeTrue('B001 NO es válida para facturas');

        // Serie muy larga
        $failed2 = false;
        $rule->validate('series', 'F1234', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeTrue('F1234 es muy larga');

        // Serie muy corta
        $failed3 = false;
        $rule->validate('series', 'F01', function () use (&$failed3) {
            $failed3 = true;
        });
        expect($failed3)->toBeTrue('F01 es muy corta');

        // Serie con letras en lugar de números
        $failed4 = false;
        $rule->validate('series', 'FABC', function () use (&$failed4) {
            $failed4 = true;
        });
        expect($failed4)->toBeTrue('FABC tiene letras en lugar de números');
    });

    it('acepta series válidas de boletas (B001-B999)', function () {
        $rule = new ValidDocumentSeries('03');

        $failed1 = false;
        $rule->validate('series', 'B001', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeFalse('B001 debe ser válida para boletas');

        $failed2 = false;
        $rule->validate('series', 'B500', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeFalse('B500 debe ser válida para boletas');

        $failed3 = false;
        $rule->validate('series', 'B999', function () use (&$failed3) {
            $failed3 = true;
        });
        expect($failed3)->toBeFalse('B999 debe ser válida para boletas');
    });

    it('rechaza series inválidas de boletas', function () {
        $rule = new ValidDocumentSeries('03');

        // Serie con letra incorrecta
        $failed1 = false;
        $rule->validate('series', 'F001', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeTrue('F001 NO es válida para boletas');

        // Serie con formato incorrecto
        $failed2 = false;
        $rule->validate('series', 'B1234', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeTrue('B1234 tiene formato incorrecto');
    });

    it('acepta series válidas de notas de crédito', function () {
        $rule = new ValidDocumentSeries('07');

        // Nota: Para nota de crédito, el patrón es /^F[CD]\d{2}$/
        // Esto acepta FC01-FC99, FD01-FD99 (para facturas)
        // También BC01-BC99, BD01-BD99 (para boletas) si el patrón fuera /^[FB][CD]\d{2}$/
        // Revisando el código, el patrón actual es /^F[CD]\d{2}$/

        $failed1 = false;
        $rule->validate('series', 'FC01', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeFalse('FC01 debe ser válida');

        $failed2 = false;
        $rule->validate('series', 'FC99', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeFalse('FC99 debe ser válida');

        $failed3 = false;
        $rule->validate('series', 'FD01', function () use (&$failed3) {
            $failed3 = true;
        });
        expect($failed3)->toBeFalse('FD01 debe ser válida');
    });

    it('rechaza BC01 para notas de crédito tipo 07', function () {
        $rule = new ValidDocumentSeries('07');

        // BC01 NO es válida para tipo 07 según el patrón /^F[CD]\d{2}$/
        // Porque empieza con B, no con F
        $failed = false;
        $rule->validate('series', 'BC01', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue('BC01 NO es válida para tipo 07 (empieza con B)');
    });

    it('rechaza series inválidas de notas de crédito', function () {
        $rule = new ValidDocumentSeries('07');

        // Serie con 3 dígitos (debe ser 2)
        $failed1 = false;
        $rule->validate('series', 'FC001', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeTrue('FC001 tiene 3 dígitos, debe tener 2');

        // Serie con letra incorrecta
        $failed2 = false;
        $rule->validate('series', 'FA01', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeTrue('FA01 tiene letra A, debe ser C o D');
    });

    it('acepta series válidas de notas de débito', function () {
        $rule = new ValidDocumentSeries('08');

        // Nota: Para nota de débito, el patrón es /^B[CD]\d{2}$/
        // Esto acepta BC01-BC99, BD01-BD99 (para boletas)

        $failed1 = false;
        $rule->validate('series', 'BC01', function () use (&$failed1) {
            $failed1 = true;
        });
        expect($failed1)->toBeFalse('BC01 debe ser válida para tipo 08');

        $failed2 = false;
        $rule->validate('series', 'BD50', function () use (&$failed2) {
            $failed2 = true;
        });
        expect($failed2)->toBeFalse('BD50 debe ser válida para tipo 08');
    });

    it('rechaza FD01 para notas de débito tipo 08', function () {
        $rule = new ValidDocumentSeries('08');

        // FD01 NO es válida para tipo 08 según el patrón /^B[CD]\d{2}$/
        $failed = false;
        $rule->validate('series', 'FD01', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue('FD01 NO es válida para tipo 08 (empieza con F, no B)');
    });

    it('rechaza tipo de documento no reconocido', function () {
        $rule = new ValidDocumentSeries('99'); // Tipo no existente

        $failed = false;
        $rule->validate('series', 'F001', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue('Tipo 99 no es reconocido');
    });
});
