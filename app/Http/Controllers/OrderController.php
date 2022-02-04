<?php

namespace App\Http\Controllers;


use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

use App\Repositories\OrderRepository;
use App\Http\Resources\JResource;

use App\Models\Order\Orders;
use App\Models\Order\Order;
use App\Models\Product\OcProduct;
use App\Models\User\Users;

/**
 *
 * Class OrderController
 *
 * @package  App\Http\Controllers
 */
class OrderController extends Controller
{

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * OrderController constructor.
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function index(Request $request)
    {
        $order = $this->orderRepository->all($request);

        return new JResource($order);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function itemOrders(Request $request)
    {
        $itemOrders = $this->orderRepository->getItemOrders($request);

        return new JResource($itemOrders);
    }

    public function ordersList(Request $request)
    {
        $order = $this->orderRepository->orderslist($request);

        return new JResource($order);
    }

    public function setStatus(Request $request, $order_id)
    {
        $response = $this->orderRepository->setStatus($request->zone, $order_id);

        return new JResource($response);
    }

    public function optionsData(Request $request)
    {
        $data = $this->orderRepository->optionsData($request);

        return new JResource($data);
    }

    public function show(int $categoryId)
    {
        return new JResource($this->orderRepository->find($categoryId));
    }

    public function update(Request $request, $categoryId)
    {
        $this->orderRepository->update($request->all(), $categoryId);

        return (new JResource(['status' => 'success', 'id' => $categoryId]));
    }

    public function deleteChecked(Request $request)
    {
        $this->orderRepository->deleteChecked($request);

        return (new JResource(['status' => 'success']));
    }

    public function deleteCheckedList(Request $request)
    {
        $this->orderRepository->deleteCheckedList($request);

        return (new JResource(['status' => 'success']));
    }

    public function sell(Request $params, $id) {
        $request = (object) $params->all()['order'];
        unset($request->order_id);

        $product_ids = ['product_id' => serialize([(int)$id])];
        $data = (object) array_merge((array) $request, (array) $product_ids);
        $product = OcProduct::select('product_id', 'price')->where('product_id', $id)->first();

        OcProduct::where('product_id', $id)->update([
            'manufacturer_id' => 8,
            'zone' => 'black'
        ]);

        Order::create([
            'product_id' => $data->product_id,
            'name' => $data->name,
            'phone' => $data->phoneDetails['formattedNumber'],
            'email' => $data->email,
            'comments' => $data->comments,
            'total_price' => $data->price,
            'type' => $data->type,
            'address' => $data->address,
            'tags' => $data->tags,
            'created_at' => $data->date,
            'zone' => 'red'
        ]);
        Orders::create([
            'product_id' => $id,
            'name' => $data->name,
            'phone' => $data->phoneDetails['formattedNumber'],
            'email' => $data->email,
            'comments' => $data->comments,
            'total_price' => $data->price,
            'type' => $data->type,
            'address' => $data->address,
            'tags' => $data->tags

        ]);

        $user = Users::where('phone', $data->phone);
        if(!$user->exists()) {
            $user = Users::create([
                'name' => $data->name,
                'phone' => $data->phoneDetails['formattedNumber'],
                'email' => $data->email,
                'total_sum' => $data->price,
                'address' => $data->address,
                'number_of_purchases' => 1
            ]);
        } else {
            $user = $user->first();
            $user->total_sum += $data->price;
            $user->number_of_purchases += 1;
            $user->update();
        }

        return (new JResource(['status' => 'success']));
    }


    public function edit(Request $request, $id)
    {
        $response = $this->orderRepository->edit((object) $request->all(), $id);

        return new JResource($response);
    }

    public function setZone(Request $request, $id) {
        $query = Order::where('id', $id);
        $order = $query->first();
        $products = unserialize($order->product_id);

        if($request->zone === "white") {
            foreach($products as $key => $id) {
                OcProduct::where('product_id', $id)->update(['zone' => 'yellow']);
            }
        }

        if($request->zone === "green") {
            foreach($products as $key => $id) {
                OcProduct::where('product_id', $id)->update(['zone' => 'black']);
            }
        }

        if($request->zone === "black") {
            foreach($products as $key => $id) {
                OcProduct::where('product_id', $id)->update(['zone' => 'white']);
            }
            Users::where('phone', $order->phone)->update(['status' => 'blacklist']);
        }

        $query->update(['zone' => $request->zone]);

        return new JResource(['status' => 'success']);
    }


    public function count(Request $request) {

        return Order::where('zone', 'red')->count();
    }
}
