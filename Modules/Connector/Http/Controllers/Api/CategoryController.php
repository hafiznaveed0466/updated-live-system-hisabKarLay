<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @group Taxonomy management
 * @authenticated
 *
 * APIs for managing taxonomies
 */
class CategoryController extends ApiController
{
    /**
     * List taxonomy
     *
     * @queryParam type Type of taxonomy (product, device, hrm_department)
     *
     * @response {
            "data": [
                {
                    "id": 1,
                    "name": "Men's",
                    "business_id": 1,
                    "short_code": null,
                    "parent_id": 0,
                    "created_by": 1,
                    "category_type": "product",
                    "description": null,
                    "slug": null,
                    "woocommerce_cat_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:06:34",
                    "updated_at": "2018-01-03 21:06:34",
                    "sub_categories": [
                        {
                            "id": 4,
                            "name": "Jeans",
                            "business_id": 1,
                            "short_code": null,
                            "parent_id": 1,
                            "created_by": 1,
                            "category_type": "product",
                            "description": null,
                            "slug": null,
                            "woocommerce_cat_id": null,
                            "deleted_at": null,
                            "created_at": "2018-01-03 21:07:34",
                            "updated_at": "2018-01-03 21:07:34"
                        },
                        {
                            "id": 5,
                            "name": "Shirts",
                            "business_id": 1,
                            "short_code": null,
                            "parent_id": 1,
                            "created_by": 1,
                            "category_type": "product",
                            "description": null,
                            "slug": null,
                            "woocommerce_cat_id": null,
                            "deleted_at": null,
                            "created_at": "2018-01-03 21:08:18",
                            "updated_at": "2018-01-03 21:08:18"
                        }
                    ]
                },
                {
                    "id": 21,
                    "name": "Food & Grocery",
                    "business_id": 1,
                    "short_code": null,
                    "parent_id": 0,
                    "created_by": 1,
                    "category_type": "product",
                    "description": null,
                    "slug": null,
                    "woocommerce_cat_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-06 05:31:35",
                    "updated_at": "2018-01-06 05:31:35",
                    "sub_categories": []
                }
            ]
        }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $query = Category::where('business_id', $business_id)
                        ->onlyParent()
                        ->with('sub_categories');

        if (! empty(request()->input('type'))) {
            $query->where('category_type', request()->input('type'));
        }

        $categories = $query->get();

        return CommonResource::collection($categories);
    }

    /**
     * Get the specified taxonomy
     *
     * @urlParam taxonomy required comma separated ids of product categories Example: 1

     * @response {
            "data": [
                {
                    "id": 1,
                    "name": "Men's",
                    "business_id": 1,
                    "short_code": null,
                    "parent_id": 0,
                    "created_by": 1,
                    "category_type": "product",
                    "description": null,
                    "slug": null,
                    "woocommerce_cat_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:06:34",
                    "updated_at": "2018-01-03 21:06:34",
                    "sub_categories": [
                        {
                            "id": 4,
                            "name": "Jeans",
                            "business_id": 1,
                            "short_code": null,
                            "parent_id": 1,
                            "created_by": 1,
                            "category_type": "product",
                            "description": null,
                            "slug": null,
                            "woocommerce_cat_id": null,
                            "deleted_at": null,
                            "created_at": "2018-01-03 21:07:34",
                            "updated_at": "2018-01-03 21:07:34"
                        },
                        {
                            "id": 5,
                            "name": "Shirts",
                            "business_id": 1,
                            "short_code": null,
                            "parent_id": 1,
                            "created_by": 1,
                            "category_type": "product",
                            "description": null,
                            "slug": null,
                            "woocommerce_cat_id": null,
                            "deleted_at": null,
                            "created_at": "2018-01-03 21:08:18",
                            "updated_at": "2018-01-03 21:08:18"
                        }
                    ]
                }
            ]
        }
     */
    public function show($category_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $category_ids = explode(',', $category_ids);

        $categories = Category::where('business_id', $business_id)
                        ->whereIn('id', $category_ids)
                        ->with('sub_categories')
                        ->get();

        return CommonResource::collection($categories);
    }

    public function store(Request $request)
    {

        $user = Auth::user();

        $business_id = $user->business_id;

        $input = $request->only(['name', 'short_code', 'category_type', 'description', 'image','location_id']);

        if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
            $input['parent_id'] = $request->input('parent_id');
        } else {
            $input['parent_id'] = 0;
        }


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move("uploads/img", $name);
            $input['image'] = $name;
        }


        $input['business_id'] = $user->business_id;

        $input['created_by'] = $user->id;

        $category = Category::create($input);

        // $location_id = $request->input('location_id');


        $output = [
            'success' => true,
            'data' => $category, 
            'msg' => __("category.added_success")
        ];
        // dd($output);
        return $output;
    }

    public function update(Request $request, $id)
    {

        // if (request()->ajax()) {
        // try {

        $user = Auth::user();
        $business_id = $user->business_id;
        $input = $request->only(['name', 'description', 'short_code','location_id' ,'image']);
        $category = Category::where('business_id', $business_id)->findOrFail($id);
       

     

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move("uploads/img", $name);
            $input['image'] = $name; // Update the image name in the $input array
        }





        if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
            $category->parent_id = $request->input('parent_id');
        } else {
            $category->parent_id = 0;
        }

        $category->update($input); 

        $output = [
            'success' => true,
            'data' => $category,
            'msg' => __("category.updated_success")
        ];
        // } catch (\Exception $e) {
        //     \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

        // $output = [
        //     'success' => false,
        //     'msg' => __("messages.something_went_wrong")
        // ];
        // }

        return $output;
        // }
    }

    public function destroy($id)
    {

        // if (request()->ajax()) {
        //     try {
           
            $user = Auth::user();
            $business_id = $user->business_id;
            
            $category = Category::where('business_id', $business_id)->findOrFail($id);
            $category->delete();

                $output = [
                    'success' => true,
                    'msg' => __("category.deleted_success")
                ];

            // } catch (\Exception $e) {
            //     \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                // $output = [
                //     'success' => false,
                //     'msg' => __("messages.something_went_wrong")
                // ];
            // }

            return $output;
        }
    // }

}
