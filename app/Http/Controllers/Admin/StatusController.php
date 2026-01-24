<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Api controller for managing Statuss in the admin panel
class StatusController extends Controller
{
        public function index(Request $request)
    {
        $query = Status::query();

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
       
        $status = Status::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'Status Created Successfully',
            'success' => true,
            'data' => $status,
        ], 201);
    }
    public function show($id)
    {

        $status = Status::find($id);
        if (!$status) {
            return response()->json([
                'status' => 404,
                'message' => 'Status Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Status Data Retrieved Successfully',
            'success' => true,
            'data' => $status,
        ]);
    }
    public function update(Request $request, $id)
    {
        $status = Status::find($id);
        if (!$status) {
            return response()->json([
                'status' => 404,
                'message' => 'Status Not Found',
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

        $status->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'Status Updated Successfully',
            'success' => true,
            'data' => $status,
        ]);
    }

    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        if (!$status) {
            return response()->json([
                'status' => 404,
                'message' => 'Status Not Found',
                'success' => false,
            ], 404);
        }
        $status->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Status Deleted Successfully',
            'success' => true,
        ]);
    }
}