<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Api controller for managing expense types in the admin panel
class ExpenseTypeController extends Controller
{
        public function index(Request $request)
    {
        $query = ExpenseType::query();
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
       
        $expenseType = ExpenseType::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'ExpenseType Created Successfully',
            'success' => true,
            'data' => $expenseType,
        ], 201);
    }
    public function show($id)
    {

        $expenseType = ExpenseType::find($id);
        if (!$expenseType) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseType Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'ExpenseType Data Retrieved Successfully',
            'success' => true,
            'data' => $expenseType,
        ]);
    }
    public function update(Request $request, $id)
    {
        $expenseType = ExpenseType::find($id);
        if (!$expenseType) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseType Not Found',
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

        $expenseType->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'ExpenseType Updated Successfully',
            'success' => true,
            'data' => $expenseType,
        ]);
    }

    public function destroy($id)
    {
        $expenseType = ExpenseType::findOrFail($id);
        if (!$expenseType) {
            return response()->json([
                'status' => 404,
                'message' => 'ExpenseType Not Found',
                'success' => false,
            ], 404);
        }
        $expenseType->delete();

        return response()->json([
            'status' => 200,
            'message' => 'ExpenseType Deleted Successfully',
            'success' => true,
        ]);
    }
}