<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductSubCategoryController extends Controller
{
    public function index(Request $request){
        if(!empty($request->category_id)){
            $subCategories = SubCategory::where('category_id', $request->category_id)
            ->orderBy('name','ASC')->get();

            return response()->json([
                'status' => true,
                'sub_categories' => $subCategories
            ]);
        }else{
            return response()->json([
                'status' => true,
                'sub_categories' => [
                    // 'error' => 'No category id provided'
                ]
                ]);
        }
    }
}
