<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Http\Resources\JResource;
use Illuminate\Support\Facades\DB;
use App\Models\FinanceArticles;
use App\Models\FinanceExpenses;
use App\Models\Order\Order;
use App\Models\Product\OcProduct;
use App\Models\User\Users;

class FinanceController extends Controller
{

    /**
     * @param Request $request
     */
    public function addArticle(Request $request) {
        FinanceArticles::create(['name' => $request->article]);
        return (new JResource(["status" => "success"]));
    }

    /**
     * @param Request $request
     */
    public function addExpense(Request $request) {
        $data = $request->all()['expenses'];
        FinanceExpenses::create(['amount' => $data['amount'], 'article' => $data['article']]);
        return (new JResource(["status" => "success"]));
    }


    /**
     * @param Request $request
     */
    public function getArticles(Request $request) {
        $data = FinanceArticles::get();
        return (new JResource([
            "status" => "success",
            "data" => $data
        ]));
    }

    /**
     * @param Request $request
     */
    public function getExpenses(Request $request) {
        $data = FinanceExpenses::get();
        $total_sum = 0;
        foreach($data as $key => $value) {
            $total_sum += $value['amount'];
        }
        return (new JResource([
            "status" => "success",
            "data" => $data,
            "total_sum" => $total_sum
        ]));
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function deleteArticle(Request $request, $id) {
        FinanceArticles::where('id', $id)->delete();
        return (new JResource(["status" => "success"]));
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function deleteExpense(Request $request, $id) {
        FinanceExpenses::where('id', $id)->delete();
        return (new JResource(["status" => "success"]));
    }


    /**
     * @param Request $request
     * @return JResource
     */
    public function getOrders(Request $request) {

        $data = [];

        $data = Order::where('created_at', 'LIKE', "%".date('Y-m')."%")->get();
        $expense = FinanceExpenses::sum('amount');
        $total_sum = 0;
        $income = 0;

        foreach ($data as $key => $value) {
            $price_rub = 0;
            $pids = unserialize($value['product_id']);
            $data[$key]['product_id'] = OcProduct::select('oc_product.product_id', 'oc_product.sku', 'oc_product_description.name', 'oc_product.price_rub')
                                            ->join('oc_product_description', 'oc_product.product_id', '=', 'oc_product_description.product_id')
                                            ->whereIn('oc_product.product_id', $pids)->get();
            $data[$key]['buyer'] = (object) Users::select('id', 'phone')->where('phone', $value['phone'])->first();

            foreach($data[$key]['product_id'] as $product) {
                $price_rub += (int) $product['price_rub'] ? $product['price_rub'] : 0;
            }

            $data[$key]['price_rub'] = $price_rub;

            $total_sum += $value['total_price'];
            $income += $value['total_price'] - $price_rub;
        }




        return (new JResource([
            "status" => "success",
            "data" => $data,
            "total_sum" => $total_sum,
            "income" => $income
        ]));
    }

}