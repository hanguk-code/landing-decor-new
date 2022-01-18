<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

use App\Models\Product\Product;

class ItemOrders extends Model
{
    public $table = 'item_orders';

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;

    protected $fillable = [
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'zone',
        'articles',
        'total_price'
    ];

}
