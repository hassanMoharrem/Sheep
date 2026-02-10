<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Setting;
use App\Models\Sheep;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NunoMaduro\Collision\Adapters\Phpunit\State;

// Api controller for managing Sheep in the admin panel
class SheepController extends Controller
{
    /**
     * معالجة الإجراء القادم وتحديث السجل حسب دورة حياة الأنثى
     * @param Request $request
     * @param int $sheepId
     * @return \Illuminate\Http\JsonResponse
     */
    // public function processTask(Request $request, $sheepId)
    // {
    //     $sheep = Sheep::find($sheepId);
    //     if (!$sheep) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Sheep Not Found',
    //             'success' => false,
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'action_type' => 'required|in:fatem,mating,pregnancy_check,birth',
    //         'result' => 'nullable|string',
    //         'male_count' => 'nullable|integer',
    //         'female_count' => 'nullable|integer',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 400,
    //             'success' => false,
    //             'message' => $validator->errors(),
    //         ], 400);
    //     }

    //     $action = $request->action_type;
    //     $result = $request->result;
    //     $now = now();
    //     $nextTask = null;
    //     $statusName = null;
    //     $nextActionType = null;
    //     $nextDate = null;

    //     // دورة الحياة حسب الإجراء
    //     switch ($action) {
    //         case 'fatem': // فطام
    //             if ($result === 'تم') {
    //                 $statusName = 'مفطومه';
    //                 $nextActionType = 'mating';
    //                 $nextDate = $now->copy()->addMonths(6);
    //             }
    //             break;
    //         case 'mating': // تلقيح
    //             if ($result === 'تم') {
    //                 $statusName = 'ملقحه';
    //                 $nextActionType = 'pregnancy_check';
    //                 $nextDate = $now->copy()->addMonths(3);
    //             }
    //             break;
    //         case 'pregnancy_check': // فحص حمل
    //             if ($result === 'حامل') {
    //                 $statusName = 'حامل';
    //                 $nextActionType = 'birth';
    //                 $nextDate = $now->copy()->addMonths(2);
    //             } elseif ($result === 'حايل') {
    //                 $statusName = 'ملقحه';
    //                 $nextActionType = 'pregnancy_check';
    //                 $nextDate = $now->copy()->addMonth(1);
    //             }
    //             break;
    //         case 'birth': // ولادة
    //             if ($result === 'تم') {
    //                 $statusName = 'والد';
    //                 $nextActionType = 'mating';
    //                 $nextDate = $now->copy()->addDays(35);
    //             }
    //             break;
    //     }

    //     // تحديث حالة الشاة
    //     if ($statusName) {
    //         $status = \App\Models\Status::where('name', $statusName)->first();
    //         if ($status) {
    //             $sheep->status_id = $status->id;
    //             $sheep->save();
    //         }
    //     }

    //     // إضافة الإجراء القادم في جدول المهام
    //     if ($nextActionType && $nextDate) {
    //         \App\Models\Task::create([
    //             'sheep_id' => $sheep->id,
    //             'action_type' => $nextActionType,
    //             'scheduled_date' => $nextDate,
    //             'status' => 'pending',
    //         ]);
    //     }

