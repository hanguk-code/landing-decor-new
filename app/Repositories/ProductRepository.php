<?php

namespace App\Repositories;

use App\Models\Product\OcProduct;

//use App\Models\Product\ProductAttribute;
use App\Models\Category\OcCategory;
use App\Models\Category\OcCategoryDescription;

use App\Models\User\Users;

//use App\Models\Reference\Tag;
use App\Models\Attribute\OcAttribute;
use App\Models\OcUrlAlias;
use App\Models\Product\OcProductAttribute;
use App\Models\Product\OcProductDescription;
use App\Models\Product\OcProductImage;
use App\Models\Product\ProductAttribute;
use App\Models\Product\OcProductToCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Order\Orders;
use App\Models\Order\Order;

class ProductRepository
{
    protected $product;
    protected $productAttribute;
    protected $category;
    //protected $tag;
    protected $attribute;
    protected $urlAlias;
    private $productGallery;

    /**
     * ItemRepository constructor.
     * @param OcProduct $product
     * @param OcProductAttribute $productAttribute
     * @param OcCategory $category
     * @param OcAttribute $attribute
     * @param OcUrlAlias $urlAlias
     * @param OcProductImage $productGallery
     */
    public function __construct(
        OcProduct $product,
        OcProductAttribute $productAttribute,
        OcCategory $category,
        //Tag              $tag,
        OcAttribute $attribute,
        OcUrlAlias $urlAlias,
        OcProductImage $productGallery
    )
    {
        $this->product = $product;
        $this->productAttribute = $productAttribute;
        $this->category = $category;
//        $this->tag = $tag;
        $this->attribute = $attribute;
        $this->urlAlias = $urlAlias;
        $this->productGallery = $productGallery;
    }

