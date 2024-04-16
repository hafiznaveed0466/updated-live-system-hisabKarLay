<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\BusinessLocation;
use App\Contact;
use App\System;
use DB;
use Spatie\Activitylog\Models\Activity;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\Utils\Util;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\SellingPriceGroup;
use App\Utils\ModuleUtil;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class ManageUserController extends ApiController
{
    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */

    protected $commonUtil;
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil , Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd("test user management");

        // if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = Auth::user()->business_id;
        $user_id =  Auth::user()->id;

    
            $users = User::where('business_id', $business_id)
                        ->user()
                        ->where('is_cmmsn_agnt', 0)
                        ->select(['id', 'username',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email', 'allow_login'])->get();

                            return CommonResource::collection($users);
           
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
        
    //     if (!auth()->user()->can('user.create')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');

    //     //Check if subscribed or not, then check for users quota
    //     if (!$this->moduleUtil->isSubscribed($business_id)) {
    //         return $this->moduleUtil->expiredResponse();
    //     } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
    //         return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action('ManageUserController@index'));
    //     }

    //     $roles  = $this->getRolesArray($business_id);
    //     $username_ext = $this->moduleUtil->getUsernameExtension();
    //     $locations = BusinessLocation::where('business_id', $business_id)
    //                                 ->Active()
    //                                 ->get();

    //     //Get user form part from modules
    //     $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);

    //     return view('manage_user.create')
    //             ->with(compact('roles', 'username_ext', 'locations', 'form_partials'));
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // if (!auth()->user()->can('user.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }
            
            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

            $user = $this->moduleUtil->createUser($request);

            $output = ['success' => 1,
                        'msg' => __("user.user_added")
                    ];

                    return response()->json([
                        'output' => $output,
                        'user' => $user
                    ]);
                
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                        'msg' => __("messages.something_went_wrong")
                    ];

                    return response()->json([
                        'output' => $output,
                    ]);
        }

       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     if (!auth()->user()->can('user.view')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');

    //     $user = User::where('business_id', $business_id)
    //                 ->with(['contactAccess'])
    //                 ->find($id);

    //     //Get user view part from modules
    //     $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

    //     $users = User::forDropdown($business_id, false);

    //     $activities = Activity::forSubject($user)
    //        ->with(['causer', 'subject'])
    //        ->latest()
    //        ->get();

    //     return view('manage_user.show')->with(compact('user', 'view_partials', 'users', 'activities'));
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     if (!auth()->user()->can('user.update')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $user = User::where('business_id', $business_id)
    //                 ->with(['contactAccess'])
    //                 ->findOrFail($id);

    //     $roles = $this->getRolesArray($business_id);

    //     $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();

    //     if ($user->status == 'active') {
    //         $is_checked_checkbox = true;
    //     } else {
    //         $is_checked_checkbox = false;
    //     }

    //     $locations = BusinessLocation::where('business_id', $business_id)
    //                                 ->get();

    //     $permitted_locations = $user->permitted_locations();
    //     $username_ext = $this->moduleUtil->getUsernameExtension();

    //     //Get user form part from modules
    //     $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);
        
    //     return view('manage_user.edit')
    //             ->with(compact('roles', 'user', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials', 'username_ext'));
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        // if (!auth()->user()->can('user.update')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $user_id =  Auth::user()->id;
        try {
            $user_data = $request->only(['surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1',
                'social_media_2', 'permanent_address', 'current_address',
                'guardian_name', 'custom_field_1', 'custom_field_2',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'max_sales_discount_percent', 'family_number', 'alt_number']);

            $user_data['status'] = !empty($request->input('is_active')) ? 'active' : 'inactive';
            $business_id = Auth::user()->business_id;


            if (!isset($user_data['selected_contacts'])) {
                $user_data['selected_contacts'] = 0;
            }

            if (empty($request->input('allow_login'))) {
                $user_data['username'] = null;
                $user_data['password'] = null;
                $user_data['allow_login'] = 0;
            } else {
                $user_data['allow_login'] = 1;
            }

            if (!empty($request->input('password'))) {
                $user_data['password'] = $user_data['allow_login'] == 1 ? Hash::make($request->input('password')) : null;
            }

            //Sales commission percentage
            $user_data['cmmsn_percent'] = !empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;

            $user_data['max_sales_discount_percent'] = !is_null($user_data['max_sales_discount_percent']) ? $this->moduleUtil->num_uf($user_data['max_sales_discount_percent']) : null;

            if (!empty($request->input('dob'))) {
                $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            if (!empty($request->input('bank_details'))) {
                $user_data['bank_details'] = json_encode($request->input('bank_details'));
            }

            DB::beginTransaction();

            if ($user_data['allow_login'] && $request->has('username')) {
                $user_data['username'] = $request->input('username');
                $ref_count = $this->moduleUtil->setAndGetReference('username');
                if (blank($user_data['username'])) {
                    $user_data['username'] = $this->moduleUtil->generateReferenceNumber('username', $ref_count);
                }

                $username_ext = $this->moduleUtil->getUsernameExtension();
                if (!empty($username_ext)) {
                    $user_data['username'] .= $username_ext;
                }
            }

            $user = User::where('business_id', $business_id)
                          ->findOrFail($id);

            $user->update($user_data);
            $role_id = $request->input('role');
            $user_role = $user->roles->first();
            $previous_role = !empty($user_role->id) ? $user_role->id : 0;
            if ($previous_role != $role_id) {
                $is_admin = $this->moduleUtil->is_admin($user);
                $all_admins = $this->getAdmins();
                //If only one admin then can not change role
                if ($is_admin && count($all_admins) <= 1) {
                    throw new \Exception(__('lang_v1.cannot_change_role'));
                }
                if (!empty($previous_role)) {
                    $user->removeRole($user_role->name);
                }
                
                $role = Role::findOrFail($role_id);
                $user->assignRole($role->name);
            }

            //Grant Location permissions
            $this->moduleUtil->giveLocationPermissions($user, $request);

            //Assign selected contacts
            if ($user_data['selected_contacts'] == 1) {
                $contact_ids = $request->get('selected_contact_ids');
            } else {
                $contact_ids = [];
            }
            $user->contactAccess()->sync($contact_ids);

            //Update module fields for user
            $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved', 'model_instance' => $user]);

            $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);

            $output = ['success' => 1,
                        'msg' => __("user.user_update_success")
                    ];

            DB::commit();

            return response()->json([
                'data' => $output
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];

               return response()->json([
                'output' => $output
               ]);      
        }

        
    }

    private function getAdmins()
    {
       $business_id = Auth::user()->business_id;

        $admins = User::role('Admin#' . $business_id)->get();

        return $admins;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (!auth()->user()->can('user.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }

      
            try {
                $business_id = Auth::user()->business_id;

                $user = User::where('business_id', $business_id)
                    ->findOrFail($id);

                $this->moduleUtil->activityLog($user, 'deleted', null, ['name' => $user->user_full_name, 'id' => $user->id]);

                $user->delete();

                $output = ['success' => true,
                                'msg' => __("user.user_delete_success")
                                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return response()->json([
                "output"  => $output
            ]);
        
    }

    /**
     * Retrives roles array (Hides admin role from non admin users)
     *
     * @param  int  $business_id
     * @return array $roles
     */
    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }
}
