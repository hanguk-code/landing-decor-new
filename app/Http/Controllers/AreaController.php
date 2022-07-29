<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Http\Resources\JResource;
use App\Models\Areas;
use App\Models\Order\Order;
use App\Models\BrowsingHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AreaController extends Controller
{

    /**
     * @param Request $request
     * @return JResource
     */
    public function index(Request $request)
    {
        $data = Areas::get();
        return (new JResource([
            "status" => "success",
            "data" => $data
        ]));
    }

    /**
     * @param Request $request
     * @return JResource
     */
    public function create(Request $request)
    {
        if (!isset($request->name) || empty($request->name)) {
            return (new JResource([
                "status" => "fail"
            ]));
        }
        Areas::create([
            'name' => $request->name
        ]);
        return (new JResource([
            "status" => "success"
        ]));
    }

    /**
     * @param $id
     * @return JResource
     */
    public function delete($id)
    {
        Areas::where('id', $id)->delete();
        return (new JResource([
            "status" => "success"
        ]));
    }


    /**
     * @param Request $request
     * @return JResource
     */
    public function statistic(Request $request)
    {

        $month = $request->month;
        $areas = Areas::get();
        $top_browsing_categories = BrowsingHistory::where('type', 'category');
        $top_browsing_products = BrowsingHistory::where('type', 'product');

        if(isset($month) && !empty($month)) {
            $top_browsing_categories->where('created_at', 'LIKE', '%'.$month.'%');
            $top_browsing_products->where('created_at', 'LIKE', '%'.$month.'%');
            $products = BrowsingHistory::select('browsing_history.product_id', DB::raw('count(DISTINCT browsing_history.id) as total'), 'oc_product.image', 'oc_product.sku', 'oc_product_to_category.category_id', 'oc_category_description.category_id', 'oc_category_description.name')
                ->join('oc_product', 'browsing_history.product_id', '=', 'oc_product.product_id')
                ->join('oc_product_to_category', 'browsing_history.product_id', '=', 'oc_product_to_category.product_id')
                ->join('oc_category_description', 'oc_product_to_category.category_id', '=', 'oc_category_description.category_id')
                ->where('browsing_history.created_at', 'LIKE', '%'.$month.'%')
                ->orderBy('total', "desc")
                ->limit(100)
                ->groupBy('browsing_history.product_id')
                ->get();
        } else {
            $products = BrowsingHistory::select('browsing_history.product_id', DB::raw('count(DISTINCT browsing_history.id) as total'), 'oc_product.image', 'oc_product.sku', 'oc_product_to_category.category_id', 'oc_category_description.category_id', 'oc_category_description.name')
                ->join('oc_product', 'browsing_history.product_id', '=', 'oc_product.product_id')
                ->join('oc_product_to_category', 'browsing_history.product_id', '=', 'oc_product_to_category.product_id')
                ->join('oc_category_description', 'oc_product_to_category.category_id', '=', 'oc_category_description.category_id')
                ->orderBy('total', "desc")
                ->limit(100)
                ->groupBy('browsing_history.product_id')
                ->get();
        }

        
        $statistic = [
            "total_orders" => isset($month) ? Order::where('created_at', 'LIKE', '%'.$month.'%')->count() : Order::count(),
            "total_reviews" => $top_browsing_categories->count() + $top_browsing_products->count(),
            "areas" => [],
            "categories" => $top_browsing_categories
                ->select('category', DB::raw('count(`id`) as total'))
                ->orderBy('total', "desc")
                ->limit(10)
                ->groupBy('category')
                ->get(),
            "products" => $products,
            "chart_time" => [],
            "chart_date" => []
        ];


        foreach ($areas as $key => $value) {
            $statistic['areas'][$key] = [
                'area' => $value['name'],
                'total' => isset($month) ? Order::where('type', $value['name'])->where('created_at', 'LIKE', '%'.$month.'%')->count() : Order::where('type', $value['name'])->count()
            ];
        }

        $hours = ["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"];
        foreach ($hours as $hour) {
            $statistic['chart_time'][$hour . ":00"] = isset($month) ? BrowsingHistory::where('watch_time', $hour)->where('created_at', 'LIKE', '%'.$month.'%')->count() : BrowsingHistory::where('watch_time', $hour)->whereBetween('created_at',  [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        }

        $priceList = [
            0 => [
                "min" => 0,
                "max" => 10000
            ],
            1 => [
                "min" => 10000,
                "max" => 25000
            ],
            2 => [
                "min" => 25000,
                "max" => 75000
            ],
            3 => [
                "min" => 75000
            ]
        ];
        foreach ($priceList as $key => $value) {
            if($key == 3) {
                $statistic['chart_products'][$key] = isset($month) ? BrowsingHistory::where('type', 'product')->where('price', '>=', $value['min'])->where('created_at', 'LIKE', '%'.$month.'%')->count() : BrowsingHistory::where('type', 'product')->where('price', '>=', $value['min'])->count();
            } else {
                $statistic['chart_products'][$key] = isset($month) ? BrowsingHistory::where('type', 'product')->whereBetween('price', [$value['min'], $value['max']])->where('created_at', 'LIKE', '%'.$month.'%')->count() : BrowsingHistory::where('type', 'product')->whereBetween('price', [$value['min'], $value['max']])->whereBetween('created_at',  [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
            }
        }

        $days = ["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"];
        foreach ($days as $day) {
            $statistic['chart_date'][$day] = isset($month) ? BrowsingHistory::where('date_viewed', $day)->where('created_at', 'LIKE', '%'.$month.'%')->count() : BrowsingHistory::where('date_viewed', $day)->whereBetween('created_at',  [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        }

        return (new JResource([
            "status" => "success",
            "data" => $statistic
        ]));
    }


    public function setStatistic(Request $request)
    {
        $data = (object)$request->all();
        $stat = BrowsingHistory::create([
            "type" => $data->type,
            "category" => isset($data->category) ? $data->category : null,
            "product_id" => isset($data->product_id) ? $data->product_id : null,
            "price" => isset($data->price) ? str_replace(" ", "", $data->price) : null,
            "date_viewed" => $data->date_viewed,
            "watch_time" => $data->watch_time
        ]);

        return (new JResource([
            "status" => "success"
        ]));
    }

}
