<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Api controller for managing expenses in the admin panel
class ExpenseController extends Controller
{
        public function index(Request $request)
    {
        $query = Expense::query();

        // if ($request->filled('name')) {
        //     $query->where('name', 'like', '%' . $request->name . '%');
        // }
        // إجمالي المصاريف
        $totalExpenses = $query->sum('amount');
        // مصروفات الشهر الحالي
        $currentMonthExpenses = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $data = $query->orderBy('id', 'desc')->with(['type','frequency'])->paginate(10);

        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
             'totalExpenses' => $totalExpenses,
            'currentMonthExpenses' => $currentMonthExpenses,
            'data' => $data,
           
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'expense_type_id' => 'required|integer|exists:expense_types,id',
            'expense_frequency_id' => 'required|integer|exists:expense_frequencies,id',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ];
            return response()->json($response, 400);
        }


        $data = $request->all();
       
        $expense = Expense::create($data);
        $expense->load('frequency','type');

        return response()->json([
            'status' => 201,
            'message' => 'Expense Created Successfully',
            'success' => true,
            'data' => $expense,
        ], 201);
    }
    public function show($id)
    {

        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json([
                'status' => 404,
                'message' => 'Expense Not Found',
                'success' => false,
            ], 404);
        }
        $expense->load('frequency','type');
        return response()->json([
            'status' => 200,
            'message' => 'Expense Data Retrieved Successfully',
            'success' => true,
            'data' => $expense,
        ]);
    }
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json([
                'status' => 404,
                'message' => 'Expense Not Found',
                'success' => false,
            ], 404);
        }

        $validator = Validator::make(request()->all(), [
            'expense_type_id' => 'required|integer|exists:expense_types,id',
            'expense_frequency_id' => 'required|integer|exists:expense_frequencies,id',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ];
            return response()->json($response, 400);
        }
        $data = $request->all();

        $expense->update($data);
        $expense->load('frequency','type');
        return response()->json([
            'status' => 200,
            'message' => 'Expense Updated Successfully',
            'success' => true,
            'data' => $expense,
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        if (!$expense) {
            return response()->json([
                'status' => 404,
                'message' => 'Expense Not Found',
                'success' => false,
            ], 404);
        }
        $expense->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Expense Deleted Successfully',
            'success' => true,
        ]);
    }
}