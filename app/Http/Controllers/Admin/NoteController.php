<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function index($id)
    {
        $data = Note::where('sheep_id', $id)->orderBy('id', 'desc')->get();
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
            'body' => 'required|string|max:255',
            'sheep_id' => 'required|exists:sheep,id',
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
        $note = Note::create($data);
        return response()->json([
            'status' => 201,
            'message' => 'Note Created Successfully',
            'success' => true,
            'data' => $note,
        ], 201);
    }
    public function show($id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'status' => 404,
                'message' => 'Note Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Note Data Retrieved Successfully',
            'success' => true,
            'data' => $note,
        ]);
    }
    public function update(Request $request, $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'status' => 404,
                'message' => 'Note Not Found',
                'success' => false,
            ], 404);
        }
        $validator = Validator::make(request()->all(), [
            'body' => 'required|string|max:255',
            'sheep_id' => 'required|exists:sheep,id',
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
        $note->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'Note Updated Successfully',
            'success' => true,
            'data' => $note,
        ]);
    }
    public function destroy($id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'status' => 404,
                'message' => 'Note Not Found',
                'success' => false,
            ], 404);
        }
        $note->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Note Deleted Successfully',
            'success' => true,
        ]);
    }
}
