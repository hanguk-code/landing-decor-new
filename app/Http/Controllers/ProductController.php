<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Http\Resources\JResource;
use App\Repositories\ProductRepository;
use App\Models\Product\OcProduct;

/**
 *
 * Class ProductController
 *
 * @package  App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * ItemController constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     *
     */
    public function index(Request $request)
    {
        $product = $this->productRepository->all($request);
        return new JResource($product);
    }

    /**
     *
     */
    public function store(Request $request)
    {
        $product = $this->productRepository->create($request->all());

        return (new JResource(['status' => 'success', 'item_id' => $product->product_id]))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     *
     */
    public function show(int $productId)
    {
        $product = $this->productRepository->find($productId);
        return new JResource($product);
    }

    /**
     *
     */
    public function update(Request $request, $productId)
    {
        $this->productRepository->update( $request->all(), $productId);

        return (new JResource(['status' => 'success', 'id' => request()->route('bouquets')]));
    }

    /**
     *
     */
    public function destroy(int $productId)
    {
        $this->productRepository->delete($productId);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function destroyProductAttribute(int $id, int $attr)
    {
         $this->productRepository->destroyProductAttribute($id, $attr);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function optionsData()
    {
        $product = $this->productRepository->optionsData();

        return new JResource($product);
    }

    public function deleteChecked(Request $request)
    {
        $this->productRepository->deleteChecked($request);

        return (new JResource(['status' => 'success']));
    }

    public function copy(Request $request)
    {
        $product = $this->productRepository->copy($request->all());

        return new JResource(['status' => 'success', 'product' => $product]);
    }


    /**
     * @param Request $request
     * @return JResource
     */
    public function sell(Request $request, $id)
    {
        $this->productRepository->sell( $id);

        return (new JResource(['status' => 'success', 'id' => request()->route('bouquets')]));
    }

    /**
     * @param Request $request
     * @return JResource
     */
    public function reset(Request $request, $id)
    {
        return (new JResource(['status' => 'success', 'data' => $this->productRepository->reset( $id)]));
    }

    public function setZone(Request $request, $id) {
        if($request->zone == "black" || $request->zone == "white" || $request->zone == "yellow") {
            OcProduct::where('product_id', $id)->update(['zone' => $request->zone, 'status' => 1]);
        } else {
            OcProduct::where('product_id', $id)->update(['zone' => $request->zone, 'status' => 0]);
        }
        return (new JResource(['status' => 'success']));
    }

    

}

