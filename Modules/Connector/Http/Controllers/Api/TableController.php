<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Restaurant\ResTable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;
use Illuminate\Support\Facades\DB as FacadesDB; 


/**
 * @group Table management
 * @authenticated
 *
 * APIs for managing tables
 */
class TableController extends ApiController
{
    /**
     * List tables
     *
     * @queryParam location_id  int id of the location Example: 1
     *
     * @response {
        "data": [
            {
                "id": 5,
                "business_id": 1,
                "location_id": 1,
                "name": "Table 1",
                "description": null,
                "created_by": 9,
                "deleted_at": null,
                "created_at": "2020-06-04 22:36:37",
                "updated_at": "2020-06-04 22:36:37"
            }
        ]
    }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $query = ResTable::where('business_id', $business_id);

        if (! empty(request()->location_id)) {
            $query->where('location_id', request()->location_id);
        }
        $tables = $query->get();

        return CommonResource::collection($tables);
    }

    /**
     * Show the specified table
     *
     * @urlParam table required comma separated ids of required tables Example: 5
     *
     * @response {
        "data": [
            {
                "id": 5,
                "business_id": 1,
                "location_id": 1,
                "name": "Table 1",
                "description": null,
                "created_by": 9,
                "deleted_at": null,
                "created_at": "2020-06-04 22:36:37",
                "updated_at": "2020-06-04 22:36:37"
            }
        ]
    }
     */
    public function show($table_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $table_ids = explode(',', $table_ids);

        $tables = ResTable::where('business_id', $business_id)
                        ->whereIn('id', $table_ids)
                        ->get();

        return CommonResource::collection($tables);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $input = $request->only(['name', 'description', 'location_id']);
            $business_id = $user->business_id;
            $input['business_id'] = $business_id;
            $input['created_by'] = $user->id;
            $table = ResTable::create($input);

            $output = [
                'success' => true,
                'msg' => __("table added success"),
                'data' => $table, 
            ];

            return $output;
        } catch (Exception $e) {
            return [
                'success'   => false,
                'message'   => $e->getMessage(),
            ];
        }
       
    }

    public function update(Request $request, $id)
    {
        try {

            $business_id = auth()->user()->business_id;

            $input = $request->except('_token');

            $sql = ResTable::where('id', $id)->where('business_id', $business_id)->update($input);

            if ($sql != 0) {
                return [
                    'success'   => true,
                    'message'   => "ResTable  Update Successfully",
                ];
            } else {
                return [
                    'success'   => false,
                    'message'   => "id Not Exist",
                ];
            }
        } catch (Exception $e) {
            return [
                'success'   => false,
                'message'   => $e->getMessage(),
            ];
        }
    }
    public function delete($id)
    {
        try {
           $sql =  FacadesDB::table('res_tables')->delete($id);
            if ($sql != 0) {
                return [
                    'success'   => true,
                    'message'   => "Table Deleted Successfully",
                ];
            } else {
                return [
                    'success'   => false,
                    'message'   => "id Not Exist",
                ];
            }
        } catch (Exception $e) {
            return [
                'success'   => false,
                'message'   => $e->getMessage(),
            ];
        }
    }

}
