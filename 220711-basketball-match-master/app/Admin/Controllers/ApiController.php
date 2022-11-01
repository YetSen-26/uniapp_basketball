<?php


namespace App\Admin\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Contract;
use App\Models\ContractLease;
use App\Models\ContractLeaseBreakage;
use App\Models\ContractLeaseDeliver;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\DepositPayment;
use App\Models\MatchCategory;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MoneyBillPay;
use App\Models\Replenish;
use App\Models\Sale;
use App\Models\Supply;
use App\Models\System\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ApiController extends Controller
{
    public function city(Request $request)
    {
        $areas = Area::query()->where(['level' => 1, 'parentid' => $request->get('q')])->get();
        $data = [];
        foreach ($areas as $area) {
            $data[] = [
                'id' => $area->id,
                'text' => $area->areaname,
            ];
        }

        return response($data);
    }

    public function matchCategory(Request $request)
    {
        $m = MatchCategory::query()->where(['status' => 1, 'competition_id' => $request->get('q')])->orderByDesc('sort')->get();
        $data = [];
        foreach ($m as $item) {
            $data[] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return response($data);
    }

    public function county(Request $request)
    {
        $areas = Area::query()->where(['level' => 2, 'parentid' => $request->get('q')])->get();
        $data = [];
        foreach ($areas as $area) {
            $data[] = [
                'id' => $area->id,
                'text' => $area->areaname,
            ];
        }

        return response($data);
    }

//    public function quickinfo(Request $request)
//    {
//        $m = CompanyQuickInfo::query()
//            ->where(['id' => $request->get('id')])
//            ->first();
//        return response($m);
//    }

    public function cacheMatchCreateInfo(Request $request)
    {
        $unionId = $request->get('union_id');
        $competitionId = $request->get('competition_id');

        if (!empty($competitionId)) {
            if (Cache::has($unionId . '_competition_id')){
                Cache::put($unionId . '_competition_id',$competitionId,600);
            } else {
                Cache::add($unionId . '_competition_id',$competitionId,600);
            }
        }
        $teamAIds = $request->get('team_a_ids');
        if (!empty($teamAIds)) {
            if (Cache::has($unionId . '_team_a_ids')){
                Cache::put($unionId . '_team_a_ids',$teamAIds,600);
            } else {
                Cache::add($unionId . '_team_a_ids',$teamAIds,600);
            }
        }

        $teamBIds = $request->get('team_b_ids');
        if (!empty($teamBIds)) {
            if (Cache::has($unionId . '_team_b_ids')){
                Cache::put($unionId . '_team_b_ids',$teamBIds,600);
            } else {
                Cache::add($unionId . '_team_b_ids',$teamBIds,600);
            }
        }
    }
}
