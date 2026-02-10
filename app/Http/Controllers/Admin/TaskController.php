<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CaseMonitor;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;

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
                'message' => 'المهمة غير موجودة',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'message' => 'تم استرجاع بيانات المهمة بنجاح',
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
                'message' => 'المهمة غير موجودة',
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
                'message' => 'لا يمكن جدولة مهمة بتاريخ في الماضي',
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
            'message' => 'تم تحديث المهمة بنجاح',
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
                'message' => 'المهمة غير موجودة',
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
        // sheep is female
        if ($task['sheep']->gender == 'female') {
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
                        // now + breeding_after_weaning setting
                        $breedingAfterWeaning = \App\Models\Setting::where('key', 'breeding_after_weaning')->first();
                            if ($breedingAfterWeaning) {
                                $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + ' . $breedingAfterWeaning->value . ' days'));
                            } else {
                                $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 30 days'));
                            }
                        $data['action_type_id'] = 4; // فحص حمل
                        // notificaton to firebase & save in notifications table
                        $notification = Notification::create([
                            'title' => 'تذكير بفحص الحمل',
                            'body' => "النعجة رقم {$task['sheep']->id} جاهزة لفحص الحمل بعد " . ($breedingAfterWeaning ? $breedingAfterWeaning->value : 30) . " يوم من الفطام.",
                        ]);
                        $tokens = User::whereNotNull('fcm_token')
                            ->pluck('fcm_token')
                            ->filter()
                            ->values()
                            ->all();

                        app(\App\Services\FirebaseService::class)->sendToTokens($tokens, [
                            'title' => $notification->title,
                            'body' => $notification->body,
                            'data' => [
                                'notification_id' => (string) $notification->id,
                                'created_at' => $notification->created_at->toDateTimeString(),
                            ],
                        ]);
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
                        // now + breeding_after_birth setting
                        $breedingAfterBirth = \App\Models\Setting::where('key', 'breeding_after_birth')->first();
                        if ($breedingAfterBirth) {
                            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + ' . $breedingAfterBirth->value . ' days'));
                        } else {
                            $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 35 days'));
                        }
                        $data['action_type_id'] = 3; // تلقيح
                        break;         
                    case 7: // ولاده
                        $task['sheep']->current_status_id = 3; // تلقيح
                        $task['sheep']->next_status_id = 4; // فحص حمل
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 30 days'));
                        $data['action_type_id'] = 4; // فحص حمل
                        break;  
                    default:
                        return response()->json([
                            'status' => 400,
                            'message' => 'حالة الأغنام الحالية غير صالحة',
                            'success' => false,
                        ], 400);
                }
            }else if ($data['action_type_id'] == 5 && $task['sheep']->current_status_id == 4) {
                // إذا كان الفحص سلبي، ارجع للتلقيح
                $task['sheep']->current_status_id = 5; // حايل
                $task['sheep']->next_status_id = 4; // فحص حمل
                $task['sheep']->save();
                $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 30 days'));
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
            }else if ($data['action_type_id'] == 8){
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
                }else{
                    $task['sheep']->current_status_id = 11; // سليم
                    $task['sheep']->next_status_id = 11; // سليم
                    $task['sheep']->save();
                    $data['action_type_id'] = 11; // سليم
                }
            }else{
                // خطأ يرجى إدخال قيمة صحيحة
                return response()->json([
                    'status' => 400,
                    'message' => 'معرّف نوع الإجراء غير صالح لحالة الأغنام الحالية',
                    'success' => false,
                ], 400);
            }
        }
        elseif ($task['sheep']->gender == 'male') {
            if($data['action_type_id'] == 100) {
                switch ($task['sheep']->current_status_id) {
                    case 1: // رضيع
                        $task['sheep']->current_status_id = 2; // فطام
                        $task['sheep']->next_status_id = 11; // سليم
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . '+' . Setting::where('key', 'breeding_after_weaning')->first()->value . ' days'));
                        $data['action_type_id'] = 11; // سليم
                        break;  

                    case 2: // فطام   
                        $task['sheep']->current_status_id = 11; // سليم
                        $task['sheep']->next_status_id = 11; // سليم
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . '+' . Setting::where('key', 'breeding_after_weaning')->first()->value . ' days'));
                        $data['action_type_id'] = 11; // سليم
                        break;
                    case 8: // علاج فوري
                        $task['sheep']->current_status_id = 9; // علاج
                        $task['sheep']->next_status_id = 10; // مراقبه
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
                        $data['action_type_id'] = 10; // مراقبه
                        break;
                    case 9: // علاج
                        $task['sheep']->current_status_id = 10; // مراقبه
                        $task['sheep']->next_status_id = 11; // سليم
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
                        $data['action_type_id'] = 11; // سليم
                        break;
                    case 10: // مراقبه
                        $task['sheep']->current_status_id = 11; // سليم
                        $task['sheep']->next_status_id = 11; // سليم
                        $task['sheep']->save();
                        $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
                        $data['action_type_id'] = 11; // سليم
                        break;
                    case 11: // سليم
                        $caseMonitor = CaseMonitor::where('sheep_id', $task['sheep']->id)->first();
                        if ($caseMonitor) {
                            $task['sheep']->current_status_id = $caseMonitor->current_status_id;
                            $task['sheep']->next_status_id = $caseMonitor->next_status_id;
                            $task['sheep']->save();
                            $data['action_type_id'] = $caseMonitor->next_status_id;
                            $task['scheduled_date'] = $caseMonitor->date_monitored;
                            $caseMonitor->delete();
                        }else{
                            $task['sheep']->current_status_id = 11; // سليم
                            $task['sheep']->next_status_id = 11; // سليم
                            $task['sheep']->save();
                            $data['action_type_id'] = 11; // سليم
                        }
                        break;
                    default:
                        return response()->json([
                            'status' => 400,
                            'message' => 'حالة الأغنام الحالية غير صالحة بالنسبة للأغنام الذكور',
                            'success' => false,
                        ], 400);
                }   
            }elseif($data['action_type_id'] == 8){
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
            }elseif ($data['action_type_id'] == 9){
                $task['sheep']->current_status_id = 9 ; // علاج
                $task['sheep']->next_status_id = 10; // مراقبه
                $task['sheep']->save();
                $data['action_type_id'] = 10; // مراقبه
                $task['scheduled_date'] = date('Y-m-d', strtotime($now . ' + 7 days'));
            }elseif ($data['action_type_id'] == 11){
                $caseMonitor = CaseMonitor::where('sheep_id', $task['sheep']->id)->first();
                if ($caseMonitor) {
                    $task['sheep']->current_status_id = $caseMonitor->current_status_id;
                    $task['sheep']->next_status_id = $caseMonitor->next_status_id;
                    $task['sheep']->save();
                    $data['action_type_id'] = $caseMonitor->next_status_id;
                    $task['scheduled_date'] = $caseMonitor->date_monitored;
                    $caseMonitor->delete();
                }else{
                    $task['sheep']->current_status_id = 11; // سليم
                    $task['sheep']->next_status_id = 11; // سليم
                    $task['sheep']->save();
                    $data['action_type_id'] = 11; // سليم
                }
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'معرّف نوع الإجراء غير صالح لحالة الأغنام الحالية',
                    'success' => false,
                ], 400);
            }
        }
        $task->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'تم تحديث نوع إجراء المهمة بنجاح',
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
                'message' => 'المهمة غير موجودة',
                'success' => false,
            ], 404);
        }
        $task->delete();
        return response()->json([
            'status' => 200,
            'message' => 'تم حذف المهمة بنجاح',
            'success' => true,
        ]);
    }
}
