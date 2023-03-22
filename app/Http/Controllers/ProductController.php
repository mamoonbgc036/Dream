<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use App\Models\ProductVariantPrice;

class ProductController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index() {
        return view('products.index');
    }

    public function test(Request $request) {
        // $products = Product::with(['product_variants', 'product_variants_price'])->paginate(3);
        // return response()->json($products);
        //return $request->input('name');
        $query = Product::with(['product_variants', 'product_variants_price']);

        // Filter by product name
        if ($request->input('name')) {
            $query->where('title', 'like', '%' . $request->input('name') . '%');
        }

        // Filter by price variant
        if ($request->input('price_from') && $request->input('price_to')) {
            $query->whereHas('product_variants_price', function($q) use ($request) {
                $q->where('price', '>=', $request->price_from)
                  ->where('price', '<=', $request->price_to);
            });
        }

          // filter by variant product name
        if ($request->input('variant')) {
            $query->whereHas('product_variants', function($q) use ($request) {
                $q->where('variant', 'like', '%' . $request->input('variant') . '%');
            });
        }

        // Filter by created-at date
        if ($request->input('created_at')) {
            $date = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $query->where('created_at', '>=', $date->startOfDay());
        }

        $products = $query->paginate(3);
        return response()->json($products);
    }

    public function filter(Request $request) {
        // $products = Product::with(['product_variants', 'product_variants_price'])->paginate(3);
        // return response()->json($products);
        //return $request->input('name');
        //$query = Product::with(['product_variants_price']);
        $query = ProductVariantPrice::with(['product']);

        // // Filter by product name
        if ($request->input('price_from')) {
            $query->where('price', '=', $request->price_from);;
        }

        // Filter by price variant
        if ($request->input('name')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('title', '=', $request->input('name'));
                //   ->where('price', '<=', $request->price_to);
            });
        }
        // // Filter by created-at date
        // if ($request->input('created_at')) {
        //     $date = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
        //     $query->where('created_at', '>=', $date->startOfDay());
        // }
        // return $query->get()[0]->product_variant_one;
        $products = $query->paginate(3);
        return response()->json($products);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create() {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $prod = Product::create([
            'title'       => $request->title,
            'sku'         => $request->sku,
            'description' => $request->description,
        ]);
        //product_variant
        $variants = $request->product_variant;
        foreach ($variants as $variant) {
            if (sizeof($variant['tags']) > 1) {
                for ($i = 0; $i < sizeof($variant['tags']); $i++) {
                    $variant_new = ProductVariant::create([
                        'variant'    => $variant['tags'][$i],
                        'variant_id' => $variant['option'],
                        'product_id' => $prod->id,
                    ]);
                }
            } else {
                $variant_new = ProductVariant::create([
                    'variant'    => $variant['tags'][0],
                    'variant_id' => $variant['option'],
                    'product_id' => $prod->id,
                ]);
            }
        }

        $variant_prices = $request->product_variant_prices;
        foreach ($variant_prices as $variant_price) {
            $remove_forward_slash     = rtrim($variant_price['title'], '/');
            $convert_variant_to_array = explode('/', $remove_forward_slash);
            $variant_id_one           = ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $prod->id)->get();
            $variant_id_two           = ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $prod->id)->get();
            ProductVariantPrice::create([
                'product_variant_one' => $variant_id_one[0]->id,
                'product_variant_two' => $variant_id_two[0]->id,
                'price'               => $variant_price['price'],
                'stock'               => $variant_price['stock'],
                'product_id'          => $prod->id,
            ]);
        }
        return $prod->id;
    }

    public function image(Request $req) {
        $files = $req->file('images');
        $id    = $req->input('id');
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/images'), $fileName);
            $image             = new ProductImage();
            $image->product_id = $id;
            $image->file_path  = asset('uploads/images/' . $fileName);
            $image->save();
        }
        return 'images';
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product) {
    
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product) {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product) {
        //
    }
}