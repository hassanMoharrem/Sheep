<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleSheep;
use App\Models\Sheep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    // عرض كل عمليات البيع
    public function index()
    {
        $sales = Sale::with('sheep')->latest()->get();
        return response()->json($sales);
    }

    // إنشاء عملية بيع جديدة
    public function store(Request $request)
{
    $validated = $request->validate([
        'sheep' => 'required|array|min:1',
        'sheep.*.id' => 'required|exists:sheep,id',
        'sheep.*.price' => 'required|numeric',
        'sheep.*.real_price' => 'nullable|numeric',
    ]);

    return DB::transaction(function () use ($validated) {
        $sale = Sale::create([
            'total_price' => collect($validated['sheep'])->sum('price'),
            'real_total_price' => collect($validated['sheep'])->sum('real_price'),
            'sold_at' => now(),
        ]);

        foreach ($validated['sheep'] as $sheepData) {
            $sale->sheep()->attach($sheepData['id'], [
                'price' => $sheepData['price'],
                'real_price' => $sheepData['real_price'] ?? null,
            ]);

            // Set visible=0 and is_active=0 for the sold sheep
            $sheep = Sheep::find($sheepData['id']);
            if ($sheep) {
                $sheep->visible = 0;
                $sheep->is_active = 0;
                $sheep->save();
            }
        }

        return response()->json($sale->load('sheep'), 201);
    });
}

    // عرض تفاصيل عملية بيع واحدة
    public function show($id)
    {
        $sale = Sale::with(['sheep'])->findOrFail($id);
        return response()->json($sale);
    }

    // حذف عملية بيع
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
