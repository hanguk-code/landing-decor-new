<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Http\Resources\JResource;
use App\Models\Areas;
use App\Models\Order\Order;
use App\Models\BrowsingHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Article;
use App\Models\OcUrlAlias;
use App\Models\Product\OcProduct;
use App\Models\Product\OcProductDescription;

class ArticleController extends Controller
{

    public function getArticle($id) {
        $article = Article::where('id', $id)->first();
        if(!$article) {
            return response()->json([
                "status" => "fail",
                "article" => $article
            ]);
        }

        $productIds = unserialize($article->products);
        $article['products'] = DB::table('oc_product')->join('oc_product_description', 'oc_product.product_id', '=', 'oc_product_description.product_id')
            ->whereIn('oc_product.product_id', $productIds)
            ->orderBy('oc_product.date_modified', 'desc')
            ->get();

        foreach($article['products'] as $key => $value) {
            $temp_article = (array) $article['products'][$key];
            $urlAlias = OcUrlAlias::where('query', 'LIKE', 'product_id='.$temp_article['product_id'])->first();
            $temp_article['url'] = $urlAlias->keyword;
            $article['products'][$key] = (object) $temp_article;
        }

        return response()->json([
            "status" => "success",
            "article" => $article
        ]);
    }

    public function articles() {
        $articles = Article::limit(10)->orderBy('id', 'desc')->get();
        return response()->json([
            "status" => "success",
            "articles" => $articles
        ]);
    }

    public function article($id) {
        $article = Article::where('category_id', $id)->first();
        if($article) {
            $article['products'] = join(", ", unserialize($article['products']));
        }
        return (new JResource([
            "status" => "success",
            "data" => $article
        ]));
    }

    public function updateArticle(Request $request, $id){
        $article = $request->article;
        $article['products'] = serialize(explode(',', str_replace(' ', '', $article['products'])));
        if(Article::where('category_id', $article['category_id'])->count() > 0) {
            Article::where('category_id', $article['category_id'])->update($article);
        } else {
            Article::create($article);
        }
        return (new JResource([
            "status" => "success"
        ]));
    }

    public function deleteArticle($id) {
        if(Article::where('category_id', $id)->first()) {
            Article::where('category_id', $id)->delete();
        }
        return (new JResource([
            "status" => "success"
        ]));
    }

}
