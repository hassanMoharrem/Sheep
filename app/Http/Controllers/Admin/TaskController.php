<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{

    public function index(Request $request,$id)
    {
        $query = Task::where('sheep_id',$id)->with('actionType');
        
        if (!$query->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Tasks Found',
                'success' => false,
            ], 404);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }
    // public function store(Request $request)
    // {
    //     $validator = Validator::make(request()->all(), [
    //         'action_type_id' => 'required|exists:tasks,action_type_id',

    //     ]);
    //     if ($validator->fails()) {
    //         $response = [
    //             'status' => 400,
    //             'success' => false,
    //             'message' => $validator->errors(),
    //         ];
    //         return response()->json($response, 400);
    //     }
    //     $data = $request->all();
    //     $task = Task::create($data);
    //     return response()->json([
    //         'status' => 201,
    //         'message' => 'Task Created Successfully',
    //         'success' => true,
    //         'data' => $task,
    //     ], 201);
    // }
    public function show($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'status' => 404,
                'message' => 'Task Not Found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Task Data Retrieved Successfully',
            'success' => true,
            'data' => $task,
        ]);
    }
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'status' => 404,
                'message' => 'Task Not Found',
                'success' => false,
            ], 404);
        }
        $validator = Validator::make(request()->all(), [
            'action_type_id' => 'required|exists:tasks,action_type_id',
            'scheduled_date' => 'required|date',
            'status' => 'in:pending,completed',
            'result' => 'string|max:255',
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
        $task->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'Task Updated Successfully',
            'success' => true,
            'data' => $task,
        ]);
    }
    public function destroy($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'status' => 404,
                'message' => 'Task Not Found',
                'success' => false,
            ], 404);
        }
        $task->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Task Deleted Successfully',
            'success' => true,
        ]);
    }
}
