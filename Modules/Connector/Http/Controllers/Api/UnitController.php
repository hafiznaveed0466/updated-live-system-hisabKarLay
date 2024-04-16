<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Unit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\TaxRate;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\TaxUtil;
use App\Utils\Util;

/**
 * @group Unit management
 * @authenticated
 *
 * APIs for managing units
 */
class UnitController extends ApiController
{
    protected $commonUtil;
     protected $taxUtil;
    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(Util $commonUtil , TaxUtil $taxUtil)
    {
        $this->commonUtil = $commonUtil;
         $this->taxUtil = $taxUtil;
    }
    
    /**
     * List units
     *
     * @response {
        "data": [
            {
                "id": 1,
                "business_id": 1,
                "actual_name": "Pieces",
                "short_name": "Pc(s)",
                "allow_decimal": 0,
                "base_unit_id": null,
                "base_unit_multiplier": null,
                "created_by": 1,
                "deleted_at": null,
                "created_at": "2018-01-03 15:15:20",
                "updated_at": "2018-01-03 15:15:20",
                "base_unit": null
            },
            {
                "id": 2,
                "business_id": 1,
                "actual_name": "Packets",
                "short_name": "packets",
                "allow_decimal": 0,
                "base_unit_id": null,
                "base_unit_multiplier": null,
                "created_by": 1,
                "deleted_at": null,
                "created_at": "2018-01-06 01:07:01",
                "updated_at": "2018-01-06 01:08:36",
                "base_unit": null
            },
            {
                "id": 15,
                "business_id": 1,
                "actual_name": "Dozen",
                "short_name": "dz",
                "allow_decimal": 0,
                "base_unit_id": 1,
                "base_unit_multiplier": "12.0000",
                "created_by": 9,
                "deleted_at": null,
                "created_at": "2020-07-20 13:11:09",
                "updated_at": "2020-07-20 13:11:09",
                "base_unit": {
                    "id": 1,
                    "business_id": 1,
                    "actual_name": "Pieces",
                    "short_name": "Pc(s)",
                    "allow_decimal": 0,
                    "base_unit_id": null,
                    "base_unit_multiplier": null,
                    "created_by": 1,
                    "deleted_at": null,
                    "created_at": "2018-01-03 15:15:20",
                    "updated_at": "2018-01-03 15:15:20"
                }
            }
        ]
    }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $units = Unit::where('business_id', $business_id)
                    ->with(['base_unit'])
                    ->get();

        return CommonResource::collection($units);
    }

    /**
     * Get the specified unit
     *
     * @urlParam unit required comma separated ids of the units Example: 1
     * @response {
        "data": [
            {
                "id": 1,
                "business_id": 1,
                "actual_name": "Pieces",
                "short_name": "Pc(s)",
                "allow_decimal": 0,
                "base_unit_id": null,
                "base_unit_multiplier": null,
                "created_by": 1,
                "deleted_at": null,
                "created_at": "2018-01-03 15:15:20",
                "updated_at": "2018-01-03 15:15:20",
                "base_unit": null
            }
        ]
    }
     */
    public function show($unit_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $unit_ids = explode(',', $unit_ids);

        $units = Unit::where('business_id', $business_id)
                        ->whereIn('id', $unit_ids)
                        ->with(['base_unit'])
                        ->get();

        return CommonResource::collection($units);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $input = $request->only(['actual_name', 'short_name', 'allow_decimal']);
        $input['business_id'] =  $user->business_id;
        $input['created_by'] =  $user->id;

        if (empty($request->has('define_base_unit'))) {
            if (!empty($request->input('base_unit_id')) && !empty($request->input('base_unit_multiplier'))) {
                $base_unit_multiplier = $this->commonUtil->num_uf($request->input('base_unit_multiplier'));
                if ($base_unit_multiplier != 0) {
                    $input['base_unit_id'] = $request->input('base_unit_id');
                    $input['base_unit_multiplier'] = $base_unit_multiplier;
                }
            }
        }
        $unit = Unit::create($input);
        $output = [
            'success' => true,
            'data' => $unit,
            'msg' => __("unit.added_success")
        ];

        return $output;
    }
    public function update(Request $request, $id)
    {
       
        // if (!auth()->user()->can('unit.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // if (request()->ajax()) {
        //     try {
        // $input = $request->only(['actual_name', 'short_name', 'allow_decimal']);
        $user = Auth::user();

        $business_id = $user->business_id;

        $unit = Unit::where('business_id', $business_id)->findOrFail($id);
        $unit->actual_name = $request['actual_name'];
        $unit->short_name = $request['short_name'];
        $unit->allow_decimal = $request['allow_decimal'];

                $base_unit_multiplier =$request->input('base_unit_multiplier');
               
                    $unit->base_unit_id = $request->input('base_unit_id');
                    $unit->base_unit_multiplier = $base_unit_multiplier;
     
        $unit->save();

        $output = [
            'success' => true,
            'msg' => __("unit.updated_success")
        ];
        // } catch (\Exception $e) {
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

        //     $output = ['success' => false,
        //                 'msg' => __("messages.something_went_wrong")
        //             ];
        // }

        return $output;
        // }
    }
    public function delete($id)
    {
        if (!auth()->user()->can('unit.delete')) {
            abort(403, 'Unauthorized action.');
        }

        // if (request()->ajax()) {
        //     try {
                $business_id = request()->user()->business_id;

                $unit = Unit::where('business_id', $business_id)->findOrFail($id);

                //check if any product associated with the unit
                $exists = Product::where('unit_id', $unit->id)
                                ->exists();
                if (!$exists) {
                    $unit->delete();
                    $output = ['success' => true,
                            'msg' => __("unit.deleted_success")
                            ];
                } else {
                    $output = ['success' => false,
                            'msg' => __("lang_v1.unit_cannot_be_deleted")
                            ];
                }
            // } catch (\Exception $e) {
            //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            //     $output = ['success' => false,
            //                 'msg' => '__("messages.something_went_wrong")'
            //             ];
            // }

            return $output;
        // }
    }

}
