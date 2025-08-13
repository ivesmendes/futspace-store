<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShirtCounter;

class ShirtCounterController extends Controller
{
    public function getCount()
    {
        $counter = ShirtCounter::firstOrCreate([], ['count' => 0]);
        return response()->json(['count' => $counter->count]);
    }

    public function updateCount(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:0'
        ]);

        $counter = ShirtCounter::firstOrCreate([], ['count' => 0]);
        $counter->update(['count' => $request->count]);
        return response()->json(['count' => $counter->count]);
    }
}
