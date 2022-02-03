<?php

namespace App\Models\Product;

use App\Models\OcUrlAlias;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;
use App\Models\Category\OcCategory;
use Illuminate\Support\Facades\DB;

//use App\Models\Attribute\OcAttribute;
//use App\Models\Reference\Tag;
use App\Models\Product\OcProductToCategory;

class OcProduct extends Model
{
    protected $table = 'oc_product';
    protected $primaryKey = 'product_id';

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'date_modified';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        'sku',
        'upc',
        'upc_date',

        'jan',
        'isbn',
        'quantity',
        'stock_status_id',

        'image',
        'manufacturer_id',
        'price',
        'date_available',

        'sort_order',
        'viewed',
        'status',

        'date_added',
        'date_modified',


        'price_rub',
        'price_euro',
        'kurs',
        'zone',
        'comment',
        'amount_of_discount',
        'date_zakup'
    ];

    // protected $casts = [
    //     "is_booked" => "boolean",
    // ];

    protected $appends = ['dates'];

    public function getDatesAttribute()
    {
        return 'Р: ' . $this->date_modified . "<br>" . 'С: ' . $this->date_added;
    }

    public function gallery()
    {
        return $this->hasMany(OcProductImage::class, 'product_id', 'product_id')->orderBy('sort_order');
    }

    public function attributes()
    {
        return $this->hasMany(OcProductAttribute::class, 'product_id', 'product_id')->with('description');
    }

    public function description()
    {
        return $this->hasOne(OcProductDescription::class, 'product_id', 'product_id');
    }

    public function mainCategory()
    {
        return $this->hasOne(OcProductToCategory::class, 'product_id', 'product_id')->where('main_category', 1);
    }

    public function categories()
    {
        return $this->hasMany(OcProductToCategory::class, 'product_id', 'product_id');
    }

    // public function tags()
    // {
    //     return $this->belongsToMany(Tag::class, 'product_tag', 'product_id', 'tag_id');
    // }

    public function productByCategory() {
        return $this->belongsTo(OcProductToCategory::class, 'product_id', 'product_id');
    }

}
