<?php

namespace App\Models\User;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    public $table = 'users';

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'status',
        'address',
        'tags',
        'total_sum',
        'number_of_purchases',
        'comments'
    ];

    public function getOrders() {
        return Order::where('phone', $this->phone)->get();
    }

}
