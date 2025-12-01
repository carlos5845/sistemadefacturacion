<?php

namespace App\Services;

class NumberToWordsService
{
    public function toWords(float $amount, string $currency = 'PEN'): string
    {
        $amount = number_format($amount, 2, '.', '');
        $parts = explode('.', $amount);
        $integerPart = (int) $parts[0];
        $decimalPart = $parts[1];

        $words = $this->integerToWords($integerPart);
        $currencyName = $currency === 'PEN' ? 'SOLES' : 'DÓLARES AMERICANOS';

        return 'SON: ' . mb_strtoupper($words) . ' CON ' . $decimalPart . '/100 ' . $currencyName;
    }

    private function integerToWords(int $number): string
    {
        if ($number == 0) return 'CERO';

        $units = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $tens = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $teens = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        
        $output = '';

        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $remainder = $number % 1000000;
            
            if ($millions == 1) {
                $output .= 'UN MILLON ';
            } else {
                $output .= $this->integerToWords($millions) . ' MILLONES ';
            }
            
            $number = $remainder;
        }

        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            
            if ($thousands == 1) {
                $output .= 'MIL ';
            } else {
                $output .= $this->integerToWords($thousands) . ' MIL ';
            }
            
            $number = $remainder;
        }

        if ($number >= 100) {
            if ($number == 100) {
                $output .= 'CIEN ';
            } elseif ($number > 100 && $number < 200) {
                $output .= 'CIENTO ';
            } else {
                $hundreds = floor($number / 100);
                $hundredsMap = [
                    2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS',
                    6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS'
                ];
                $output .= $hundredsMap[$hundreds] . ' ';
            }
            $number %= 100;
        }

        if ($number >= 20) {
            $ten = floor($number / 10);
            $unit = $number % 10;
            
            if ($unit == 0) {
                $output .= $tens[$ten] . ' ';
            } else {
                if ($number < 30) {
                    $output .= 'VEINTI' . $units[$unit] . ' '; // Veintiuno, Veintidos...
                } else {
                    $output .= $tens[$ten] . ' Y ' . $units[$unit] . ' ';
                }
            }
        } elseif ($number >= 10) {
            $output .= $teens[$number - 10] . ' ';
        } elseif ($number > 0) {
            $output .= $units[$number] . ' ';
        }

        return trim($output);
    }
}
