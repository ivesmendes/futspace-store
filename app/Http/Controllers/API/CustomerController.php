<?php // <<--- Certifique-se de que esta é a PRIMEIRA linha, sem nada antes

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::orderBy('order_date', 'desc');

        // Se o parâmetro 'all' estiver presente, retorne todos os clientes sem paginação
        if ($request->has('all')) {
            return response()->json($query->get());
        }

        // FILTROS DE PESQUISA (texto)
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('order_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        // NOVO: Filtros baseados em amount_paid (payment_status)
        if ($request->has('payment_status') && $request->input('payment_status') !== null) {
            $status = $request->input('payment_status');
            if ($status === 'fully_paid') {
                // Clientes onde o valor pago é maior ou igual ao valor do pedido
                $query->whereRaw('amount_paid >= order_value');
            } elseif ($status === 'partially_paid') {
                // Clientes onde o valor pago é maior que 0 E menor que o valor do pedido
                $query->whereRaw('amount_paid > 0 AND amount_paid < order_value');
            } elseif ($status === 'not_paid') {
                // Clientes onde o valor pago é 0 ou nulo
                $query->where(function ($q) {
                    $q->where('amount_paid', 0)->orWhereNull('amount_paid');
                });
            }
        }

        // Mantém o filtro de entrega
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'order_description' => 'nullable|string',
            'order_value' => 'required|numeric|min:0', // Validar valor do pedido
            'wholesale_value' => 'required|numeric|min:0', // Validar valor de atacado
            'amount_paid' => 'nullable|numeric|min:0', // <<--- ADICIONADO: Validação para amount_paid
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            // 'paid' => 'boolean', // <<--- REMOVIDO: Campo 'paid' não existe mais
            'delivered' => 'boolean',
        ]);

        // Define amount_paid para 0.00 se não for fornecido ou for nulo
        $validatedData['amount_paid'] = $request->input('amount_paid', 0.00);
        $validatedData['delivered'] = $request->input('delivered', false); // Mantém delivered

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
            'amount_paid' => 'nullable|numeric|min:0', // <<--- ADICIONADO: Validação para amount_paid
            'order_date' => 'required|date',
            'city' => 'nullable|string|max:255',
            // 'paid' => 'boolean', // <<--- REMOVIDO: Campo 'paid' não existe mais
            'delivered' => 'boolean',
        ]);

        // Define amount_paid para 0.00 se não for fornecido ou for nulo
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