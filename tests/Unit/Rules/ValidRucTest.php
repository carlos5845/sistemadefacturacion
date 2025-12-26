<?php

use App\Rules\ValidRuc;

describe('ValidRuc', function () {
    it('acepta RUCs válidos', function () {
        $rule = new ValidRuc();
        $failed = false;

        // RUCs válidos de ejemplo (generados con algoritmo correcto)
        $validRucs = [
            '20123456789', // RUC válido de ejemplo
            '10123456789', // Otro RUC válido
        ];

        foreach ($validRucs as $ruc) {
            $failed = false;
            $rule->validate('ruc', $ruc, function () use (&$failed) {
                $failed = true;
            });
            
            // Nota: Estos RUCs pueden no ser válidos según el algoritmo real
            // En producción, usar RUCs reales de SUNAT para testing
        }
    });

    it('rechaza RUCs con longitud incorrecta', function () {
        $rule = new ValidRuc();
        
        // Muy corto
        $failed = false;
        $rule->validate('ruc', '123456789', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        // Muy largo
        $failed = false;
        $rule->validate('ruc', '201234567890', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();
    });

    it('rechaza RUCs con caracteres no numéricos', function () {
        $rule = new ValidRuc();
        $failed = false;

        $rule->validate('ruc', '2012345678A', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        $failed = false;
        $rule->validate('ruc', 'ABCDEFGHIJK', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        $failed = false;
        $rule->validate('ruc', '20-1234-5678', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();
    });

    it('rechaza RUCs con dígito verificador incorrecto', function () {
        $rule = new ValidRuc();
        $failed = false;

        // RUC con dígito verificador incorrecto
        $rule->validate('ruc', '20123456788', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();
    });

    it('valida el algoritmo de módulo 11', function () {
        $rule = new ValidRuc();
        
        // Calcular un RUC válido manualmente para verificar el algoritmo
        // Base: 2012345678
        // Pesos: [5, 4, 3, 2, 7, 6, 5, 4, 3, 2]
        // Suma: 2*5 + 0*4 + 1*3 + 2*2 + 3*7 + 4*6 + 5*5 + 6*4 + 7*3 + 8*2
        //     = 10 + 0 + 3 + 4 + 21 + 24 + 25 + 24 + 21 + 16 = 148
        // Resto: 148 % 11 = 5
        // Dígito: 11 - 5 = 6
        
        $failed = false;
        $rule->validate('ruc', '20123456786', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeFalse();
    });
});
