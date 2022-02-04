<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

use App\Models\Product\Product;

class Order extends Model
{
    public $table = 'order';

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'name',
        'phone',
        'email',
        'comment',
        'comments',
        'tags',
        'total_price',
        'zone',
        'created_at',
        'address',
        'amount_of_discount'
    ];

}
