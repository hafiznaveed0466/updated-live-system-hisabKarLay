<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\SellingPriceGroup;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Connector\Transformers\CommonResource;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
       
        try {
            $business_id = Auth::user()->business_id;

            $roles = Role::where('business_id', $business_id);
            
            $filters = request()->only(['per_page']);

            $perpage =  !empty($filters['per_page'])  ? $filters['per_page'] :  10 ;
            if($perpage == -1){
                $role = $roles->get();
            }else{
                $role = $roles->paginate($perpage);
            }
            // return CommonResource::collection($roles->paginate($perpage));
            // return CommonResource::collection($roles);

            
            DB::commit();
            return $data = [
                'success' => true,
                'msg' => 'Get Role Succesfully',
                'data' => $role,
            ];
            // return BusinessLocationResource::collection($business_locations);
            
        } catch (\Exception $e){
                 DB::rollBack();
                 \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }

    
            
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function GetAllPermissions()
    {
        if (!auth()->user()->can('roles.create')) {
            abort(403, 'Unauthorized action.');
        }


        $permissions = Permission::all();

        // $business_id = Auth::user()->business_id;

        // $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
        //                             ->active()
        //                             ->get();

        // $module_permissions = $this->moduleUtil->getModuleData('user_permissions');

        // dd($module_permissions);

        // $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

       return CommonResource::collection($permissions);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $role_name = request()->name;
            $permissions = $request->permissions;
            $business_id = Auth::user()->business_id;


            
            $count = Role::where('name', $role_name . '#' . $business_id)
                        ->where('business_id', $business_id)
                        ->count();

            if ($count == 0) {
                $is_service_staff = 0;
                if ($request->input('is_service_staff') == 1) {
                    $is_service_staff = 1;
                }

                $role = Role::create([
                            'name' => $role_name . '#' . $business_id ,
                            'business_id' => $business_id,
                            'is_service_staff' => $is_service_staff,
                            'guard_name' => 'web'
                        ]);

                //Include selling price group permissions
                $spg_permissions = $request->input('radio_option');

                
                if (!empty($spg_permissions)) {
                    foreach ($spg_permissions as $spg_permission) {
                        $permissions[] = $spg_permission;
                    }
                }

                $radio_options = $request->input('radio_option');
                if (!empty($radio_options)) {
                    foreach ($radio_options as $key => $value) {
                        $permissions[] = $value;
                    }
                }

                $this->__createPermissionIfNotExists($permissions);

                if (!empty($permissions)) {
                    $role->syncPermissions($permissions);
                }

                $output = ['success' => 1,
                            'msg' => __("user.role_added")
                        ];
            } else {
                $output = ['success' => 0,
                            'msg' => __("user.role_already_exists")
                        ];
            }

            return response()->json([
                'status' => 'success',
                'output' => $output,
                'permissions' => $permissions,
            ]);
            
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
           return $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
        try {
            $role_name = $request->input('name');
            $permissions = $request->input('permissions');
            $business_id = Auth::user()->business_id;

            $count = Role::where('name', $role_name . '#' . $business_id)
                        ->where('id', '!=', $id)
                        ->where('business_id', $business_id)
                        ->count();
            if ($count == 0) {
                $role = Role::findOrFail($id);

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {
                    if ($role->name == 'Cashier#' . $business_id) {
                        $role->is_default = 0;
                    }

                    $is_service_staff = 0;
                    if ($request->input('is_service_staff') == 1) {
                        $is_service_staff = 1;
                    }
                    $role->is_service_staff = $is_service_staff;
                    $role->name = $role_name . '#' . $business_id;
                    $role->save();

                    //Include selling price group permissions
                    $spg_permissions = $request->input('spg_permissions');
                    if (!empty($spg_permissions)) {
                        foreach ($spg_permissions as $spg_permission) {
                            $permissions[] = $spg_permission;
                        }
                    }

                    $radio_options = $request->input('radio_option');
                    if (!empty($radio_options)) {
                        foreach ($radio_options as $key => $value) {
                            $permissions[] = $value;
                        }
                    }

                    $this->__createPermissionIfNotExists($permissions);

                    if (!empty($permissions)) {
                        $role->syncPermissions($permissions);
                    }

                    $output = ['success' => 1,
                            'msg' => __("user.role_updated")
                        ];
                } else {
                    $output = ['success' => 0,
                            'msg' => __("user.role_is_default")
                        ];
                }
            } else {
                $output = ['success' => 0,
                            'msg' => __("user.role_already_exists")
                        ];
            }

            return response()->json([
                'status' => 'success',
                'megs' => 'Role Update Successfully',
                'output' => $output,
                'permissions' => $permissions,
            ]);

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];

                        return $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
            try {

                $business_id = Auth::user()->business_id;

                $role = Role::where('business_id', $business_id)->find($id);

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {

                    $role->delete();

                    $output = ['success' => true,
                            'msg' => __("user.role_deleted")
                            ];
                } else {
                    $output = ['success' => 0,
                            'msg' => __("user.role_is_default")
                        ];
                }

                return response()->json([
                    'output' => $output,
                    
                ]);

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];

                        return response()->json([
                           
                            'output' => $output,
                          
                        ]);

            }

            
        
    }

    /**
     * Creates new permission if doesn't exist
     *
     * @param  array  $permissions
     * @return void
     */
    private function __createPermissionIfNotExists($permissions)
    {
        $exising_permissions = Permission::whereIn('name', $permissions)
                                    ->pluck('name')
                                    ->toArray();

        $non_existing_permissions = array_diff($permissions, $exising_permissions);

        if (!empty($non_existing_permissions)) {
            foreach ($non_existing_permissions as $new_permission) {
                $time_stamp = \Carbon::now()->toDateTimeString();
                Permission::create([
                    'name' => $new_permission,
                    'guard_name' => 'web'
                ]);
            }
        }
    }

    // Get Specfic permisions for assign to role Details API

    public function AssignPermissions($id){

        
        try {
            $business_id = Auth::user()->business_id;
            $role = DB::table('roles')
                            ->leftjoin('role_has_permissions' , 'role_has_permissions.role_id' , 'roles.id')
                            ->leftjoin('permissions' ,  'permissions.id' , 'role_has_permissions.permission_id')
                            ->where('roles.business_id' , $business_id)
                            ->where('roles.id' , $id)
                            ->select('roles.name as role_name' ,'permissions.*')
                            ->get();
            
            DB::commit();
            return $data = [
                'success' => true,
                'msg' => 'Get Permissions Succesfully',
                'data' => $role,
            ];
            // return BusinessLocationResource::collection($business_locations);
            
        } catch (\Exception $e){
                 DB::rollBack();
                 \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }

    }
}