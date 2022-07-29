<?php

namespace App\Repositories\Web;

use App\Models\Order\Order;
use App\Models\User\Users;
use App\Models\Order\ItemOrders;
use Illuminate\Support\Facades\Mail;

class OrderRepository
{
    protected $order;
	//public $data =[];
	public $idd;
    /**
     * OrderRepository constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
	/*public function shownumber($oid)
    {
    	$data[] = $oid;
    	$data[] = 'Text';
    	$data[] = '98';
    	//return view('mails.order_adm', $data);
    	return view('mails.order_adm')->with('iddata', $oid);
    }*/
    public function order($request)
    {

        if(count($request['cartData']) == 0) {
            return false;
        }

        $productId = [];

        $articles = [];

        $total_price = 0;

        foreach ($request['cartData'] as $item) {
            $productId[] = $item['id'];
            $total_price += (int) str_replace(' ', '', $item['price']);
            $articles[] = $item['article'];
        }
        

        $user = Users::where('phone', $request['userForm']['phone']);

        if(!$user->exists()) {
            $user = Users::create([
                'name' => $request['userForm']['name'],
                'phone' => $request['userForm']['phoneDetails']['formattedNumber'],
                'email' => $request['userForm']['email'],
                'total_sum' => $total_price,
                'number_of_purchases' => 1
            ]);
        } else {
            $user = $user->first();
            $user->total_sum += $total_price;
            $user->number_of_purchases += 1;
            $user->update();
        }

        $odata=$this->order->create([
            'product_id' => serialize($productId),
            'name' => $request['userForm']['name'],
            'phone' => $request['userForm']['phoneDetails']['formattedNumber'],
            'email' => $request['userForm']['email'],
            'comment' => $request['userForm']['comment'],
            'total_price' => $total_price,
            'zone' => 'red'
        ]);



		$idd=$odata['id'];
		if(empty($idd)) {$idd='415';}
		//$this->shownumber($idd);
		
		//$textone[] = $ordid;
		//$textone[] = 'OrderRepository';

        Mail::to($request['userForm']['email'])->send(new SendOrderForm($request,$idd));
        //Mail::to(env('EMAIL_ORDER', 'enot70@yandex.ru'))->send(new SendOrderForm($request));
        //Mail::to('vo710mail@gmail.com')->send(new SendAdminForm($request,$idd));
		Mail::to(env('EMAIL_ORDER'))->send(new SendAdminForm($request,$idd));
		
    }

}
