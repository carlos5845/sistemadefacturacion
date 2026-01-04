<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsultController extends Controller
{
    public function dni($dni)
    {
        if (strlen($dni) !== 8) {
            return response()->json(['error' => 'El DNI debe tener 8 dígitos'], 422);
        }

        try {
            // Using apis.net.pe / Decolecta structure
            // Token is loaded from .env: APIS_NET_PE_TOKEN
            $token = config('services.apis_net_pe.token', env('APIS_NET_PE_TOKEN'));
            $baseUrl = config('services.apis_net_pe.base_url', 'https://api.decolecta.com');
            
            $response = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$baseUrl}/v1/reniec/dni", [
                    'numero' => $dni
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'name' => ($data['nombres'] ?? $data['first_name'] ?? '') . ' ' . ($data['apellidoPaterno'] ?? $data['first_last_name'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? $data['second_last_name'] ?? ''),
                    'address' => '', // DNI lookup usually doesn't return address
                    'full_data' => $data
                ]);
            }

            return response()->json(['error' => 'No se encontró información para este DNI'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar el servicio: ' . $e->getMessage()], 500);
        }
    }

    public function ruc($ruc)
    {
        if (strlen($ruc) !== 11) {
            return response()->json(['error' => 'El RUC debe tener 11 dígitos'], 422);
        }

        try {
            $baseUrl = config('services.apis_net_pe.base_url', 'https://api.decolecta.com');
            $token = config('services.apis_net_pe.token', env('APIS_NET_PE_TOKEN'));
            
            $response = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$baseUrl}/v1/sunat/ruc", [
                    'numero' => $ruc
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $addressParts = [
                    $data['direccion'] ?? $data['domicilio_fiscal'] ?? '',
                    $data['distrito'] ?? '',
                    $data['provincia'] ?? '',
                    $data['departamento'] ?? ''
                ];
                
                $address = implode(' - ', array_filter($addressParts, fn($value) => !empty($value)));

                return response()->json([
                    'name' => $data['razon_social'] ?? $data['razonSocial'] ?? '',
                    'address' => $address,
                    'full_data' => $data
                ]);
            }

            return response()->json(['error' => 'No se encontró información para este RUC'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar el servicio: ' . $e->getMessage()], 500);
        }
    }
}