    //     // تحديث المهمة الحالية (إن وجدت)
    //     if ($request->has('task_id')) {
    //         $task = \App\Models\Task::find($request->task_id);
    //         if ($task) {
    //             $task->status = 'completed';
    //             $task->result = $result;
    //             $task->completed_at = $now;
    //             $task->save();
    //         }
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Sheep status and next action updated successfully',
    //         'success' => true,
    //         'current_status' => $statusName,
    //         'next_action' => $nextActionType,
    //         'next_date' => $nextDate,
    //     ]);
    // }
    public function index(Request $request)
    {
        $query = Sheep::with('breed', 'currentStatus','nextStatus', 'mother')->where('visible', 1);
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('breed_id')) {
            $query->where('breed_id', $request->breed_id);
        }
        if ($request->filled('status_id')) {
            $query->where('current_status_id', $request->status_id);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        if ($request->filled('birth_date')) {
            $query->whereDate('birth_date', '>=', $request->birth_date);
        }
        //where sort descending by id or ascending
        if ($request->filled('sort') && in_array($request->sort, ['asc', 'desc'])) {
            $query->orderBy('id', $request->sort);
        }
        // paginate the results
        $data = $query->paginate(10);
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }
    // Show sheep order by offspring alot and count
    public function popularMothers()
    {
        // id ,code , birth_date, count offspring
        $data = Sheep::where('visible', 1)->select('id', 'code', 'birth_date')
        ->withCount('offspring')
        ->whereHas('offspring')
        ->orderBy('offspring_count', 'desc')
        ->get();
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }
    public function getDataFast()
    {
        // Get all sheep with only id and code
        $data = Sheep::select('id', 'code')->where('visible', 1)->where('gender', 'female')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'unique:sheep|required|string|max:255',
            'breed_id' => 'required|exists:breeds,id',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'health_status_id' => 'required|in:1,2,3', // enum ['1', '3', '2']
            'weight' => 'required|numeric',
            'current_status_id' => 'required|exists:statuses,id',
            'mother_id' => 'nullable|exists:sheep,id',
            'is_active' => 'required|boolean',
            'note' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $data = $request->except(['next_status_id']);
        if ($request->filled('current_status_id')) {
            // تعيين الحالة التالية بناءً على الحالة الحالية
            $currentStatus = \App\Models\Status::find($request->current_status_id);

            $nextName = match ($currentStatus?->name) {
                'رضيعه'          => 'فطام',
                'فطام', 'والده' => 'تلقيح', // Multiple cases pointing to one result
                'تلقيح'          => 'فحص حمل',
                'فحص حمل'       => 'حامل',
                'حايل'          => 'فحص حمل',
                'حامل'           => 'والده',
                'ولاده'         => 'تلقيح',
                'علاج فوري'     => 'علاج',
                'علاج'           => 'مراقبه',
                'مراقبه'        => 'سليم',
                default          => null,
            };

            $nextStatus = $nextName ? Status::where('name', $nextName)->first() : null;
            if ($nextStatus) {
                $data['next_status_id'] = $nextStatus->id;
            }
        }
        $sheep = Sheep::create($data);
        if ($sheep->gender === 'female') {
            $status = \App\Models\Status::find($sheep->current_status_id);
            if ($status) {
                $nextAction = null;
                $nextDate = null;
                $now = now();
                switch ($status->name) {
                    case 'رضيعه':
                        $nextAction = Status::where('name', 'فطام')->first()->id; // فطام
                        $nextDate = $now->addMonths(2);
                        break;
                    case 'فطام':
                        $nextAction = Status::where('name', 'تلقيح')->first()->id; // تلقيح 
                        $nextDate = $now->addMonths(6);
                        break;
                    case 'تلقيح':
                        $nextAction = Status::where('name', 'فحص حمل')->first()->id; // فحص حمل
                        $nextDate = $now->addMonths(3);
                        break;
                    case 'حامل':
                        $nextAction = Status::where('name', 'حامل')->first()->id; // حامل
                        $nextDate = $now->addMonths(2);  
                        break;
                    case 'حايل':
                        $nextAction = Status::where('name', 'فحص حمل')->first()->id; // فحص حمل
                        $nextDate = $now->addDays(10);  //10 day
                        break;
                    case 'حامل':
                        $nextAction = Status::where('name', 'ولاده')->first()->id; // ولادة
                        $nextDate = $now->addMonths(2);
                        break;
                    case 'ولاده':
                        $nextAction = Status::where('name', 'تلقيح')->first()->id; // تلقيح 
                        $nextDate = $now->addDays(35);
                        break;
                    case 'علاج فوري':
                        $nextAction = Status::where('name', 'علاج')->first()->id; // علاج
                        $nextDate = null; // حسب الحالة
                        break;
                    case 'علاج':
                        $nextAction = Status::where('name', 'مراقبه')->first()->id; // مراقبه
                        $nextDate = $now->addDays(3);
                        break;
                    case 'مراقبه':
                        $nextAction = Status::where('name', 'سليم')->first()->id; // سليم
                        $nextDate = $now->addDays(7);
                        break;
                }
                if ($nextAction) {
                    \App\Models\Task::create([
                        'sheep_id' => $sheep->id,
                        'action_type_id' => $nextAction,
                        'scheduled_date' => $nextDate,
                        'status' => 'pending',
                    ]);
                }
            }
        }else {
            $status = \App\Models\Status::find($sheep->current_status_id);
            if ($status) {
                $nextAction = null;
                $nextDate = null;
                $now = now();
                switch ($status->name) {
                    // رضيع ثم فطام ثم سليم
                    case 'رضيعه':
                        $nextAction = Status::where('name', 'فطام')->first()->id; // فطام
                        $nextDate = $now->addMonths(2);
                        break;
                    case 'فطام':
                        $nextAction = Status::where('name', 'سليم')->first()->id; // سليم
                        $nextDate = $now->addMonths(4);
                        break;
                    case 'علاج فوري':
                        $nextAction = Status::where('name', 'علاج')->first()->id; // علاج
                        $nextDate = null; // حسب الحالة
                        break;
                    case 'علاج':
                        $nextAction = Status::where('name', 'مراقبه')->first()->id; // مراقبه
                        $nextDate = $now->addDays(3);
                        break;
                    case 'مراقبه':
                        $nextAction = Status::where('name', 'سليم')->first()->id; // سليم
                        $nextDate = $now->addDays(7);
                        break;

                }
                if ($nextAction) {
                    \App\Models\Task::create([
                        'sheep_id' => $sheep->id,
                        'action_type_id' => $nextAction,
                        'scheduled_date' => $nextDate,
                        'status' => 'pending',
                    ]);
                }
            }
        }
        if ($request->filled('note')) {
            Note::create([
                'sheep_id' => $sheep->id,
                'body' => $request->note,
            ]);
        }
        $sheep->load('breed', 'currentStatus','nextStatus', 'mother');

        return response()->json([
            'status' => 201,
            'message' => 'Sheep Created Successfully',
            'success' => true,
            'data' => $sheep,
        ], 201);
    }
    public function show($id)
    {
        $sheep = Sheep::with('offspring', 'tasks')->find($id);
        if (!$sheep) {
            return response()->json([
                'status' => 404,
                'message' => 'Sheep Not Found',
                'success' => false,
            ], 404);
        }
        $sheep->load('breed', 'currentStatus','nextStatus', 'mother');
        return response()->json([
            'status' => 200,
            'message' => 'Sheep Retrieved Successfully',
            'success' => true,
            'data' => $sheep,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $sheep = Sheep::find($id);
        if (!$sheep) {
            return response()->json([
                'status' => 404,
                'message' => 'Sheep Not Found',
                'success' => false,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'unique:sheep,code,' . $sheep->id . '|string|max:255',
            'breed_id' => 'exists:breeds,id',
            'birth_date' => 'date',
            'weight' => 'nullable|numeric|min:0',
            'gender' => 'in:male,female',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $data = $request->all();
        $sheep->update($data);
        $sheep->load('breed', 'currentStatus','nextStatus', 'mother');
        return response()->json([
            'status' => 200,
            'message' => 'Sheep Updated Successfully',
            'success' => true,
            'data' => $sheep,
        ], 200);
    }
    public function toggleVisibility(Request $request, $id)
    {
        $sheep = Sheep::find($id);
        if (!$sheep) {
            return response()->json([
                'status' => 404,
                'message' => 'Sheep Not Found',
                'success' => false,
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }
        if ($request->filled('note')) {
            Note::create([
                'sheep_id' => $sheep->id,
                'body' => $request->note,
            ]);
        }
        $sheep->visible = !$sheep->visible;
        $sheep->save();
        return response()->json([
            'status' => 200,
            'message' => 'Sheep visibility toggled successfully',
            'success' => true,
            'data' => [
                'id' => $sheep->id,
                'visible' => $sheep->visible,
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $sheep = Sheep::find($id);
        if (!$sheep) {
            return response()->json([
                'status' => 404,
                'message' => 'Sheep Not Found',
                'success' => false,
            ], 404);
        }
        // حذف جميع المهام المرتبطة بالشاة
        \App\Models\Task::where('sheep_id', $sheep->id)->delete();
        $sheep->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Sheep and related tasks deleted successfully',
            'success' => true,
        ], 200);
    }
}
