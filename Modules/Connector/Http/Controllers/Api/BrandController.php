<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Brands;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

/**
 * @group Brand management
 * @authenticated
 *
 * APIs for managing brands
 */
class BrandController extends ApiController
{
     /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * List brands
     *
     * @response {
            "data": [
                {
                    "id": 1,
                    "business_id": 1,
                    "name": "Levis",
                    "description": null,
                    "created_by": 1,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:19:47",
                    "updated_at": "2018-01-03 21:19:47"
                },
                {
                    "id": 2,
                    "business_id": 1,
                    "name": "Espirit",
                    "description": null,
                    "created_by": 1,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:19:58",
                    "updated_at": "2018-01-03 21:19:58"
                }
            ]
        }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $brands = Brands::where('business_id', $business_id)
                        ->get();

        return CommonResource::collection($brands);
    }

    /**
     * Get the specified brand
     *
     * @urlParam brand required comma separated ids of the brands Example: 1
     * @response {
            "data": [
                {
                    "id": 1,
                    "business_id": 1,
                    "name": "Levis",
                    "description": null,
                    "created_by": 1,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:19:47",
                    "updated_at": "2018-01-03 21:19:47"
                }
            ]
        }
     */
    public function show($brand_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $brand_ids = explode(',', $brand_ids);

        $brands = Brands::where('business_id', $business_id)
                        ->whereIn('id', $brand_ids)
                        ->get();

        return CommonResource::collection($brands);
    }

    public function store(Request $request)
    {   
        
        if (! auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            $user = Auth::user();
            $input = $request->only(['name', 'description']);
            $business_id = $user->business_id;
            $input['business_id'] = $business_id;
            $input['created_by'] = $user->id;

            if ($this->moduleUtil->isModuleInstalled('Repair')) {
                $input['use_for_repair'] = ! empty($request->input('use_for_repair')) ? 1 : 0;
            }

            $brand = Brands::create($input);
            $output = ['success' => true,
                'data' => $brand,
                'msg' => __('brand.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        
            try {

                $input = $request->only(['name', 'description']);

                $business_id = Auth::user()->business_id;


                $brand = Brands::where('business_id', $business_id)->findOrFail($id);

                $brand->name = $input['name'];
                $brand->description = $input['description'];

                if ($this->moduleUtil->isModuleInstalled('Repair')) {
                    $brand->use_for_repair = ! empty($request->input('use_for_repair')) ? 1 : 0;
                }

                $brand->save();

                $output = ['success' => true,
                    'msg' => __('brand.updated_success'),
                    'data' => $brand
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    
        public function delete($id)
    {
        if (! auth()->user()->can('brand.delete')) {
            abort(403, 'Unauthorized action.');
        }
            try {

                $business_id = request()->user()->business_id;
                
                $brand = Brands::where('business_id', $business_id)->findOrFail($id);
                $brand->delete();

                $output = ['success' => true,
                    'msg' => __('brand.deleted_success'),
                    'data' => $brand
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        
        }


}
