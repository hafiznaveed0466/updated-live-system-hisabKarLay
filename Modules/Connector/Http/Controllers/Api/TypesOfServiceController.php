<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\TypesOfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\TypesOfServiceResource;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @group Types of service management
 * @authenticated
 *
 * APIs for managing Types of services
 */
class TypesOfServiceController extends ApiController
{

    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param TaxUtil $taxUtil
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * List types of service
     *
     * @response {
        "data": [
            {
                "id": 1,
                "name": "Home Delivery",
                "description": null,
                "business_id": 1,
                "location_price_group": {
                    "1": "0"
                },
                "packing_charge": "10.0000",
                "packing_charge_type": "fixed",
                "enable_custom_fields": 0,
                "created_at": "2020-06-04 22:41:13",
                "updated_at": "2020-06-04 22:41:13"
            }
        ]
    }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $types_of_service = TypesOfService::where('business_id', $business_id)
                                        ->get();

        return TypesOfServiceResource::collection($types_of_service);
    }

    /**
     * Get the specified types of service
     *
     * @urlParam types_of_service required comma separated ids of required types of services Example: 1
     *
     * @response {
        "data": [
            {
                "id": 1,
                "name": "Home Delivery",
                "description": null,
                "business_id": 1,
                "location_price_group": {
                    "1": "0"
                },
                "packing_charge": "10.0000",
                "packing_charge_type": "fixed",
                "enable_custom_fields": 0,
                "created_at": "2020-06-04 22:41:13",
                "updated_at": "2020-06-04 22:41:13"
            }
        ]
    }
     */
    public function show($types_of_service_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $types_of_service_ids = explode(',', $types_of_service_ids);

        $types_of_service = TypesOfService::where('business_id', $business_id)
                        ->whereIn('id', $types_of_service_ids)
                        ->get();

        return TypesOfServiceResource::collection($types_of_service);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $input = $request->only([
            'name', 'description',
            'location_price_group', 'packing_charge_type',
            'packing_charge'
        ]);

        $input['business_id'] = $business_id;
        $input['packing_charge'] = !empty($input['packing_charge']) ? $this->commonUtil->num_uf($input['packing_charge']) : 0;
        $input['enable_custom_fields'] = !empty($request->input('enable_custom_fields')) ? 1 : 0;

        TypesOfService::create($input);

        $output = [
            'success' => true,
            'msg' => __("lang_v1.added_success")
        ];
        return $output;
    }
    public function update(Request $request, $id)
    {
        // if (!auth()->user()->can('access_types_of_service')) {
        //      abort(403, 'Unauthorized action.');
        // }

        try {
       
        $user = Auth::user();

        $business_id = $user->business_id;
        $input = $request->only([
            'name', 'description',
            'location_price_group', 'packing_charge_type',
            'packing_charge'
        ]);

        $business_id = $user->business_id;
        $input['packing_charge'] = !empty($input['packing_charge']) ? $this->commonUtil->num_uf($input['packing_charge']) : 0;
        $input['enable_custom_fields'] = !empty($request->input('enable_custom_fields')) ? 1 : 0;
        $input['location_price_group'] = !empty($input['location_price_group']) ? json_encode($input['location_price_group']) : null;

        TypesOfService::where('business_id', $business_id)
            ->where('id', $id)
            ->update($input);

        $output = [
            'success' => true,
            'msg' => __("lang_v1.updated_success")
        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

        $output = ['success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
        }    

        return $output;
    }
    public function delete($id)
    {
        // if (!auth()->user()->can('access_types_of_service')) {
        //      abort(403, 'Unauthorized action.');
        // }

            try {
        $user = Auth::user();

        $business_id = $user->business_id;
        TypesOfService::where('business_id', $business_id)
            ->where('id', $id)
            ->delete();

        $output = [
            'success' => true,
            'msg' => __("lang_v1.deleted_success")
        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

        $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
        }

        return $output;
       
    }

}
