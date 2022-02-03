<?php

namespace App\Repositories\Web;

use App\Models\Product\OcProduct;

//use App\Models\Product\ProductAttribute;
use App\Models\Category\OcCategory;

//use App\Models\Reference\Tag;
use App\Models\Attribute\OcAttribute;
use App\Models\OcUrlAlias;
use App\Models\Product\OcProductAttribute;
use App\Models\Product\OcProductToCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    protected $product;
    //protected $productAttribute;
    protected $category;
    //protected $tag;
    protected $attribute;
    protected $urlAlias;

    /**
     * ItemRepository constructor.
     * @param OcProduct $product
     * @param OcCategory $category
     * @param OcAttribute $attribute
     * @param OcUrlAlias $urlAlias
     * @param OcProductToCategory $productToCategory
     */
    public function __construct(
        OcProduct $product,
        //ProductAttribute $productAttribute,
        OcCategory $category,
        //Tag              $tag,
        OcAttribute $attribute,
        OcUrlAlias $urlAlias,
        OcProductToCategory $productToCategory
    )
    {
        $this->product = $product;
        //$this->productAttribute = $productAttribute;
        $this->category = $category;
        //$this->tag              = $tag;
        $this->attribute = $attribute;
        $this->urlAlias = $urlAlias;
        $this->productToCategory = $productToCategory;
    }

    /**
     * {@inheritDoc}
     */
    public function all($request)
    {
        $limit = $request->input('limit') ?? 15;
        $products = $this->product
            ->with('description')
            ->where('status', true)
            ->where('zone', '!=', 'black')
            ->orderBy('date_modified', 'desc')
            ->paginate($limit);

        return [
            'products' => self::parseProducts($products),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ];
    }

    public function newLimit($request)
    {
        $limit = $request->input('limit') ?? 3;
        $products = $this->product
            ->with('description')
            ->where('upc', 'new')
            ->where('status', true)
            ->where('zone', '!=', 'black')
            ->orderBy('upc_date', 'desc')
            ->limit($limit)
            ->get();

        return self::parseProducts($products);
    }

    public function related($request)
    {
        $products = Cache::remember('related_products_' . $request->input('product_id'), 60, function () use ($request) {
            $product = $this->product->with('description')->where('product_id', $request->input('product_id'))->first();
            return $this->product
                ->whereHas('description', function ($query) use ($product) {
                    return $query->where('tag', $product->description->tag);
                })
                ->where('status', true)
                ->where('zone', '!=', 'black')
                ->paginate(24);
        });

//        $products = $this->product
//            ->whereHas('description', function ($query) use ($product) {
//                return $query->where('tag', $product->description->tag);
//            })
//            ->where('status', true)
////            ->orderBy('product_id', 'asc')
//            ->get();

        return self::parseProducts($products);
    }

    public function newAll($request)
    {
        $length = $request->input('limit') ?? 15;
        $products = $this->product
            ->with('description')
//            ->inRandomOrder()
            ->where('upc', 'new')
            ->where('jan', '>', 0)
            ->where('status', true)
            ->where('zone', '!=', 'black')
            ->orderBy('upc_date', 'desc')
            ->paginate($length);

        return [
            'products' => self::parseProducts($products),
            'pagination' => [
                'total' => $products->total(),
                //per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ];
    }


    public function archiveAll($request)
    {
        $length = $request->input('limit') ?? 12;
        $products = $this->product
            ->with('description')
//            ->where('quantity', '<', 0)
            ->where('status', true)
            ->where('zone', 'black')
            ->orderBy('date_available', 'desc')
            ->paginate($length)
        ;


        return [
            'products' => self::parseProducts($products),
            'pagination' => [
                'total' => $products->total(),
                //per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ];
    }


    /**
     * {@inheritDoc}
     */
    public function find(int $productId)
    {
        $product = $this->product->with(['categories:id,name', 'tags:id,name', 'attributes'])->findOrFail($productId);

        return $product;
    }

    public function optionsData()
    {
        $tag = $this->tag->select('id', 'name')->get();
        $attribute = $this->attribute->select('id', 'name')->get();
        $category = $this->category->select('id', 'name as label', 'parent_id')
            ->where('parent_id', NULL)
            ->with('children')
            ->where('status', 'active')
            ->get();

        $data = [
            'tags' => $tag,
            'attributes' => $attribute,
            'categories' => $category
        ];

        return $data;
    }

    public function type($request, $url)
    {
        $length = 12;
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $country = $request->input('country');
        $material = $request->input('material');

        $urlExplode = explode('/', $url);
        $c = count($urlExplode);
        $lastUrl = $urlExplode[$c - 1];


        //$qu = 'category_id='.$item['category_id'];
        $urlAlias = $this->urlAlias->where('keyword', $lastUrl)->first();
        if(!$urlAlias){
            return [];
        }
        
        $expUrlAlias = explode('=', $urlAlias['query']);
        $itemType = $expUrlAlias[0];

        if ($itemType == 'product_id') {
            //$product = $this->product->where('status', 'active')->orderBy('sort_order', 'asc')->get();
            $product = $this->product->with(['gallery', 'attributes', 'mainCategory'])
                ->where('product_id', $expUrlAlias[1])
                ->first();
//            dd($product);

            if (isset($product->product_id)) {

                $qu = 'product_id=' . $product['product_id'];
                $urlAlias = $this->urlAlias->where('query', $qu)->first();

                //foreach ($product as $item) {
                $categoryProd = $this->category->where('category_id', $product['mainCategory']['category_id'])->first();
                $quCat = 'category_id=' . $categoryProd['category_id'];
                $urlAliasCat = $this->urlAlias->where('query', $quCat)->first();

                if ($categoryProd['parent_id']) {
                    $categoryChild = $this->category->where('category_id', $categoryProd['parent_id'])->first();
                    $quCatChild = 'category_id=' . $categoryChild['category_id'];
                    $urlAliasCatChild = $this->urlAlias->where('query', $quCatChild)->first();

                    if ($categoryChild['parent_id']) {
                        $categoryChildS = $this->category->where('category_id', $categoryChild['parent_id'])->first();
                        $quCatChildS = 'category_id=' . $categoryChildS['category_id'];
                        $urlAliasCatChildS = $this->urlAlias->where('query', $quCatChildS)->first();

                        $productUrl = $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword'] . '/' . $urlAlias['keyword'];

                        $breadcrumbs = [
                            ['name' => 'Главная', 'url' => '/'],
                            ['name' => $categoryChildS['description']['name'],
                                'url' => '/' . $urlAliasCatChildS['keyword']],
                            ['name' => $categoryChild['description']['name'],
                                'url' => '/' . $categoryChildS['keyword'] . '/' . $urlAliasCatChildS['keyword']],
                            ['name' => $categoryProd['description']['name'],
                                'url' => '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']],
                            ['name' => $product['description']['name'],
                                'url' => '/' . $productUrl]
                        ];
                    } else {
                        $productUrl = $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword'] . '/' . $urlAlias['keyword'];

                        $breadcrumbs = [
                            ['name' => 'Главная', 'url' => '/'],
                            ['name' => $categoryChild['description']['name'], 'url' => '/' . $urlAliasCatChild['keyword']],
                            ['name' => $categoryProd['description']['name'], 'url' => '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']],
                            ['name' => $product['description']['name'], 'url' => '/' . $productUrl]
                        ];
                    }


                } else {
                    $productUrl = $urlAliasCat['keyword'] . '/' . $urlAlias['keyword'];

                    $breadcrumbs = [
                        ['name' => 'Главная', 'url' => '/'],
                        ['name' => $categoryProd['description']['name'], 'url' => '/' . $urlAliasCat['keyword']],
                        ['name' => 'Главная', 'url' => '/'],
                        ['name' => $product['description']['name'], 'url' => '/' . $productUrl]
                    ];
                }

                $gallery = [];
                foreach ($product['gallery'] as $itemGallery) {
                    $gallery[] = [
                        'name' => $itemGallery['name'],
                        'image_url' => '/' . $itemGallery['image']
                    ];
                }

                $attribute = [];
                foreach ($product['attributes'] as $itemAttr) {
                    $attribute[] = [
                        'name' => $itemAttr['text'],
                        'description' => $itemAttr['description']['name']
                    ];
                }

                //if($url === $productUrl) {
                $productData = [
                    'id' => $product['product_id'],
                    'name' => $product['description']['name'],
                    'image_url' => $product['image'],
                    'url' => '/' . $productUrl,
                    'price' => self::priceFormat($product['price']),
                    'article' => $product['sku'],
                    'description' => htmlspecialchars_decode($product['description']['description']),
                    'breadcrumbs' => $breadcrumbs,
                    'gallery' => $gallery,
                    'attributes' => $attribute,
                    'archive' => $product['zone'] === 'black',
                    'jan' => $product['jan'],
                    'upc' => $product['upc'],
                    'zone' => $product['zone']
                ];

                //dd($productData);
                return [
                    'type' => 'product',
                    'product' => $productData
                ];
                //}
                //}
            }

        } // end itemType

        if ($itemType == 'category_id') {
            //$category = $this->category->with('children')->where('parent_id', NULL)->where('status', 'active')->orderBy('sort_order', 'asc')->get();

            $category = $this->category->where('category_id', $expUrlAlias[1])->with('description')->first();
            $quCat = 'category_id=' . $category['category_id'];
            $urlAliasCat = $this->urlAlias->where('query', $quCat)->first();

            if ($category['parent_id'] > 0) {
                $categoryCatChild = $this->category->where('category_id', $category['parent_id'])->first();
                $quCatChild = 'category_id=' . $categoryCatChild['category_id'];
                $urlAliasCatChild = $this->urlAlias->where('query', $quCatChild)->first();
            }

            if (isset($categoryCatChild->id)) {
                $categoryUrl = $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword'];
            } else {
                $categoryUrl = $urlAliasCat['keyword'];
            }

            $productCat = $this->product->whereHas('categories', function ($query) use ($category) {
                return $query->where('category_id', $category->category_id);
            });

            if ($priceMin) {
                $productCat = $productCat->where('price', '>=', $priceMin);
            }
            if ($priceMax) {
                $productCat = $productCat->where('price', '<=', $priceMax);
            }
            if ($country) {
                $productCat = $productCat->whereHas('attributes', function ($query) use ($country) {
                    return $query->where('text', $country);
                });
            }
            if ($material) {
                $productCat = $productCat->whereHas('attributes', function ($query) use ($material) {
                    return $query->where('text', $material);
                });
            }
            $productCat = $productCat
                ->where('status', true)
                ->where('zone', '!=', 'black')
                ->orderby('date_modified', 'desc')->paginate($length);

            $productCatData = [];
            foreach ($productCat as $productItemCat) {
                $quCatProd = 'product_id=' . $productItemCat['product_id'];
                $urlAliasProd = $this->urlAlias->where('query', $quCatProd)->orderBy('url_alias_id', 'desc')->first();

                if (!$urlAliasProd) {
                    continue;
                }

                $totalUrl = '/' . $categoryUrl . '/' . $urlAliasProd['keyword'];
                if ($category['parent_id'] > 0) {
                    $c2 = $this->category->where('category_id', $category['parent_id'])->first();
                    $qc2 = 'category_id=' . $c2['category_id'];
                    $url2 = $this->urlAlias->where('query', $qc2)->first();
                    $totalUrl = '/' . $url2['keyword'] . '/' . $categoryUrl . '/' . $urlAliasProd['keyword'];

                    if ($c2['parent_id'] > 0) {
                        $c3 = $this->category->where('category_id', $c2['parent_id'])->first();
                        $qc3 = 'category_id=' . $c3['category_id'];
                        $url3 = $this->urlAlias->where('query', $qc3)->first();
                        $totalUrl = '/' . $url3['keyword'] . '/' . $url2['keyword'] . '/' . $categoryUrl . '/' . $urlAliasProd['keyword'];

                    }
                }
                $productIds[] = $productItemCat['product_id'];

                $productCatData[] = [
                    'id' => $productItemCat['product_id'],
                    'name' => $productItemCat['description']['name'],
                    'image_url' => $productItemCat['image'],
                    'url' => $totalUrl,
                    'upc' => $productItemCat['upc'],
                    'jan' => $productItemCat['jan'],
                    'price' => self::priceFormat($productItemCat['price']),
                    'article' => $productItemCat['sku'],
                    'archive' => $productItemCat['zone'] === 'black',
                    'zone' => $productItemCat['zone']
                ];
            }

            $subCategoryData = [];
            $subCategory = $this->category->where('parent_id', $category['category_id'])->where('status', 1)->orderBy('sort_order', 'asc')->get();

            foreach ($subCategory as $subItem) {

                $quCatSubCat = 'category_id=' . $subItem['category_id'];
                $urlAliasSubCat = $this->urlAlias->where('query', $quCatSubCat)->first();

                $totalUrl = '/' . $urlAliasCat['keyword'] . '/' . $urlAliasSubCat['keyword'];
                if ($category['parent_id'] > 0) {
                    $c2 = $this->category->where('category_id', $category['parent_id'])->first();
                    $qc2 = 'category_id=' . $c2['category_id'];
                    $url2 = $this->urlAlias->where('query', $qc2)->first();
                    $totalUrl = '/' . $url2['keyword'] . '/' . $urlAliasCat['keyword'] . '/' . $urlAliasSubCat['keyword'];

                    if ($c2['parent_id'] > 0) {
                        $c3 = $this->category->where('category_id', $c2['parent_id'])->first();
                        $qc3 = 'category_id=' . $c3['category_id'];
                        $url3 = $this->urlAlias->where('query', $qc3)->first();
                        $totalUrl = '/' . $url3['keyword'] . '/' . $url2['keyword'] . '/' . $urlAliasCat['keyword'] . '/' . $urlAliasSubCat['keyword'];

                    }
                }
                $subCategoryData[] = [
                    'id' => $subItem['category_id'],
                    'name' => $subItem['description']['name'],
                    'image_url' => $subItem['image'],
                    'url' => $totalUrl,
                    'seo_text' => $subItem['seo_text'],
                    'meta_keyword' => $subItem['description']['meta_keyword'],
                    'meta_description' => $subItem['description']['meta_description'],
                    'seo_title' => $subItem['description']['seo_title']
                ];


            }

            if ($category['parent_id'] > 0) {
                $categoryCatChild = $this->category->where('category_id', $category['parent_id'])->first();
                $quCatChild = 'category_id=' . $categoryCatChild['category_id'];
                $urlAliasCatChild = $this->urlAlias->where('query', $quCatChild)->first();

                if ($categoryCatChild['parent_id'] > 0) {
                    $categoryCatChildS = $this->category->where('category_id', $categoryCatChild['parent_id'])->with('description')->first();
                    $quCatChildS = 'category_id=' . $categoryCatChildS['category_id'];
                    $urlAliasCatChildS = $this->urlAlias->where('query', $quCatChildS)->first();

                    if ($categoryCatChildS['parent_id'] > 0) {
                        $breadcrumbs = [
                            ['name' => 'Главная', 'url' => '/'],
                            ['name' => $category['description']['name'],
                                'url' => '/' . $urlAliasCatChildS['keyword'] . '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']],
                            ['name' => $categoryCatChild['description']['name'],
                                'url' => '/' . $urlAliasCatChild['keyword']],
                            ['name' => $category['description']['name'],
                                'url' => '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']]
                        ];

                    } else {
                        $breadcrumbs = [
                            ['name' => 'Главная', 'url' => '/'],
                            ['name' => $categoryCatChildS['description']['name'],
                                'url' => '/' . $urlAliasCatChildS['keyword']],
                            ['name' => $categoryCatChild['description']['name'],
                                'url' => $urlAliasCatChildS['keyword'] . '/' . $urlAliasCatChild['keyword']],
                            ['name' => $category['description']['name'],
                                'url' => $urlAliasCatChildS['keyword'] . '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']]
                        ];

                        // $breadcrumbs = [
                        //     ['name' => 'Главная', 'url' => '/'],
                        //     ['name' => $category['description']['name'],
                        //         'url' => '/'.$urlAliasCat['keyword']],
                        //     ['name' => $categoryCatChild['description']['name'],
                        //         'url' => '/'.$urlAliasCat['keyword'].'/'.$urlAliasCatChild['keyword']],
                        //     ['name' => $categoryCatChildS['description']['name'],
                        //         'url' => '/'.$urlAliasCat['keyword'].'/'.$urlAliasCatChild['keyword']].'/'.$urlAliasCatChildS['keyword']]
                        // ];
                    }
                } else {
                    $breadcrumbs = [
                        ['name' => 'Главная', 'url' => '/'],
                        ['name' => $categoryCatChild['description']['name'],
                            'url' => '/' . $urlAliasCatChild['keyword']],
                        ['name' => $category['description']['name'],
                            'url' => '/' . $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword']]
                    ];
                    // $breadcrumbs = [
                    //     ['name' => 'Главная', 'url' => '/'],
                    //     ['name' => $category['description']['name'],
                    //         'url' => '/'.$urlAliasCat['keyword']],
                    //     ['name' => $categoryCatChild['description']['name'],
                    //         'url' => '/'.$urlAliasCat['keyword'].'/'.$urlAliasCatChild['keyword']],
                    //     ['name' => $categoryCatChildS['description']['name'],
                    //         'url' => '/'.$urlAliasCat['keyword'].'/'.$urlAliasCatChild['keyword']].'/'.$urlAliasCatChildS['keyword']]
                    // ];
                }
            } else {
                $breadcrumbs = [
                    ['name' => 'Главная', 'url' => '/'],
                    ['name' => $category['description']['name'],
                        'url' => '/' . $urlAliasCat['keyword']],
                ];
            }

            $categoryData = [
                'id' => $category['category_id'],
                'name' => $category['description']['name'],
                'image_url' => $category['image'],
                'url' => $urlAliasCat['keyword'],
                'seo_text' => $category['seo_text'],
                'meta_keyword' => $category['description']['meta_keyword'],
                'meta_description' => $category['description']['meta_description'],
                'seo_title' => $category['description']['seo_title']
            ];

            $priceLimit = Cache::remember('price_limit__' . $category['category_id'], 60, function () use ($category) {
                return $this->product
                    ->select(\DB::raw('MIN(round(price)) AS min_price, MAX(round(price)) AS max_price'))
                    ->whereHas('categories', function ($query) use ($category) {
                        return $query->where('category_id', $category['category_id']);
                    })
                    ->first();
            });

            $countries = Cache::remember('categories__' . $category['category_id'], 60, function () use ($category) {
                return OcProductAttribute::where('attribute_id', 12)
                    ->leftJoin('oc_product_to_category', 'oc_product_to_category.product_id', '=', 'oc_product_attribute.product_id')
                    ->where('oc_product_to_category.category_id', $category['category_id'])
                    ->groupBy(DB::raw("TRIM(text)"))
                    ->get();
            });

            $materials = Cache::remember('materials__' . $category['category_id'], 60, function () use ($category) {
                return OcProductAttribute::where('attribute_id', 13)
                    ->leftJoin('oc_product_to_category', 'oc_product_to_category.product_id', '=', 'oc_product_attribute.product_id')
                    ->where('oc_product_to_category.category_id', $category['category_id'])
                    ->groupBy(DB::raw("TRIM(text)"))
                    ->get();
            });

            return [
                'type' => 'category',
                'sub_categories' => $subCategoryData,
                'products' => $productCatData,
                'category' => $categoryData,
                'breadcrumbs' => $breadcrumbs,
                'countries' => $countries,
                'materials' => $materials,
                'price_limit' => $priceLimit,
                'pagination' => [
                    'total' => $productCat->total(),
                    //per_page' => $productCat->perPage(),
                    'current_page' => $productCat->currentPage(),
                    'last_page' => $productCat->lastPage(),
                    'from' => $productCat->firstItem(),
                    'to' => $productCat->lastItem(),
                ],
            ];
            //}
        } //end itemType

    }

    public function getProduct($request)
    {
        $length = 12;
        $type = $request->input('type');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $country = $request->input('country');

        $product = $this->product;
        if ($priceMin) {
            $product = $product->where('price', '>=', $priceMin);
        }
        if ($priceMax) {
            $product = $product->where('price', '<=', $priceMax);
        }

        $product = $product->paginate($length);

        $productData = [];
        foreach ($product as $item) {
            $qu = 'product_id=' . $item['product_id'];
            $urlAlias = $this->urlAlias->where('query', $qu)->first();
            $productData[] = [
                'id' => $item['product_id'],
                'name' => $item['description']['name'],
                'image_url' => $item['image'],
                'url' => $urlAlias['keyword'],
                'price' => self::priceFormat($item['price']),
                'article' => $item['sku'],
            ];
        }

        return $productData;

    }

    public function search($request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit') ?? 6;

        if (is_numeric($search)) {
            $productQuery = $this->product
                ->with('description')
                ->where('sku', 'like', "{$search}%");
        } else {
            $productQuery = $this->product
                ->whereHas('description', function ($query) use ($search) {
                    return $query->where('name', 'like', "{$search}%");
                });
        }

        return self::parseProducts($productQuery->where('status', true)->where('zone', '!=', 'black')->limit($limit)->get());
    }

    /**
     * @param $product
     * @return array|null
     */
    protected function parseProducts($product)
    {
        foreach ($product as $item) {
            $categoryId = $this->productToCategory->where(['product_id' => $item['product_id']])->first();
            if (!$categoryId) {
                continue;
            }
            $category = $this->category->find($categoryId->category_id);
            $quCat = 'category_id=' . $categoryId->category_id;
            $urlAliasCat = $this->urlAlias->where('query', $quCat)->first();

            $qu = 'product_id=' . $item['product_id'];
            $urlAlias = $this->urlAlias->where('query', $qu)->first();
            if (!$urlAlias) {
                continue;
            }

            if ($category && $category['parent_id']) {
//                $categoryChild = $this->category->find($category['parent_id']);
                $quCatChild = 'category_id=' . $categoryId->category_id;
                $urlAliasCatChild = $this->urlAlias->where('query', $quCatChild)->first();
                $url = $urlAliasCatChild['keyword'] . '/' . $urlAliasCat['keyword'] . '/' . $urlAlias['keyword'];
            } else {
                $url = $urlAliasCat['keyword'] . '/' . $urlAlias['keyword'];
            }

            $productData[] = [
                'id' => $item['product_id'],
                'name' => $item['description']['name'],
                'description' => strip_tags(html_entity_decode($item['description']['description'])),
                'image_url' => $item['image'],
                'url' => $url ?? '',
                'price' => self::priceFormat($item['price']),
                'article' => $item['sku'],
                'jan' => $item['jan'],
            ];
        }

        return $productData ?? null;
    }

    protected function priceFormat($price)
    {
        return number_format($price, 0, '.', ' ');
    }

}
