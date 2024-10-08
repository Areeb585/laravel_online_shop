<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest('id')->with('product_images');

        if ($request->get('keyword') != "") {
            $products = $products->where('title', 'like', '%' . $request->keyword . '%');
        }

        $products = $products->paginate(10);
        $data['products'] = $products;
        return view('admin.products.list', $data);
    }

    public function create()
{
    $data = [];
    $categories = Category::orderBy('name', 'ASC')->get();
    $subCategories = SubCategory::orderBy('name', 'ASC')->get();
    $brands = Brand::orderBy('name', 'ASC')->get();

    // Use 'categories' to pass the variable to the view
    $data['categories'] = $categories;
    $data['subCategories'] = $subCategories;
    $data['brands'] = $brands;
    
    return view('admin.products.create', $data);
}


    public function store(Request $request)
    {
        \Log::info('Store request data:', $request->all()); // Log request data

        $rules = [
            'title'         => 'required',
            'slug'          => 'required|unique:products',
            'price'         => 'required|numeric',
            'sku'           => 'required|unique:products',
            'track_qty'     => 'required|in:Yes,No',
            'category'      => 'required|numeric',
            'sub_category'  => 'required|numeric',
            'is_featured'   => 'required|in:Yes,No',
        ];

        if ($request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Validation errors:', $validator->errors()->all()); // Log validation errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = ($request->track_qty == 'Yes') ? $request->qty : null;
            $product->status = $request->status;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();

            // Save gallery images
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {
                    $tempImageInfo  = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'Null';
                    $productImage->save();

                    $imageName = $product->id . '-' . $productImage->id . '-' . time() . '.' . $ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product thumbnail
                    // Large Image
                    $sourcePath = public_path() . '/temp/' . $imageName;
                    $destPath = public_path() . '/uploads/products/large/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image
                    $destPath = public_path() . '/uploads/products/small/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);
                    $image->save($destPath);
                }
            }

            \Log::info('Product saved successfully:', $product->toArray()); // Log successful save

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully',
                'redirect_url' => route('admin.products.index')
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred:', ['error' => $e->getMessage()]); // Log exception message
            return response()->json([
                'status' => false,
                'message' => 'Failed to save product. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function edit($id, Request $request)
    {
       $product = Product::find($id);

       if(empty($product)){
        return redirect()->route('admin.products.index')->with('error', 'Product not found');
       }
       $productImages = ProductImage::where('product_id', $product->id)->get();
       $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        
       $relatedProducts = [];
       //fetch related products
       if($product->related_products != ''){
         $productArray = explode(',',$product->related_products);
         $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
       }

        $data = [];        
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();

        // Use 'categories' to pass the variable to the view
        $data['categories'] = $categories; 
        $data['brands'] = $brands;
        $data['product'] = $product;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;
        return view('admin.products.edit',$data);
    }

    public function update($id, Request $request){
       \Log::info('Store request data:', $request->all()); // Log request data
         
    $product = Product::find($id);
        $rules = [
            'title'         => 'required',
            'slug'          => 'required|unique:products,slug,'.$product->id.',id',
            'price'         => 'required|numeric',
            'sku'           => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty'     => 'required|in:Yes,No',
            'category'      => 'required|numeric',
            'sub_category'  => 'required|numeric',
            'is_featured'   => 'required|in:Yes,No',
        ];

        if ($request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Validation errors:', $validator->errors()->all()); // Log validation errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        try {
            // $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = ($request->track_qty == 'Yes') ? $request->qty : null;
            $product->status = $request->status;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();

            // Save gallery images
            

            \Log::info('Product updated successfully:', $product->toArray()); // Log successful save

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully',
                'redirect_url' => route('admin.products.index')
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred:', ['error' => $e->getMessage()]); // Log exception message
            return response()->json([
                'status' => false,
                'message' => 'Failed to save product. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }        
    }

    public function destroy($id, Request $request){

        $product = Product::find($id);
        if(empty($product)){
                        $request->session()->flash('error', 'Product not found');
            return  response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $productImages = ProductImage::where('product_id',$id)->get();

        if(!empty($productImages)){
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/products/large/'.$productImage->image));
                File::delete(public_path('uploads/products/small/'.$productImage->image));
                
                }
                ProductImage::where('product_id',$id)->delete();
            }
            $product->delete();

           
            $request->session()->flash('success', 'Product deleted successfully');
                return response()->json([
                   'status' => true,
                   'message' => 'Product deleted successfully',
                   'redirect_url' => route('admin.products.index')
                ]);
        }

        public function getProducts(Request $request){

            $tempProduct = [];
            if($request->term != null){
                $products = Product::where('title','like','%'.$request->term.'%')->get();

                if($products != null){
                    foreach($products as $product){
                        $tempProduct[] = [
                            'id' => $product->id,
                            'text' => $product->title
                        ];
                    }
                }
            }
            return response()->json([
                'tags' => $tempProduct,
                'status' => true
            ]);
        }
    }
