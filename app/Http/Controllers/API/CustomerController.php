<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer; // Certifique-se de que o modelo Customer está importado

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Começa a construir a consulta
        $query = Customer::orderBy('order_date', 'desc');

        // Se o parâmetro 'all' estiver presente, retorne todos os clientes sem paginação
        if ($request->has('all')) {
            return response()->json($query->get()); // Retorna todos os clientes
        }

        // FILTROS DE PESQUISA (texto) - (mantido do ajuste anterior)
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('order_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        // FILTROS DE STATUS (checkboxes) - (mantido do ajuste anterior)
        if ($request->has('paid') && $request->input('paid') !== null) {
            $query->where('paid', (bool) $request->input('paid'));
        }

        if ($request->has('delivered') && $request->input('delivered') !== null) {
            $query->where('delivered', (bool) $request->input('delivered'));
        }

        // Pagina os resultados (apenas se 'all' não estiver presente)
        $perPage = 4;
        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'order_description' => 'nullable|string', // Permite que a descrição seja nula
            'order_value' => 'required|numeric',
            'wholesale_value' => 'required|numeric',
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            'paid' => 'boolean',
            'delivered' => 'boolean',
        ]);

        // Define valores padrão para 'paid' e 'delivered' se não forem enviados (checkboxes desmarcados)
        // Isso é crucial para que o DB entenda false se a checkbox não for marcada
        $validatedData['paid'] = $request->input('paid', false);
        $validatedData['delivered'] = $request->input('delivered', false);

        // Cria o novo cliente no banco de dados
        $customer = Customer::create($validatedData);

        // Retorna o cliente criado com status HTTP 201 (Created)
        return response()->json($customer, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // Validação para a atualização
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Ajustado para 255
            'order_description' => 'nullable|string',
            'order_value' => 'required|numeric',
            'wholesale_value' => 'required|numeric',
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            'paid' => 'boolean',
            'delivered' => 'boolean',
        ]);

        // Atualiza o cliente no banco de dados
        $customer->update($validatedData);

        // Retorna o cliente atualizado com status HTTP 200 (OK)
        return response()->json($customer, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Deleta o cliente
        $customer->delete();

        // Retorna uma resposta vazia com status HTTP 204 (No Content)
        return response()->json(null, 204);
    }
}