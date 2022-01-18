<?php

namespace App\Repositories\Web;

use App\Models\Category\OcCategory;
use App\Models\OcUrlAlias;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    protected $category;
    protected $urlAlias;

    /**
     * CategoryRepository constructor.
     * @param OcCategory $category
     * @param OcUrlAlias $urlAlias
     */
    public function __construct(
        OcCategory $category,
        OcUrlAlias $urlAlias
    ) {
        $this->category = $category;
        $this->urlAlias = $urlAlias;
    }

    /**
     * getting all categories
     * TODO: will need refactoring this function
     * @param $request
     * @return array
     */
    public function all($request): array
    {

        $recordsPerPage = 100;
        $category = Cache::rememberForever('top_categories', function () use ($recordsPerPage) {
            return $this->category::where('status', 1)
                ->where('parent_id', 0)
                ->orderBy('sort_order', 'asc')
                ->paginate($recordsPerPage);
        });

        $categories = self::getCategories($category);

/*        array_unshift($categories, [
            'id' => 116,
            'image_url' => 'data/glavnay/9374c94eef03.jpg',
            'name' => 'Новый Год и Рождество!!!',
            'url' => '/novyj-god-i-rozhdestvo-'
        ]);*/

        return [
            'pagination' => [
                'total' => $category->total(),
                'per_page' => $category->perPage(),
                'current_page' => $category->currentPage(),
                'last_page' => $category->lastPage(),
                'from' => $category->firstItem(),
                'to' => $category->lastItem()
            ],


            'data' => $categories
        ];
    }

    /**
     * getting all categories
     * @param $topCategories
     * @return array
     */
    protected function getCategories($topCategories): array
    {
        return Cache::rememberForever('getCategories_' . $topCategories, function () use ($topCategories) {
            foreach ($topCategories as $category) {
                $categoryChild = [];
                $qu = 'category_id=' . $category['category_id'];
                $urlAlias = $this->urlAlias->where('query', $qu)->first();
                if ($category['children']) {
                    foreach ($category['children'] as $itemChild) {
                        $nextChildren = [];
                        $quChild = 'category_id=' . $itemChild['category_id'];
                        $urlAliasChild = $this->urlAlias->where('query', $quChild)->first();

                        $children = Cache::rememberForever('children' . $itemChild['category_id'],
                            function () use ($itemChild) {
                                return $this->category::where('status', 1)
                                    ->where('parent_id', $itemChild['category_id'])
                                    ->with('description')
                                    ->orderBy('sort_order', 'asc')
                                    ->get()
                                    ->toArray();
                            });
                        if (count($children) > 0) {
                            foreach ($children as $child) {
                                $urlAliasChild2 = $this->urlAlias->where('query',
                                    'category_id=' . $child['category_id'])->first();
                                $nextChildren[] = [
                                    'id' => $child['category_id'],
                                    'name' => $child['description']['name'],
                                    'image_url' => $child['image'],
                                    'url' => '/' . $urlAlias['keyword'] . '/' . $urlAliasChild['keyword'] . '/' . $urlAliasChild2['keyword']
                                ];
                            }
                        }
                        $categoryChild[] = [
                            'id' => $itemChild['category_id'],
                            'name' => $itemChild['description']['name'],
                            'image_url' => $itemChild['image'],
                            'url' => '/' . $urlAlias['keyword'] . '/' . $urlAliasChild['keyword'],
                            'children' => $nextChildren ?? []
                        ];

                    }
                }

                $categories[] = [
                    'id' => $category['category_id'],
                    'name' => $category['description']['name'],
                    'image_url' => $category['image'],
                    'url' => '/' . $urlAlias['keyword'],
                    'children' => $categoryChild
                ];
            }
            return $categories ?? [];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $categoryId)
    {
        $category = $this->category->with('description')->findOrFail($categoryId);

        return $category;
    }

    public function optionsData($request)
    {
        $currentCatId = $request->input('id');

        $category = $this->category->select('id', 'name as label', 'parent_id')->with('children')->where('status',
            'active');

        if (isset($currentCatId)) {
            $category = $category->where('id', '!=', $currentCatId);
        }

        $category = $category->get();
        $category = $category->push([
            'id' => 0,
            'label' => 'Главная категория',
            'parent_id' => null,
            'children' => [],
            'dates' => "<br><br>"
        ]);

        $data = [
            'categories' => $category,
        ];

        return $data;
    }

}
