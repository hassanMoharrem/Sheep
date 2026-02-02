<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ExpenseFrequency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Api controller for managing expense frequencies in the admin panel
class ExpenseFrequencyController extends Controller
{
        public function index(Request $request)
    {
        $query = ExpenseFrequency::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        $data = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
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
       
        $expenseFrequency = ExpenseFrequency::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'ExpenseFrequency Created Successfully',
            'success' => true,
            'data' => $expenseFrequency,
        ], 201);
    }
    public function show($id)
    {

        $expenseFrequency = ExpenseFrequency::find($id);
        if (!$expenseFrequency) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseFrequency Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'ExpenseFrequency Data Retrieved Successfully',
            'success' => true,
            'data' => $expenseFrequency,
        ]);
    }
    public function update(Request $request, $id)
    {
        $expenseFrequency = ExpenseFrequency::find($id);
        if (!$expenseFrequency) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseFrequency Not Found',
                'success' => false,
            ], 404);
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
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

        $expenseFrequency->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'ExpenseFrequency Updated Successfully',
            'success' => true,
            'data' => $expenseFrequency,
        ]);
    }

    public function destroy($id)
    {
        $expenseFrequency = ExpenseFrequency::findOrFail($id);
        if (!$expenseFrequency) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseFrequency Not Found',
                'success' => false,
            ], 404);
        }
        $expenseFrequency->delete();

        return response()->json([
            'status' => 200,
            'message' => 'ExpenseFrequency Deleted Successfully',
            'success' => true,
        ]);
    }
}