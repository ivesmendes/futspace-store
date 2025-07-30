<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockItem; // Importe o modelo StockItem

class StockItemController extends Controller
{
    /**
     * Display a listing of the resource (Listar itens em estoque).
     */
    public function index(Request $request)
    {
        // Ordena os itens por data de criação de forma decrescente por padrão
        $query = StockItem::orderBy('created_at', 'desc');

        // Se o parâmetro 'search' estiver presente, aplica o filtro de pesquisa
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('description', 'like', '%' . $searchTerm . '%');
        }

        // Retorna todos os itens do estoque (não paginados, pois a lista de estoque tende a ser menor)
        // Se precisar de paginação no futuro, pode mudar aqui: $items = $query->paginate(10);
        $items = $query->get();

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage (Adicionar novo item ao estoque).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'wholesale_value' => 'required|numeric|min:0',
            'suggested_sale_value' => 'required|numeric|min:0',
        ]);

        $stockItem = StockItem::create($validatedData);

        return response()->json($stockItem, 201); // Retorna o item criado com status 201 (Created)
    }

    /**
     * Remove the specified resource from storage (Remover item do estoque - após venda).
     */
    public function destroy(StockItem $stockItem)
    {
        $stockItem->delete();

        return response()->json(null, 204); // Retorna resposta vazia com status 204 (No Content)
    }

    /**
     * Calculate and return the total value of all items currently in stock.
     */
    public function getStockValue()
    {
        // Sumariza o 'wholesale_value' de todos os itens em estoque
        $totalStockValue = StockItem::sum('wholesale_value');

        return response()->json([
            'total_stock_value' => $totalStockValue
        ]);
    }

    // Os métodos 'show' e 'update' não são estritamente necessários para o fluxo atual,
    // mas podem ser adicionados se precisar ver/editar um item individualmente no estoque.
    // public function show(StockItem $stockItem) { /* ... */ }
    // public function update(Request $request, StockItem $stockItem) { /* ... */ }
}