<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Api controller for managing breeds in the admin panel
class BreedController extends Controller
{
        public function index(Request $request)
    {
        $query = Breed::query();

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
       
        $breed = Breed::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'Breed Created Successfully',
            'success' => true,
            'data' => $breed,
        ], 201);
    }
    public function show($id)
    {

        $breed = Breed::find($id);
        if (!$breed) {
            return response()->json([
                'status' => 404,
                'message' => 'Breed Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Breed Data Retrieved Successfully',
            'success' => true,
            'data' => $breed,
        ]);
    }
    public function update(Request $request, $id)
    {
        $breed = Breed::find($id);
        if (!$breed) {
            return response()->json([
                'status' => 404,
                'message' => 'Breed Not Found',
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

        $breed->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'Breed Updated Successfully',
            'success' => true,
            'data' => $breed,
        ]);
    }

    public function destroy($id)
    {
        $breed = Breed::findOrFail($id);
        if (!$breed) {
            return response()->json([
                'status' => 404,
                'message' => 'Breed Not Found',
                'success' => false,
            ], 404);
        }
        $breed->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Breed Deleted Successfully',
            'success' => true,
        ]);
    }
}