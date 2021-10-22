<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $products = Product::filtered($request)->latest()->paginate(2);

//        dd($products[0]->product_variant_prices[0]->);
        $variants = ProductVariant::with('variation')->get()->groupBy('variation.title');
        return view('products.index', compact('products', 'variants', 'request'));
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
     * @param ProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            // Get data for product data store into product table
            $product_data = $request->except('product_variant', 'product_variant_prices', 'product_image');

            // Store product and get inserted product
            $product = Product::create($product_data);

            // Product image upload part
            $product_images = $request->product_image;
            if (count($product_images) > 0) {
                foreach ($product_images as $product_image) {
                    // image path
                    $folderPath = "product-images/";

                    // image processing from base64
                    $image_parts    = explode(";base64,", $product_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type     = $image_type_aux[1];
                    $image_base64   = base64_decode($image_parts[1]);
                    $file           = $folderPath . uniqid() . '.' . $image_type;

                    Storage::disk('public')->put($file, $image_base64);

                    // Store product image into product image table
                    ProductImage::create([
                        'product_id' => $product->id,
                        'file_path'  => $file
                    ]);
                }
            }

            // Product variant store part
            $product_variants               = $request->product_variant;
            $product_variant_price_variants = [];
            if (count($product_variants) > 0) {
                foreach ($product_variants as $product_variant) {
                    foreach ($product_variant['tags'] as $tag) {
                        $new_product_variant                  = ProductVariant::create([
                            'variant'    => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id
                        ]);
                        $product_variant_price_variants[$tag] = $new_product_variant->id;
                    }
                }
            }

            // Product variant prices store part
            $product_variant_prices = $request->product_variant_prices;
            if (count($product_variant_prices) > 0) {
                foreach ($product_variant_prices as $product_variant_price) {
                    $titleParts = explode('/', $product_variant_price['title']);
                    foreach ($titleParts as $key => $titlePart) {
                        if ($titlePart != "") {
                            if ($key == 0) {
                                $product_variant_price['product_variant_one'] = $product_variant_price_variants[$titlePart];
                            } elseif ($key == 1) {
                                $product_variant_price['product_variant_two'] = $product_variant_price_variants[$titlePart];
                            } else {
                                $product_variant_price['product_variant_three'] = $product_variant_price_variants[$titlePart];
                            }
                        }
                    }
                    $product_variant_price['product_id'] = $product->id;
                    ProductVariantPrice::create($product_variant_price);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data'    => $product
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            \DB::rollBack();
            return \response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data'    => null
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        dd($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse|Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product->load(['product_variants', 'product_variants.variation', 'product_images', 'product_variant_prices']);
        $product_variants = ProductVariant::where('product_id', $product->id)->with('variation')->get()->groupBy('variant_id');
        return view('products.edit', compact('variants', 'product', 'product_variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductRequest $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();
            // Get data for product data store into product table
            $product_data = $request->except('product_variant', 'product_variant_prices', 'product_image');

            // Store product and get inserted product
            $product->update($product_data);

            // Product image upload part
            $product_images = $request->product_image;
            if (count($product_images) > 0) {
                if ($product->product_images->count() > 0) {
                    foreach ($product->product_images as $old_product_image) {
                        if (Storage::disk('public')->exists($old_product_image->file_path)) {
                            Storage::disk('public')->delete($old_product_image->file_path);
                        }
                        $old_product_image->delete();
                    }
                }
                foreach ($product_images as $product_image) {
                    // image path
                    $folderPath = "product-images/";

                    // image processing from base64
                    $image_parts    = explode(";base64,", $product_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type     = $image_type_aux[1];
                    $image_base64   = base64_decode($image_parts[1]);
                    $file           = $folderPath . uniqid() . '.' . $image_type;

                    Storage::disk('public')->put($file, $image_base64);

                    // Store product image into product image table
                    ProductImage::create([
                        'product_id' => $product->id,
                        'file_path'  => $file
                    ]);
                }
            }

            // Product variant store part
            $product->product_variants()->delete();
            $product_variants               = $request->product_variant;
            $product_variant_price_variants = [];
            if (count($product_variants) > 0) {
                foreach ($product_variants as $product_variant) {
                    foreach ($product_variant['tags'] as $tag) {
                        $new_product_variant                  = ProductVariant::create([
                            'variant'    => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id
                        ]);
                        $product_variant_price_variants[$tag] = $new_product_variant->id;
                    }
                }
            }

            // Product variant prices store part
            $product->product_variant_prices()->delete();
            $product_variant_prices = $request->product_variant_prices;
            if (count($product_variant_prices) > 0) {
                foreach ($product_variant_prices as $product_variant_price) {
                    $titleParts = explode('/', $product_variant_price['title']);
                    foreach ($titleParts as $key => $titlePart) {
                        if ($titlePart != "") {
                            if ($key == 0) {
                                $product_variant_price['product_variant_one'] = $product_variant_price_variants[$titlePart];
                            } elseif ($key == 1) {
                                $product_variant_price['product_variant_two'] = $product_variant_price_variants[$titlePart];
                            } else {
                                $product_variant_price['product_variant_three'] = $product_variant_price_variants[$titlePart];
                            }
                        }
                    }
                    $product_variant_price['product_id'] = $product->id;
                    ProductVariantPrice::create($product_variant_price);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data'    => $product
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            \DB::rollBack();
            return \response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data'    => null
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // Delete product images
        if ($product->product_images->count() > 0) {
            foreach ($product->product_images as $old_product_image) {
                if (Storage::disk('public')->exists($old_product_image->file_path)) {
                    Storage::disk('public')->delete($old_product_image->file_path);
                }
                $old_product_image->delete();
            }
        }
        // Delete product variants
        $product->product_variants()->delete();

        // Delete product variant prices
        $product->product_variant_prices()->delete();

        // Finally, delete product
        $product->delete();
        return back();
    }
}
