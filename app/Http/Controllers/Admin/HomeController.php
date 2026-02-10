<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sheep;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function summary(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $sheepCount = Sheep::where('visible', 1)->count();

        $femaleCount = Sheep::where('visible', 1)->where('gender', 'female')->count();
        $growthRate = $sheepCount > 0 ? round(($femaleCount / $sheepCount) * 100) : 0;

        // المواليد المتوقعة (مثال: عدد المهام "ولادة" المجدولة لهذه السنة)
        // $expectedBirths = \App\Models\Task::where('action_type', 'birth')
        //     ->whereYear('scheduled_date', $year)
        //     ->count();

        // المواليد الفعلية (مثال: عدد المهام "ولادة" المكتملة لهذه السنة)
        // $actualBirths = \App\Models\Task::where('action_type', 'birth')
        //     ->whereYear('completed_at', $year)
        //     ->where('status', 'completed')
        //     ->count();

        // عدد الخسائر (مثال: عدد الأغنام التي تغيرت حالتها إلى "ميتة" هذه السنة)
        $losses = Sheep::where('visible', 0)->count();

        // الإحصائيات المالية
        // $actualRevenue = Sale::whereYear('created_at', $year)->sum('amount');
        // $maleValue = Sheep::where('visible', 1)->where('gender', 'male')->sum('value');
        // $saleBasketValue = Sale::whereYear('created_at', $year)->sum('basket_value');
        // $totalAssets = Sheep::where('visible', 1)->sum('value');

        return response()->json([
            'year' => $year,
            'sheep_count' => $sheepCount,
            'growth_rate' => $growthRate,
            // 'expected_births' => $expectedBirths,
            // 'actual_births' => $actualBirths,
            'losses' => $losses,
            // 'financial' => [
            //     'total_assets' => $totalAssets,
            //     'actual_revenue' => $actualRevenue,
            //     'male_value' => $maleValue,
            //     'sale_basket_value' => $saleBasketValue,
            // ],
        ]);
    }
}
