<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loss;
use Illuminate\Http\Request;

class LossController extends Controller
{
    // Listar (com possibilidade de filtrar por mês/ano igual ao customers)
    public function index(Request $request)
    {
        $q = Loss::orderBy('created_at', 'desc');

        $month = $request->input('month');
        $year  = $request->input('year');
        $m = is_numeric($month) ? (int)$month : null;
        $y = is_numeric($year)  ? (int)$year  : null;

        if ($m && $y) {
            $q->whereMonth('loss_date', $m)->whereYear('loss_date', $y);
        } elseif ($y) {
            $q->whereYear('loss_date', $y);
        }

        // se passar ?all=1 retorna tudo (sem paginação)
        if ($request->has('all')) {
            return response()->json(['data' => $q->get()]);
        }

        return response()->json($q->paginate(10));
    }

    // Criar
    public function store(Request $request)
    {
        $data = $request->validate([
            'reason'    => 'required|string|max:255',
            'amount'    => 'required|numeric|min:0',
            'loss_date' => 'nullable|date',
        ]);

        $data['loss_date'] = $data['loss_date'] ?? now()->toDateString();

        $loss = Loss::create($data);
        return response()->json($loss, 201);
    }

    // Atualizar
    public function update(Request $request, Loss $loss)
    {
        $data = $request->validate([
            'reason'    => 'required|string|max:255',
            'amount'    => 'required|numeric|min:0',
            'loss_date' => 'nullable|date',
        ]);

        $loss->update($data);
        return response()->json($loss);
    }

    // Excluir
    public function destroy(Loss $loss)
    {
        $loss->delete();
        return response()->json(null, 204);
    }

    // Soma total (útil para o dashboard)
    public function total(Request $request)
    {
        $q = Loss::query();

        $m = is_numeric($request->input('month')) ? (int)$request->input('month') : null;
        $y = is_numeric($request->input('year'))  ? (int)$request->input('year')  : null;

        if ($m && $y) {
            $q->whereMonth('loss_date', $m)->whereYear('loss_date', $y);
        } elseif ($y) {
            $q->whereYear('loss_date', $y);
        }

        return response()->json(['total_losses' => (float) $q->sum('amount')]);
    }
}
