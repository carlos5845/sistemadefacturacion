<?php

use App\Rules\ValidDni;

describe('ValidDni', function () {
    it('acepta DNIs válidos de 8 dígitos', function () {
        $rule = new ValidDni();
        $failed = false;

        $validDnis = [
            '12345678',
            '87654321',
            '40123456',
            '70123456',
        ];

        foreach ($validDnis as $dni) {
            $failed = false;
            $rule->validate('dni', $dni, function () use (&$failed) {
                $failed = true;
            });
            expect($failed)->toBeFalse();
        }
    });

    it('rechaza DNIs con longitud incorrecta', function () {
        $rule = new ValidDni();
        
        // Muy corto
        $failed = false;
        $rule->validate('dni', '1234567', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        // Muy largo
        $failed = false;
        $rule->validate('dni', '123456789', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();
    });

    it('rechaza DNIs con caracteres no numéricos', function () {
        $rule = new ValidDni();
        $failed = false;

        $rule->validate('dni', '1234567A', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        $failed = false;
        $rule->validate('dni', 'ABCDEFGH', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();

        $failed = false;
        $rule->validate('dni', '12-345-678', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeTrue();
    });

    it('rechaza DNIs con todos los dígitos iguales', function () {
        $rule = new ValidDni();
        
        $invalidDnis = [
            '00000000',
            '11111111',
            '22222222',
            '99999999',
        ];

        foreach ($invalidDnis as $dni) {
            $failed = false;
            $rule->validate('dni', $dni, function () use (&$failed) {
                $failed = true;
            });
            expect($failed)->toBeTrue();
        }
    });

    it('acepta DNIs con dígitos variados', function () {
        $rule = new ValidDni();
        $failed = false;

        // DNI con variación de dígitos
        $rule->validate('dni', '12345678', function () use (&$failed) {
            $failed = true;
        });
        expect($failed)->toBeFalse();
    });
});
