<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Spatie\Permission\Models\Role;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        //Check Filter Data
        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;

        $product_variants = ProductVariant::select('product_variants.variant', 'product_variants.variant_id')->groupBy('variant_id')->groupBy('variant')->get();

        $all_products = Product::with('productVariants', 'productVariantPrices')->select(
            'products.id',
            'products.title',
            'products.description',
            'products.created_at'
        )
            ->orderBy('products.id', 'desc');

        // Filter option
        if ($title != null) {
            $all_products = $all_products->where('products.title', $title);
        }
        if ($date != null) {
            $all_products = $all_products->where(DB::raw('DATE(products.created_at)'), $date);
        }
        $all_products = $all_products->paginate(20);


        return view('products.index', compact('all_products', 'product_variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {
            DB::transaction(function () use ($request) {
                //Data Insert Into Product Table
                $product_store = new Product();
                $product_store->title = $request->title;
                $product_store->sku = $request->sku;
                $product_store->description = $request->description;
                $product_store->save();

                //Keep Tracking Single Variant
                $variant_options = [];

                foreach ($request->product_variant as $single_product_variant) {
                    foreach ($single_product_variant['tags'] as $single_product_variant_tag) {
                        //Data Insert Into product_variants Table
                        $product_variant = new ProductVariant();
                        $product_variant->product_id = $product_store->id;
                        $variant = Variant::findOrFail($single_product_variant['option']);
                        array_push($variant_options, $single_product_variant['option']);
                        $product_variant->variant = $single_product_variant_tag;
                        $product_variant->variant_id = $variant->id;
                        $product_variant->product_id = $product_store->id;
                        $product_variant->save();
                    }
                }
                //Format the variants into unique
                $unique_variant_options = array_unique($variant_options);
                $unique_variant_options = array_values($unique_variant_options);

                //Data Insert Into product_variant_prices Table
                $product_varirant_price = new ProductVariantPrice();
                $product_varirant_price->price = 0;
                $product_varirant_price->stock = 0;
                $product_varirant_price->product_id = $product_store->id;

                if (isset($unique_variant_options[0])) $product_varirant_price->product_variant_one = $unique_variant_options[0];
                if (isset($unique_variant_options[1])) $product_varirant_price->product_variant_two = $unique_variant_options[1];
                if (isset($unique_variant_options[2])) $product_varirant_price->product_variant_three = $unique_variant_options[2];
                $product_varirant_price->save();

                //Data Insert Into product_image Table
                $product_image = new ProductImage();
                $product_image->product_id = $product_store->id;
                $product_image->file_path = "no";
                $product_image->thumbnail = 1;
                $product_image->save();

            });
        }
        catch (Exception $e) {
            return response()->json([
                'error' => $e]);
        }
        return response()->json([
            'success' => "Product Created Successfully"]);

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {

        $product = Product::findOrFail($product->id);
        $product_id = $product->id;

        $product = Product::with('productVariantPrices')
            ->where('id', $product_id)
            ->first();

        $product_variants = Variant::with(['productVariants' => function ($q) use ($product_id) {
            $q->where('product_id', $product_id);
        }])->get();

        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product', 'product_variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        try {
            $product_update = Product::findOrFail($id);
            $product_update->title = $request->title;
            $product_update->sku = $request->sku;
            $product_update->description = $request->description;
            $product_update->save();

            $update_variant_options = [];

            DB::table('product_variants')->where('product_id', $id)->delete();
            foreach ($request->product_variants as $product_variant) {
                //Data Insert Into product variantsTable
                foreach ((array)$product_variant['product_variant_names'] as $product_variant_name) {
                    $new_product_variant = new ProductVariant();
                    $new_product_variant->variant_id = $product_variant['id'];
                    array_push($update_variant_options, $product_variant['id']);
                    $new_product_variant->variant = $product_variant_name;
                    $new_product_variant->product_id = $id;
                    $new_product_variant->save();
                }

            }
            DB::table('product_variant_prices')->where('product_variant_prices.product_id', $id)->delete();
            $update_variant_option = array_unique($update_variant_options);
            $update_variant_option = array_values($update_variant_option);

            //Data Insert Into product_variant_prices Table
            $product_varirant_price = new ProductVariantPrice();
            $product_varirant_price->price = 0;
            $product_varirant_price->stock = 0;
            $product_varirant_price->product_id = $id;

            if (isset($update_variant_option[0])) $product_varirant_price->product_variant_one = $update_variant_option[0];
            if (isset($update_variant_option[1])) $product_varirant_price->product_variant_two = $update_variant_option[1];
            if (isset($update_variant_option[2])) $product_varirant_price->product_variant_three = $update_variant_option[2];
            $product_varirant_price->save();

        }
        catch (Exception $e) {
            return response()->json([
                'error' => $e]);
        }
        return response()->json([
            'success' => "Product Updated Successfully"]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
