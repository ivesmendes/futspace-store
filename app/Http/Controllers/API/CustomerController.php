<?php // Certifique-se de que esta é a PRIMEIRA linha, sem nada antes

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Carbon\Carbon; // Importe a classe Carbon

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Customer::orderBy('order_date', 'desc');

        // --- Lógica de Filtro de Mês/Ano (para Dashboard e listagem geral) ---
        $month = $request->input('month');
        $year = $request->input('year');

        // Convertemos para int para garantir que são números para a query, se existirem.
        // Se a string for vazia (''), is_numeric() retornará false, e eles se tornarão null.
        $filterMonth = is_numeric($month) ? (int)$month : null;
        $filterYear = is_numeric($year) ? (int)$year : null;

        if ($filterMonth && $filterYear) {
            // Filtra por mês e ano se ambos forem fornecidos e válidos
            $query->whereMonth('order_date', $filterMonth)
                  ->whereYear('order_date', $filterYear);
        } elseif ($filterYear) {
            // Filtra apenas por ano se o mês for 'Todos' (null) mas o ano for especificado
            $query->whereYear('order_date', $filterYear);
        }
        // Se ambos filterMonth e filterYear forem null, nenhum filtro de data será aplicado.
        // Isso significa que a query continuará buscando todos os clientes (para a lista de clientes sem filtro de data, ou para o dashboard se "Todos" for selecionado em ambos).


        // --- Lógica para o Dashboard vs. Listagem de Clientes ---
        // Se a requisição vem do Dashboard (indicado por 'is_dashboard' ou pela presença de month/year)
        // e não precisa de paginação para os cálculos, retornamos todos os dados filtrados.
        // A flag `all` que você já tinha pode ser usada para isso.
        // Ou podemos usar a presença dos filtros de mês/ano como um indicador para o dashboard.

        // Se a requisição tem os filtros de mês/ano, ou se tem a flag 'all',
        // significa que provavelmente é para o dashboard ou para uma exportação,
        // então retornamos todos os dados sem paginação.
        if ($request->has('all') || ($filterMonth !== null || $filterYear !== null)) {
            $customers = $query->get(); // Retorna todos os clientes filtrados por data (se houver)

            // Para o Dashboard, precisamos retornar os dados brutos para ele calcular as métricas.
            // O Dashboard.vue espera 'response.data.data' para a lista de clientes.
            return response()->json([
                'data' => $customers
            ]);
        }


        // --- FILTROS DE PESQUISA (texto) para a listagem de Clientes ---
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('order_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        // --- Filtros baseados em amount_paid (payment_status) para a listagem de Clientes ---
        if ($request->has('payment_status') && $request->input('payment_status') !== null) {
            $status = $request->input('payment_status');
            if ($status === 'fully_paid') {
                $query->whereRaw('amount_paid >= order_value');
            } elseif ($status === 'partially_paid') {
                $query->whereRaw('amount_paid > 0 AND amount_paid < order_value');
            } elseif ($status === 'not_paid') {
                $query->where(function ($q) {
                    $q->where('amount_paid', 0)->orWhereNull('amount_paid');
                });
            }
        }

        // --- Filtro de entrega para a listagem de Clientes ---
        if ($request->has('delivered') && $request->input('delivered') !== null) {
            $query->where('delivered', (bool) $request->input('delivered'));
        }

        // Pagina os resultados (apenas se não for uma requisição "all" ou de filtro de data completa)
        $perPage = 4;
        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'order_description' => 'nullable|string',
            'order_value' => 'required|numeric|min:0',
            'wholesale_value' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            'delivered' => 'boolean',
        ]);

        $validatedData['amount_paid'] = $request->input('amount_paid', 0.00);
        $validatedData['delivered'] = $request->input('delivered', false);

        $customer = Customer::create($validatedData);

        return response()->json($customer, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'order_description' => 'nullable|string',
            'order_value' => 'required|numeric|min:0',
            'wholesale_value' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            'delivered' => 'boolean',
        ]);

        $validatedData['amount_paid'] = $request->input('amount_paid', 0.00);

        $customer->update($validatedData);
        return response()->json($customer, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}