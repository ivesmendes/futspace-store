<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChampionShipSale;
use Illuminate\Http\Request;

class ChampionShipSaleController extends Controller
{
    /**
     * Retorna todas as vendas da parceria.
     */
    public function index()
    {
        $sales = ChampionShipSale::orderBy('number', 'desc')->get();
        return response()->json($sales);
    }

    /**
     * Armazena uma nova venda no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|integer',
            'description' => 'required|string',
        ]);

        $sale = ChampionShipSale::create([
            'number' => $request->input('number'),
            'description' => $request->input('description'),
        ]);

        return response()->json($sale, 201);
    }
}