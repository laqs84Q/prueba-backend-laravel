<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        // Esta ruta ayuda a consultar qué IDs de divisas están disponibles.
        return CurrencyResource::collection(Currency::orderBy('id')->get());
    }
}
