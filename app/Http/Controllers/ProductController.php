<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index() {
        return view('products.index');
    }

    public function variants() {
        $variants = ProductVariant::all();
        return response()->json($variants);
    }

    public function test(Request $request) {
        // $query = ProductVariantPrice::with(['product','variant'])->get();
       $query = Product::with(['product_variants', 'product_variants_price']);
        // Filter by product name
        if ($request->input('name')) {
            $query->where('title', 'like', '%' . $request->input('name') . '%');
        }

        // Filter by price variant
        if ($request->input('price_from') && $request->input('price_to')) {
            $query->whereHas('product_variants_price', function ($q) use ($request) {
                $q->where('price', '>=', $request->price_from)
                    ->where('price', '<=', $request->price_to);
            });
        }

        // filter by variant product name
        if ($request->input('variant')) {
            $query->whereHas('product_variants', function ($q) use ($request) {
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
        $productValidation = $request->validate([
            'title'                  => 'required',
            'sku'                    => 'required',
            'description'            => 'required',
            'product_variant_prices' => 'required',
        ]);
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
            $variant_id_one           = isset($convert_variant_to_array[0]) ? ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $prod->id)->get()[0]->id : null;
            $variant_id_two           = isset($convert_variant_to_array[1]) ? ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $prod->id)->get()[0]->id : null;
            $variant_id_three         = isset($convert_variant_to_array[2]) ? ProductVariant::where('variant', $convert_variant_to_array[2])->where('product_id', $prod->id)->get()[0]->id : null;
            // return $variant_id_three.$variant_id_one.$variant_id_two;
            ProductVariantPrice::create([
                'product_variant_one'   => $variant_id_one,
                'product_variant_two'   => $variant_id_two,
                'product_variant_three' => $variant_id_three,
                'price'                 => $variant_price['price'],
                'stock'                 => $variant_price['stock'],
                'product_id'            => $prod->id,
            ]);
        }
        return $variant_new->product_id;
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
        $product  = Product::with(['product_variants', 'images', 'product_variants_price'])->where('id', $product->id)->get();
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product) {
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        $variants = $request->product_variant;
        //in case of edit. if i only edit stock and price, last variant is deleted. and destroy display pages. check if ($reqVariantSize > $dbVarSize) {
        $reqVarArray    = [];
        $reqVariantSize = 0;
        foreach ($variants as $variant) {
            $reqVariantSize += sizeof($variant['tags']);
            if (sizeof($variant['tags']) > 1) {
                for ($i = 0; $i < sizeof($variant['tags']); $i++) {
                    array_push($reqVarArray, $variant['option'] . '=>' . $variant['tags'][$i]);
                }
            } else {
                array_push($reqVarArray, $variant['option'] . '=>' . $variant['tags'][0]);
            }
        }

        $dbVariant = ProductVariant::where('product_id', $product->id)->get();

        $dbVarSize  = sizeof($dbVariant);
        $varCounter = 0;
        if ($reqVariantSize > $dbVarSize) {
            foreach ($reqVarArray as $variant) {
                if ($varCounter >= $dbVarSize) {
                    $insertVar = explode('=>', $variant);
                    ProductVariant::create([
                        'variant'    => $insertVar[1],
                        'variant_id' => $insertVar[0],
                        'product_id' => $product->id,
                    ]);
                } else {
                    $insertVar                          = explode('=>', $variant);
                    $dbVariant[$varCounter]->variant    = $insertVar[1];
                    $dbVariant[$varCounter]->variant_id = $insertVar[0];
                    $dbVariant[$varCounter]->product_id = $product->id;
                    $dbVariant[$varCounter]->save();

                }
                $varCounter++; //1 1, 2 2
            }
        }else if($reqVariantSize == $dbVarSize){

        }else {
            for (; $varCounter < $dbVarSize; $varCounter++) {
                if ($varCounter < $dbVarSize - 1) {
                    $insertVar                          = explode('=>', $reqVarArray[$varCounter]);
                    $dbVariant[$varCounter]->variant    = $insertVar[1];
                    $dbVariant[$varCounter]->variant_id = $insertVar[0];
                    $dbVariant[$varCounter]->product_id = $product->id;
                    $dbVariant[$varCounter]->save();
                } else {
                    $dbVariant[$varCounter]->delete();
                }
            }
        }

        $variant_prices = $request->product_variant_prices;
        //return $variant_prices;
        $reqPriceSize = sizeof($variant_prices);
       //return $reqPriceSize;
        $dbProductVariantPrice = ProductVariantPrice::where('product_id', $product->id)->get();
        $dbPriceSize = sizeof($dbProductVariantPrice);
        //return $dbPriceSize;
        if ($reqPriceSize > $dbPriceSize) {
            for ($i = 0; $i < $reqPriceSize; $i++) {
                if($i<$dbPriceSize){
                    //$productData                        = ProductVariantPrice::find($variant_prices[1][$i]);
                    $data                               = $variant_prices[$i];
                    $remove_forward_slash               = rtrim($data['title'], '/');
                    $convert_variant_to_array           = explode('/', $remove_forward_slash);
                    $variant_id_one                     = isset($convert_variant_to_array[0]) ? ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_two                     = isset($convert_variant_to_array[1]) ? ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_three                   = isset($convert_variant_to_array[2]) ? ProductVariant::where('variant', $convert_variant_to_array[2])->where('product_id', $product->id)->get()[0]->id : null;
                    $dbProductVariantPrice[$i]->product_variant_one   = $variant_id_one;
                    $dbProductVariantPrice[$i]->product_variant_two   = $variant_id_two;
                    $dbProductVariantPrice[$i]->product_variant_three = $variant_id_three;
                    $dbProductVariantPrice[$i]->price                 = $data['price'];
                    $dbProductVariantPrice[$i]->stock                 = $data['stock'];
                    $dbProductVariantPrice[$i]->product_id            = $product->id;
                    $dbProductVariantPrice[$i]->save();
                }else{
                    $data                               = $variant_prices[$i];
                    //return $data;
                    $remove_forward_slash               = rtrim($data['title'], '/');
                    $convert_variant_to_array           = explode('/', $remove_forward_slash);
                    $variant_id_one                     = isset($convert_variant_to_array[0]) ? ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_two                     = isset($convert_variant_to_array[1]) ? ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_three                   = isset($convert_variant_to_array[2]) ? ProductVariant::where('variant', $convert_variant_to_array[2])->where('product_id', $product->id)->get()[0]->id : null;
                    ProductVariantPrice::create([
                        'product_variant_one'   => $variant_id_one,
                        'product_variant_two'   => $variant_id_two,
                        'product_variant_three' => $variant_id_three,
                        'price'                 => $data['price'],
                        'stock'                 => $data['stock'],
                        'product_id'            => $product->id,
                    ]);
                }
            }
        } elseif ($reqPriceSize == $dbPriceSize) {
           for ($i=0; $i < $reqPriceSize; $i++) { 
                $data                               = $variant_prices[$i];
                $remove_forward_slash               = rtrim($data['title'], '/');
                $convert_variant_to_array           = explode('/', $remove_forward_slash);
                $variant_id_one                     = isset($convert_variant_to_array[0]) ? ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $product->id)->get()[0]->id : null;
                $variant_id_two                     = isset($convert_variant_to_array[1]) ? ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $product->id)->get()[0]->id : null;
                $variant_id_three                   = isset($convert_variant_to_array[2]) ? ProductVariant::where('variant', $convert_variant_to_array[2])->where('product_id', $product->id)->get()[0]->id : null;
                $dbProductVariantPrice[$i]->product_variant_one   = $variant_id_one;
                $dbProductVariantPrice[$i]->product_variant_two   = $variant_id_two;
                $dbProductVariantPrice[$i]->product_variant_three = $variant_id_three;
                $dbProductVariantPrice[$i]->price                 = $data['price'];
                $dbProductVariantPrice[$i]->stock                 = $data['stock'];
                $dbProductVariantPrice[$i]->product_id            = $product->id;
                $dbProductVariantPrice[$i]->save();
           }
        } else {
            for ($i = 0; $i < $dbPriceSize; $i++) {
                //$productData                        = ProductVariantPrice::find($variant_prices[1][$i]);
                if($i<$reqPriceSize){                
                    $data                               = $variant_prices[$i];
                    $remove_forward_slash               = rtrim($data['title'], '/');
                    $convert_variant_to_array           = explode('/', $remove_forward_slash);
                    $variant_id_one                     = isset($convert_variant_to_array[0]) ? ProductVariant::where('variant', $convert_variant_to_array[0])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_two                     = isset($convert_variant_to_array[1]) ? ProductVariant::where('variant', $convert_variant_to_array[1])->where('product_id', $product->id)->get()[0]->id : null;
                    $variant_id_three                   = isset($convert_variant_to_array[2]) ? ProductVariant::where('variant', $convert_variant_to_array[2])->where('product_id', $product->id)->get()[0]->id : null;
                    $dbProductVariantPrice[$i]->product_variant_one   = $variant_id_one;
                    $dbProductVariantPrice[$i]->product_variant_two   = $variant_id_two;
                    $dbProductVariantPrice[$i]->product_variant_three = $variant_id_three;
                    $dbProductVariantPrice[$i]->price                 = $data['price'];
                    $dbProductVariantPrice[$i]->stock                 = $data['stock'];
                    $dbProductVariantPrice[$i]->product_id            = $product->id;
                    $dbProductVariantPrice[$i]->save();
                }else{
                    $dbProductVariantPrice[$i]->delete();
                }
            }
        }
        //return 'awesome';
    }

    public function updateImages(Request $request, $id) {
        $reqArray = [];
        foreach ($request->images as $reqImage) {
            array_push($reqArray, $reqImage['dataURL']);
        }
        $dbImages = ProductImage::where('product_id', $id)->get();
        foreach ($request->images as $reqImage) {
            if (isset($reqImage['name'])) {
                foreach ($dbImages as $dbImage) {
                    if (!in_array($dbImage['file_path'], $reqArray)) {
                        $dbImage->delete();
                    }
                }
            } else {
                $decoded_image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $reqImage['dataURL']));
                $filename      = 'image_' . rand() . '.jpg';
                $path          = public_path('uploads/images/' . $filename);
                file_put_contents($path, $decoded_image);
                $image             = new ProductImage();
                $image->product_id = $id;
                $image->file_path  = asset('uploads/images/' . $filename);
                $image->save();
            }
        }
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