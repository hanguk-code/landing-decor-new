<?php

namespace App\Models\Category;

use Illuminate\Database\Eloquent\Model;

class OcCategory extends Model
{

    public $table = 'oc_category';

    protected $primaryKey = 'category_id';
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

    protected $fillable = [
        'image',
        'parent_id',
        'top',
        'column',
        'sort_order',
        'status',

        'date_added',
        'date_modified',
        'seo_text'
    ];

     protected $appends = ['dates'];

    public function getDatesAttribute()
    {
        return 'Р: '.$this->date_modified."<br>".'С: '.$this->date_added;
    }

    public function parent()
    {
        return $this->belongsTo(OcCategory::class, 'parent_id')->with('description')->selectRaw('category_id, image, parent_id')->where('status', 1);
    }

    public function child()
    {
        return $this->hasMany(OcCategory::class, 'parent_id')->with('description')->selectRaw('category_id, image,  parent_id')->where('status', 1);
    }

    //recursive child call
    public function children()
    {
        return $this->child()->with('children')->with('description')->where('status', 1);
    }

    public function description()
    {
        return $this->hasOne(OcCategoryDescription::class, 'category_id', 'category_id');
    }
}
