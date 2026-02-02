<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CaseMonitor;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'scheduled_date' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors(),
                'success' => false,
            ];
            return response()->json($response, 400);
        }
        // check if scheduled_date is in the past
        if (strtotime($request->scheduled_date) < strtotime(date('Y-m-d')) && !in_array($request->scheduled_date, ['One Day', 'Two Day', 'Ten Day'])) {
            return response()->json([
                'status' => 400,
                'message' => 'Scheduled date cannot be in the past',
                'success' => false,
            ], 400);
        }
        $data = $request->all();
        // One Day , Tow Day , 10 Day , or date (2026-04-24)
        if (in_array($data['scheduled_date'], ['One Day', 'Two Day', 'Ten Day'])) {
            $daysMap = [
                'One Day' => 1,
                'Two Day' => 2,
                'Ten Day' => 10,
            ];
            $data['scheduled_date'] = date('Y-m-d', strtotime($task->scheduled_date . ' + ' . $daysMap[$data['scheduled_date']] . ' days'));
        }else {
            $data['scheduled_date'] = date('Y-m-d', strtotime($data['scheduled_date']));
        }
        $task->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'Task Updated Successfully',
            'success' => true,
            'data' => $task,
        ]);
    }
    public function updateActionType(Request $request, $id)
    {
        $task = Task::with('sheep')->find($id);
        if (!$task) {
            return response()->json([
                'status' => 404,
                'message' => 'Task Not Found',
                'success' => false,
            ], 404);
        }
        $request->validate([
            'action_type_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if ($value == 100) {
                    return;
                }else{
                    $exists = DB::table('statuses')->where('id', $value)->exists();
                    if ($exists) {
                        return;
                    }
                }
                // إذا لا "next" ولا رقم موجود
                $fail("الحقل $attribute لازم يكون 100 أو رقم ID موجود في جدول statuses.");
            },
            ],
            'result' => 'nullable|string',
        ]);

        $data = $request->all();
        $now = date('Y-m-d');
        if($data['action_type_id'] == 100) {
            switch ($task['sheep']->current_status_id) {
                case 1: // رضيعه
                    $task['sheep']->current_status_id = 2; // فطام
                    $task['sheep']->next_status_id = 3; // تلقيح
                    $task['sheep']->save();
                    $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 3 months'));
                    $data['action_type_id'] = 3; // ملقحه
                    break;
                case 2: // فطام
                    $task['sheep']->current_status_id = 3; // تلقيح 
                    $task['sheep']->next_status_id = 4; // فحص حمل
                    $task['sheep']->save();
                    $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 6 months'));
                    $data['action_type_id'] = 4; // فحص حمل
                    break; 
                case 3: // تلقيح
                    $task['sheep']->current_status_id = 4; // فحص حمل
                    $task['sheep']->next_status_id = 6; // حامل
                    $task['sheep']->save();
                    $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 3 months'));
                    $data['action_type_id'] = 6; // ولادة
                    break;  
                case 6: // حامل
                    $task['sheep']->current_status_id = 7; // ولادة
                    $task['sheep']->next_status_id = 3; // تلقيح
                    $task['sheep']->save();
                    $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 35 days'));
                    $data['action_type_id'] = 3; // تلقيح
                    break;         
                case 7: // والد
                    $task['sheep']->current_status_id = 3; // تلقيح
                    $task['sheep']->next_status_id = 4; // فحص حمل
                    $task['sheep']->save();
                    $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 6 months'));
                    $data['action_type_id'] = 4; // فحص حمل
                    break;  
                default:
                    return response()->json([
                        'status' => 400,
                        'message' => 'Invalid current sheep status',
                        'success' => false,
                    ], 400);
            }
        }else if ($data['action_type_id'] == 5 && $task['sheep']->current_status_id == 4) {
            // إذا كان الفحص سلبي، ارجع للتلقيح
            $task['sheep']->current_status_id = 5; // حايل
            $task['sheep']->next_status_id = 4; // فحص حمل
            $task['sheep']->save();
            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 10 days'));
            $data['action_type_id'] = 4; // فحص حمل
        }else if($data['action_type_id'] == 6 && $task['sheep']->current_status_id == 5) {
            // إذا كان الفحص إيجابي، انتقل للحمل
            $task['sheep']->current_status_id = 6; // حامل
            $task['sheep']->next_status_id = 7; // ولادة
            $task['sheep']->save();
            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 2 months'));
            $data['action_type_id'] = 7; //ولادة
        }else if ($data['action_type_id'] == 6 && $task['sheep']->current_status_id == 4) {
            $task['sheep']->current_status_id = 6 ; // حامل
            $task['sheep']->next_status_id = 7; // ولادة
            $task['sheep']->save();
            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 2 months'));
            $data['action_type_id'] = 7; // ولادة
        }
        // علاج فوري و علاج و مراقبه و سليم
        else if ($data['action_type_id'] == 8){
            $caseMonitor = CaseMonitor::where('sheep_id', $task['sheep']->id)->first();
            if ($caseMonitor) {
                $caseMonitor->current_status_id = $task['sheep']->current_status_id;
                $caseMonitor->next_status_id = $task['sheep']->next_status_id;
                $caseMonitor->date_monitored = $task['scheduled_date'];
                $caseMonitor->save();
            } else {
                CaseMonitor::create([
                    'sheep_id' => $task['sheep']->id,
                    'current_status_id' => $task['sheep']->current_status_id,
                    'next_status_id' => $task['sheep']->next_status_id,
                    'date_monitored' => $task['scheduled_date'],
                ]);
            }
            $task['sheep']->current_status_id = 8 ; // علاج فوري
            $task['sheep']->next_status_id = 9; // علاج
            $task['sheep']->save();
            $data['action_type_id'] = 9; // علاج
            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
            
        }else if ($data['action_type_id'] == 9){
            $task['sheep']->current_status_id = 9 ; // علاج
            $task['sheep']->next_status_id = 10; // مراقبه
            $task['sheep']->save();
            $data['action_type_id'] = 10; // مراقبه
            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
        }else if ($data['action_type_id'] == 11){
            $caseMonitor = CaseMonitor::where('sheep_id', $task['sheep']->id)->first();
            if ($caseMonitor) {
                $task['sheep']->current_status_id = $caseMonitor->current_status_id;
                $task['sheep']->next_status_id = $caseMonitor->next_status_id;
                $task['sheep']->save();
                $data['action_type_id'] = $caseMonitor->next_status_id;
                $task['scheduled_date'] = $caseMonitor->date_monitored;
                $caseMonitor->delete();
            }
        }
        else{
            // خطأ يرجى إدخال قيمة صحيحة
            return response()->json([
                'status' => 400,
                'message' => 'Invalid action_type_id for the current sheep status',
                'success' => false,
            ], 400);
        }
        $task->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'Task Action Type Updated Successfully',
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
