<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use App\Http\Resources\VariantResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->paginate(5);
        $variants = Variant::with('product_variants')->get();
        
        return view('products.index')->with('products', $products)->with('variants', $variants)->with('test', 'ABCD');
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
        $this->validate($request, [
            'title' => 'required',
        ]);

        $product = new Product;
        $product->title = $request->input('title');
        $product->sku = $request->input('sku');
        $product->description = $request->input('description');

        $product->save();

        // if(count($request->product_images)>0){
        //     foreach ($request->product_images as $image) {
        //         $fileName = uniqid(Auth::user()->user_id).'.'.file($image['dataURL'])->extension();
        //         $image->file('dataURL')->move(public_path('uploads/products'), $fileName);
        //         $path = URL::asset('uploads/products/'.$fileName);

        //         $uploads = new ProductImage();
        //         $uploads->product_id = $product->id ;
        //         $uploads->file_path = $fileName;
        //         $uploads->save();
        //     }
        // }

        $variants = [];
        foreach($request->input('product_variant') as $variant){
            $variant_id = $variant['option'];
            foreach($variant['tags'] as $tag){
                $var_item = ['variant' => $tag, 'variant_id' => $variant_id, 'product_id' => $product->id];
                array_push($variants, $var_item);
            }
        }

        $product->product_variants()->createMany($variants);

        $variant_prices = [];

        foreach($request->input('product_variant_prices') as $variant){
            $variant_one = null;
            $variant_two = null;
            $variant_three = null;
            foreach(explode("/",$variant['title']) as $item){
                $product_variant = $product->product_variants()->where('variant', $item)->first();
                if($product_variant){
                    if($product_variant->variant_id == 1){
                        $variant_one = $product_variant->id;
                    }elseif($product_variant->variant_id == 2){
                        $variant_two = $product_variant->id;
                    }else{
                        $variant_three = $product_variant->id;
                    }
                }
            }
            $var_item = ['product_variant_one' => $variant_one, 'product_variant_two' => $variant_two, 'product_variant_three' => $variant_three, 'price' => $variant['price'], 'stock' => $variant['stock']];
            array_push($variant_prices, $var_item);
        }

        $product->product_variant_prices()->createMany($variant_prices);

        $response = "The product has been created successfully!";
        return response($response);
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
        $variants = Variant::all();
        //return new ProductResource($product);
        //return $variants;
        return view('products.edit')->with('product', new ProductResource($product))->with('variants', $variants);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $product->title = $request->input('title');
        $product->sku = $request->input('sku');
        $product->description = $request->input('description');

        $product->save();

        // if($request->hasfile('product_image')){
        //     foreach ($request->file('product_image') as $key => $file) {
        //         $fileName = uniqid(Auth::user()->id).'.'.$file->extension();
        //         $file->move(public_path().'/uploads/products/', $fileName);    

        //         $uploads = new ProductImage();
        //         $uploads->product_id = $product->id ;
        //         $uploads->file_path = $fileName;
        //         $uploads->save();
        //     }
        // }
        $product->product_variants()->delete();
        $variants = [];
        foreach($request->input('product_variant') as $variant){
            $variant_id = $variant['option'];
            foreach($variant['tags'] as $tag){
                $var_item = ['variant' => $tag, 'variant_id' => $variant_id, 'product_id' => $product->id];
                array_push($variants, $var_item);
            }
        }

        $product->product_variants()->createMany($variants);

        $product->product_variant_prices()->delete();

        $variant_prices = [];
        foreach($request->input('product_variant_prices') as $variant){
            $variant_one = null;
            $variant_two = null;
            $variant_three = null;
            foreach(explode("/",$variant['title']) as $item){
                $product_variant = $product->product_variants()->where('variant', $item)->first();
                if($product_variant){
                    if($product_variant->variant_id == 1){
                        $variant_one = $product_variant->id;
                    }elseif($product_variant->variant_id == 2){
                        $variant_two = $product_variant->id;
                    }else{
                        $variant_three = $product_variant->id;
                    }
                }
            }
            $var_item = ['product_variant_one' => $variant_one, 'product_variant_two' => $variant_two, 'product_variant_three' => $variant_three, 'price' => $variant['price'], 'stock' => $variant['stock']];
            array_push($variant_prices, $var_item);
        }

        $product->product_variant_prices()->createMany($variant_prices);

        $response = "The product has been updated successfully!";
        return response($response);
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

    public function filterproducts(Request $request)
    {
        $products = Product::where('title', 'LIKE','%'.$request->title.'%')
                        ->whereHas('product_variants', function($q)
                            {
                                $q->where('variant','LIKE','%'.$request->variant.'%');
                            })
                        ->whereHas('product_variant_prices', function($q)
                        {
                            $q->whereBetween('price', [$request->price_from, $request->price_to]);
                        })
                        ->paginate(5);
        $variants = Variant::with('product_variants')->get();
        
        return view('products.index')->with('products', $products)->with('variants', $variants);
    }
}