    /**
     * {@inheritDoc}
     */
    public function all($request)
    {
        if ($request->input('client')) {
            return $this->product->all();
        }

        $columns = ['sku', 'name', 'image', 'status', 'zone', 'price', 'price_rub', 'date_modified'];

        $length = $request->input('length') ?? 10;
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $searchByCategory = $request->input('search_by_category');
        $searchByZone = $request->input('search_by_zone');

//        $query = $this->product->with('description');
        $query = $this->product;


        if (!isset($searchByCategory) || empty($searchByCategory)) {
            if (is_numeric($searchValue)) {
                $query = $query->where('sku', 'like', $searchValue . '%');
//                ->where(function ($query) use ($searchValue) {
//                    $query->where('product_id', 'like', '%' . $searchValue)
//                        ->orWhere('sku', 'like', '%' . $searchValue . '');
//                    ->orWhere('name',       'like', '%' . $searchValue . '')
//                        ->orWhere('status', 'like', '%' . $searchValue . '')
//                    ->orWhere('date_added', 'like', '%' . $searchValue . '')
//                    ->orWhere('date_modified', 'like', '%' . $searchValue . '');
//                });
            } else {
                $query = $query->whereHas('description', function ($query) use ($searchValue) {
                    return $query->where('name', 'like', '%' . $searchValue . '%');
                });
            }
        } else {


            $query = $query->join('oc_product_to_category', 'oc_product_to_category.product_id', '=', 'oc_product.product_id')
                ->where('oc_product_to_category.category_id', $searchByCategory);
            //$productsByCategory = OcProductToCategory::where('category_id', $sCategoryData->category_id)->get();

        }

        if (isset($searchByZone) || !empty($searchByZone)) {
            $query = $query->where('zone', $searchByZone);
        }


        if (isset($columns[$column])) {
            $query = $query->orderBy($columns[$column], $dir);
        } else {
            $query = $query->orderBy('sku', 'desc');
        }

        $total = clone $query;
        $green = clone $query;
        $blue = $this->product;
        $white = clone $query;
        $red = clone $query;
        $siniy = clone $query;
        $violet = clone $query;
        $yellow = clone $query;
        $black = clone $query;
        $grey = clone $query;

        $price = clone $query;

        $total = $total->count();
        $total_price_all_products = number_format($price->whereNotIn('zone', ['black'])->sum('price'));
        $price_rub_all_products = number_format($price->whereNotIn('zone', ['black'])->sum('price_rub'));
        $red = $red->where('zone', 'red')->count();
        $siniy = $siniy->where('zone', 'siniy')->count();
        $violet = $violet->where('zone', 'violet')->count();
        $yellow = $yellow->where('zone', 'yellow')->count();
        $blue = $blue->where('zone', 'blue')->count();
        $green = $green->where('zone', 'green')->count();
        $black = $black->where('zone', 'black')->count();
        $grey = $grey->where('zone', 'grey')->count();
        $white = (int)$white->where('zone', 'white')->count() + (int)$blue + (int)$green;
        $data = $query->paginate($length);

        /*        if(!$productsByCategory) {
                    $data = $query->paginate($length);
                } else {
                    foreach($productsByCategory as $prod) {
                        $data = [];
                        $prodId = $prod['product_id'];
                        array_push($data, OcProduct::where('product_id', $prodId)->first());
                    }
                }*/


        $dataItem = [];

        foreach ($data as $item) {
            $dataItem[] = [
                'id' => $item->product_id,
                'sku' => $item->sku,
                'image' => $item->image,
                'name' => $item->description->name ?? '',
                'status' => $item->status,
                'zone' => $item->zone,
                'price' => $item->price,
                'price_rub' => $item->price_rub
//                'dates' => $item->dates,
            ];
        }

        $columns = [
//            array('width' => '33%', 'label' => 'Id', 'name' => 'id'),
            ['width' => '33%', 'label' => 'Артикул', 'name' => 'sku'],
            ['width' => '33%', 'label' => 'Фото', 'name' => 'image', 'type' => 'image'],
            ['width' => '33%', 'label' => 'Наименование', 'name' => 'name'],
            ['width' => '33%', 'label' => 'Статус', 'name' => 'status'],
            ['width' => '33%', 'label' => 'Цена закупки', 'name' => 'price_rub'],
            ['width' => '33%', 'label' => 'Цена продажи', 'name' => 'price'],
//            array('width' => '33%', 'label' => 'Даты', 'name' => 'dates')
        ];

        $statusClass = [
            ['status' => '0', 'badge' => 'kt-badge--danger'],
            ['status' => '1', 'badge' => 'kt-badge--success']
        ];

        $sortKey = 'id';


        if ((isset($searchValue) && !empty(($searchValue)))
            || (isset($searchByCategory) && !empty($searchByCategory))
            || (isset($searchByZone) && !empty($searchByZone))) {
            return [
                'data' => [
                    'data' => $dataItem,
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                ],

                'columns' => $columns,
                'statusClass' => $statusClass,
                'sortKey' => $sortKey,
                'draw' => $request->input('draw'),
                'stats' => [
                    'total' => $total,
                    'white' => $white,
                    'blue' => $blue,
                    'green' => $green,
                    'red' => $red,
                    'siniy' => $siniy,
                    'violet' => $violet,
                    'yellow' => $yellow,
                    'black' => $black,
                    'grey' => $grey,
                    'total_price_all_products' => $total_price_all_products,
                    'price_rub_all_products' => $price_rub_all_products
                ]
            ];
        }

        return [
            'data' => [
                'data' => $dataItem,
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ],

            'columns' => $columns,
            'statusClass' => $statusClass,
            'sortKey' => $sortKey,
            'draw' => $request->input('draw'),
            'stats' => [
                'total' => $total,
                'white' => $white,
                'blue' => $blue,
                'green' => $green,
                'red' => $red,
                'siniy' => $siniy,
                'violet' => $violet,
                'yellow' => $yellow,
                'black' => $black,
                'grey' => $grey,
                'total_price_all_products' => $total_price_all_products,
                'price_rub_all_products' => $price_rub_all_products
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $request)
    {
        $sorted = $this->product->orderBy('sort_order', 'DESC')->first();

        if (isset($sorted->id)) {
            $sortOrder = $sorted->sort_order + 1;
        } else {
            $sortOrder = 1;
        }

        $product = $this->product->create($request['product'] + [
                'sort_order' => $sortOrder,
                'upc' => 'new',
                'upc_date' => date('Y-m-d')
            ]);
        $product->description()->create($request['product']['description']);
        OcUrlAlias::create([
            'query' => DB::raw('\'product_id=' . $product->product_id . '\''),
            'keyword' => $request['product_slug'],
            'language_id' => 0,
        ]);

        if (isset($request['product']['categories'])) {
            $product->categories()->where(['product_id' => $product->product_id])->delete();
            foreach ($request['product']['categories'] as $item) {
                $product->categories()->updateOrInsert(
                    ['product_id' => $product->product_id, 'category_id' => $item]
                );
            }
            $product->categories()->updateOrInsert(
                ['product_id' => $product->product_id, 'category_id' => $request['product']['category_id']],
                ['main_category' => true]
            );
        }

        if (isset($request['photo'])) {
            $this->savePhoto($request['photo'], $product->product_id);
        }

        $attr = $request['product']['attributes'];
        if ($attr) {
            foreach ($attr as $item) {
                $this->productAttribute->updateOrInsert(
                    ['product_id' => $product->product_id, 'attribute_id' => $item['attribute_id'], 'language_id' => 2],
                    ['text' => $item['text']]
                );
            }
        }

        return $product;
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $productId)
    {
//        return $this->product->with(['categories:id,name', 'tags:id,name', 'attributes'])->findOrFail($productId);
        $product = $this->product
            ->with(['categories:product_id,category_id,main_category', 'description', 'attributes', 'gallery'])
            ->leftJoin('oc_url_alias', 'query', DB::raw('\'product_id=' . $productId . '\''))
            ->findOrFail($productId);

        $product->description->description = html_entity_decode($product->description->description);

        foreach ($product->gallery as $key => $image) {
            $product->gallery[$key]['image'] = env('API_WEB_URL') . '/image/' . $product->gallery[$key]['image'];
        }


        return $product;
    }


    /**
     * {@inheritDoc}
     */
    public function update(array $request, int $productId)
    {
        $product = $this->product->find($productId);
        $productData = $request['product'];

/*        if($productData['zone'] == 'black' || $productData['zone'] == 'white') {
            $productData['status'] = 1;
        } else {
            $productData['status'] = 0;
        }*/

        $product->update($productData);
        $product->description()->update($request['product']['description']);
        $urlAlias = OcUrlAlias::where('query', DB::raw('\'product_id=' . $product->product_id . '\''))->first();

        if (!$urlAlias) {
            OcUrlAlias::create([
                'query' => DB::raw('\'product_id=' . $product->product_id . '\''),
                'keyword' => $request['product_slug'],
                'language_id' => 0,
            ]);
        } else {
            $urlAlias->keyword = $request['product_slug'];
            $urlAlias->save();
        }

        if (isset($request['product']['categories'])) {
            $product->categories()->where(['product_id' => $productId])->delete();
            foreach ($request['product']['categories'] as $item) {
                $product->categories()->updateOrInsert(
                    ['product_id' => $productId, 'category_id' => $item]
                );
            }
            $product->categories()->updateOrInsert(
                ['product_id' => $productId, 'category_id' => $request['product']['main_category_id']],
                ['main_category' => true]
            );
        }

        $attr = $request['product']['attributes'];
        if ($attr) {
            foreach ($attr as $item) {
                $this->productAttribute->updateOrInsert(
                    ['product_id' => $productId, 'attribute_id' => $item['attribute_id'], 'language_id' => 2],
                    ['text' => $item['text']]
                );
            }
        }


        if (isset($request['photo']) && strpos($request['photo'], 'data:image') !== false) {
//            if (strpos($request['photo'], $request['product']['image']) === false) {
            $this->savePhoto($request['photo'], $productId);
//            }
        }

    }

    /**
     * {@inheritDoc}
     */
    public function destroy(int $productId)
    {
        $product = $this->product->find($productId);
        $product->delete();
    }

    public function destroyProductAttribute(int $id, int $attr)
    {
        $this->productAttribute->where('product_id', $id)->where('attribute_id', $attr)->delete();
    }

    public function savePhoto($logoDataImage, $id)
    {
        $filename = time() . '.' . explode('/', explode(':', substr($logoDataImage, 0, strpos($logoDataImage, ';')))[1])[1];
        $path = public_path('image/product/' . $id . '/');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $image = $logoDataImage;  // your base64 encoded
//        $image = str_replace('data:image/png;base64,', '', $image);
        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);

        File::put($path . $filename, base64_decode($image));

        $this->product::find($id)->update(['image' => 'product/' . $id . '/' . $filename]);
    }


    public function optionsData()
    {
//        $tag = $this->tag->select('id', 'name')->get();
        $tags = OcProductDescription::select('tag')->groupBy('tag')->get();

        $lastArticle = $this->product->max('product_id');

        $attributes = $this->attribute->select('attribute_id', 'attribute_id as label')
            ->with('description')
            ->get();

        if (!empty($attributes)) {
            foreach ($attributes as $key => $item) {
                $attribute[] = [
                    'id' => $item->attribute_id,
                    'label' => $item->description->name,
                    'language_id' => $item->description->language_id,
                ];
            }
        }

        $categories = $this->category
            ->select('category_id', 'parent_id', 'category_id as label')
            ->with('description')
            ->with('children')
            ->where('status', true)
            ->where('parent_id', 0)
            ->get();

        foreach ($categories as $category) {

            if (!empty($category->children)) {
                foreach ($category->children as $child) {
                    $subChildren = $child
                        ->select('category_id', 'parent_id', 'category_id as label')
                        ->with('description')
                        ->where('status', true)
                        ->where('parent_id', $child->category_id)
                        ->get();

                    if (!empty($subChildren)) {
                        foreach ($subChildren as $item) {
                            $subChild[] = [
                                'id' => $item->category_id,
                                'label' => $item->description->name,
                            ];
                        }
                    }

                    if (!empty($subChild)) {
                        $children[] = [
                            'id' => $child->category_id,
                            'label' => $child->description->name,
                            'children' => $subChild ?? []
                        ];
                    } else {
                        $children[] = [
                            'id' => $child->category_id,
                            'label' => $child->description->name,
                        ];
                    }

                    unset($subChild);
                }
            }

            $treeSelect[] = [
                'id' => $category->category_id,
                'label' => $category->description->name,
                'children' => $children ?? []
            ];

            unset($children);
        }

        return [
            'new_article' => ++$lastArticle,
            'tags' => $tags,
            'attributes' => $attribute ?? [],
            'categories' => $treeSelect ?? []
        ];
    }

    public function deleteChecked($request)
    {
        $checkedItems = $request->get('checkedItems');

        foreach ($checkedItems as $item) {
            $this->product->where('product_id', $item)->delete();
            $this->productAttribute->where('product_id', $item)->delete();
            $this->productGallery->where('product_id', $item)->delete();
        }
    }

    public function copy($request)
    {
        $productId = $request['id'];

        $productCopy = $this->product->with('description', 'categories', 'attributes', 'gallery')->find($productId);

        $urlAlias = OcUrlAlias::where('query', 'product_id=' . $productId)->first();

//        $sorted = $this->product->orderBy('sort_order', 'DESC')->first();
//        if (isset($sorted->id)) {
//            $sortOrder = $sorted->sort_order + 1;
//        } else {
//            $sortOrder = 1;
//        }

        $productArray = $productCopy->toArray();


        $tempOriginalProductId = $productArray['product_id'];
        unset($productArray['product_id']);

        $product = $this->product->create($productArray);

        $productArray['description']['name'] = $productArray['description']['name'] . '-копия';
        $product->description()->create($productArray['description']);

        OcUrlAlias::create([
            'query' => DB::raw('\'product_id=' . $product->product_id . '\''),
            'keyword' => $urlAlias['keyword'] . '-kopiya',
            'language_id' => 0,
        ]);

        if (!empty($productArray['categories'])) {
            foreach ($productArray['categories'] as $item) {
                $product->categories()->create([
                    'product_id' => $product->product_id,
                    'category_id' => $item['category_id'],
                    'main_category' => $item['main_category'],
                ]);
            }
        }

        if (!empty($productArray['attributes'])) {
            foreach ($productArray['attributes'] as $item) {
                $product->attributes()->create([
                    'product_id' => $product->product_id,
                    'attribute_id' => $item['attribute_id'],
                    'language_id' => $item['language_id'],
                    'text' => $item['text']
                ]);
            }
        }


        if (!empty($productArray['gallery'])) {
            foreach ($productArray['gallery'] as $item) {
                $oldPath = public_path('image/product/' . $tempOriginalProductId . '/');
                $newPath = public_path('image/product/' . $product->product_id . '/');
                $this->rcopy($oldPath, $newPath);
                $image = str_replace($tempOriginalProductId, $product->product_id, $item['image']);
                $product->gallery()->create([
                    'product_id' => $product->product_id,
                    /*'image' => $item['image'],*/
                    'image' => $image,
                    'sort_order' => $item['sort_order'],
                ]);
            }
        }

        $product->id = $product->product_id;
        $product->name = $productArray['description']['name'];
        return $product;
    }

    public function rcopy($src, $dst)
    {
        if (is_dir($src)) {
            if (!file_exists($dst)) {
                mkdir($dst);
            }

            $files = scandir($src);
            foreach ($files as $file)
                if ($file != "." && $file != "..") $this->rcopy("$src/$file", "$dst/$file");
        } else if (file_exists($src)) copy($src, $dst);
    }

    public function reset($id)
    {

        OcProduct::where('product_id', $id)->update([
            'manufacturer_id' => 0,
            'zone' => 'white'
        ]);

        $product = OcProduct::where('product_id', $id)->first();

        $orders = Orders::where('product_id', $id)->get();

        foreach ($orders as $order) {
            $user = Users::where('phone', $order->phone);
            if ($user->exists()) {
                $orderList = Order::where('phone', $user->first()['phone'])->get();
                foreach($orderList as $order) {
                    $product_ids = unserialize($order->product_id);
                    $key = array_search($id, $product_ids);
                    unset($product_ids[$key]);
                    $order->product_id = serialize($product_ids);
                    if($order->total_price > 0) {
                        $order->total_price = $order->total_price - (int)$product->price;
                    }
                    $order->update();

                    if($order->total_price == 0) {
                        $order->delete();
                    }
                }
                $user->update([
                    'number_of_purchases' => $user->first()['number_of_purchases'] - 1,
                    'total_sum' => (int)$user->first()['total_sum'] - (int)$product->price
                ]);
            }
        }
        Orders::where('product_id', $id)->delete();

        return;
    }

}
