<?php

namespace App\Repositories;

use App\Mail\OrderNotification;
use App\Mail\OrderNotificationAdmin;
use App\Models\Order\Order;
use App\Models\Order\Orders;
use App\Models\Order\ItemOrders;
use App\Models\Product\OcProduct;
use App\Models\Product\Product;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User\Users;

class OrderRepository
{
    protected $order, $orders, $itemOrders;

    /**
     * OrderRepository constructor.
     * @param Order $order
     */
    public function __construct(
        Order $order,
        Orders $orders,
        ItemOrders $itemOrders
    )
    {
        $this->order = $order;
        $this->orders = $orders;
    }

    public function all($request)
    {
        if ($request->input('client')) {
            return $this->order->all();
        }

        $columns = ['id', 'product_id', 'name', 'phone', 'email', 'comment', 'status', 'total_price', 'zone', 'created_at'];

        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $searchByZone = $request->input('search_by_zone');

        $query = $this->order->select('id', 'product_id', 'name', 'phone', 'email', 'comment', 'status', 'total_price', 'zone', 'created_at')
            ->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('comment', 'like', '%' . $searchValue . '%');
            });
        }

        if(isset($searchByZone) && !empty($searchByZone)) {
            $query->where('zone', $searchByZone);
        }

        $data = $query->orderBy('zone', 'desc')->paginate($length);
        $columns = [
            ['width' => '33%', 'label' => 'Дата заказа',  'name' => 'created_at'],
            ['width' => '33%', 'label' => 'Номер заказ', 'name' => 'id'],
            ['width' => '33%', 'label' => 'Статус', 'name' => 'order_status'],
            ['width' => '33%', 'label' => 'Имя', 'name' => 'name'],
            ['width' => '33%', 'label' => 'Телефон', 'name' => 'phone'],
            ['width' => '33%', 'label' => 'Сумма заказа', 'name' => 'total_price'],
            ['width' => '33%', 'label' => 'Комментарий', 'name' => 'comment'],
        ];

        // $statusClass = array (
        //     array('status' => 'inactive',   'badge' => 'kt-badge--danger'),
        //     array('status' => 'active', 'badge' => 'kt-badge--success')
        // );

        $sortKey = 'id';


        $green = clone $query;
        $white = clone $query;
        $red = clone $query;
        $black = clone $query;

        $white = $white->where('zone', 'white')->count();
        $red = $red->where('zone', 'red')->count();
        $green = $green->where('zone', 'green')->count();
        $black = $black->where('zone', 'black')->count();

        return [
            'data' => $data,
            'columns' => $columns,
            //'statusClass' => $statusClass,
            'sortKey' => $sortKey,
            'draw' => $request->input('draw'),
            'stats' => [
                'total' => Order::count(),
                'white' => $white,
                'red' => $red,
                'green' => $green,
                'black' => $black
            ]
        ];
    }





    public function orderslist($request)
    {
        if ($request->input('client')) {
            return $this->orders->all();
        }

        $columns = ['id', 'order_id', 'product_id', 'name', 'phone', 'email', 'comment', 'status', 'created_at'];

        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');

        $query = $this->orders->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('comment', 'like', '%' . $searchValue . '%');
            });
        }

        $data = $query->paginate($length);
        $columns = [
            ['width' => '33%', 'label' => 'Id', 'name' => 'id'],
            ['width' => '33%', 'label' => 'Номер заказа', 'name' => 'order_id'],
            ['width' => '33%', 'label' => 'Имя', 'name' => 'name'],
            ['width' => '33%', 'label' => 'Телефон', 'name' => 'phone'],
            ['width' => '33%', 'label' => 'Почта', 'name' => 'email'],
            ['width' => '33%', 'label' => 'Продано на', 'name' => 'type'],
            ['width' => '33%', 'label' => 'Товар', 'name' => 'product_id'],
            ['width' => '33%', 'label' => 'Дата заказа',  'name' => 'created_at'],
        ];

        // $statusClass = array (
        //     array('status' => 'inactive',   'badge' => 'kt-badge--danger'),
        //     array('status' => 'active', 'badge' => 'kt-badge--success')
        // );

        $sortKey = 'id';

        return [
            'data' => $data,
            'columns' => $columns,
            //'statusClass' => $statusClass,
            'sortKey' => $sortKey,
            'draw' => $request->input('draw')
        ];
    }




    public function find(int $orderId)
    {
        $order = $this->order->where('id', $orderId)->first();
        $ids = unserialize($order->product_id);
        foreach ($ids as $id) {
            $products[] = OcProduct::with('description')->where('product_id', $id)->first();
        }
        $order->product_id = $products ?? [];

        $order['buyer'] = Users::where('phone', $order->phone)->first();

        return $order;
    }

    public function optionsData($request)
    {
        return [

        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($request, int $orderId)
    {
        if($request['order']['status'] == 2) {
            Mail::to($request['order']['email'])->send(new OrderNotification($request['order']));
            Mail::to(env('MAIL_ADMIN'))->send(new OrderNotificationAdmin($request['order']));
        }


        Order::where('id', $orderId)->update(['status' => $request['order']['status'], 'created_at' => date('Y-m-d H:i:s')]);
    }

    public function deleteChecked($request)
    {
        $checkedItems = $request->get('checkedItems');

        foreach ($checkedItems as $item) {
            $this->order->where('id', $item)->delete();
        }
    }

    public function deleteCheckedList($request)
    {
        $checkedItems = $request->get('checkedItems');

        foreach ($checkedItems as $item) {
            Orders::where('id', $item)->delete();
        }
    }


    public function getItemOrders($request)
    {
        if ($request->input('client')) {
            return $this->itemOrders->all();
        }

        $columns = ['id', 'buyer_name', 'buyer_phone', 'buyer_email', 'articles', 'total_price', 'zone', 'created_at'];

        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');

        $query = ItemOrders::select('id', 'buyer_name', 'buyer_phone', 'buyer_email', 'articles', 'total_price', 'zone', 'created_at')
            ->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('buyer_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('buyer_phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('buyer_email', 'like', '%' . $searchValue . '%');
            });
        }

        $data = $query->paginate($length);
        $columns = [
            ['width' => '33%', 'label' => 'Дата заказа', 'name' => 'created_at'],
            ['width' => '33%', 'label' => 'Номер заказа', 'name' => 'id'],
            ['width' => '33%', 'label' => 'Перечень заказа', 'name' => 'articles'],
            ['width' => '33%', 'label' => 'Сумма заказа', 'name' => 'total_price'],
            ['width' => '33%', 'label' => 'Покупатель (Телефон)', 'name' => 'buyer_phone'],
        ];

        // $statusClass = array (
        //     array('status' => 'inactive',   'badge' => 'kt-badge--danger'),
        //     array('status' => 'active', 'badge' => 'kt-badge--success')
        // );

        $sortKey = 'id';

        return [
            'data' => $data,
            'columns' => $columns,
            //'statusClass' => $statusClass,
            'sortKey' => $sortKey,
            'draw' => $request->input('draw')
        ];
    }


    public function setStatus($zone, $order_id) {
        $order = $this->order->where('id', $order_id);
        if(!$order->first()) {
            return false;
        }

        $order->update(['zone' => $zone]);
        return ['status' => 'success'];
    }


    public function edit($request, $order_id) {
        $products = serialize($request->products);
        $order = $this->order->where('id', $order_id)->update([
            'name' => $request->order['name'],
            'phone' => $request->order['phone'],
            'email' => $request->order['email'],
            'comment' => $request->order['comment'],
            'product_id' => $products
        ]);
        return ['status' => 'success'];
    }

}
