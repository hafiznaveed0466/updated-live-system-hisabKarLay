<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\TaxRate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\GroupSubTax;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\TaxUtil;
/**
 * @group Tax management
 * @authenticated
 *
 * APIs for managing taxes
 */
class TaxController extends ApiController
{
    protected $taxUtil;

    /**
     * Constructor
     *
     * @param TaxUtil $taxUtil
     * @return void
     */
    public function __construct(TaxUtil $taxUtil)
    {
        $this->taxUtil = $taxUtil;
    }
    /**
     * List taxes
     *
     * @response {
    "data": [
                {
                    "id": 1,
                    "business_id": 1,
                    "name": "VAT@10%",
                    "amount": 10,
                    "is_tax_group": 0,
                    "created_by": 1,
                    "woocommerce_tax_rate_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:40:07",
                    "updated_at": "2018-01-04 02:40:07"
                },
                {
                    "id": 2,
                    "business_id": 1,
                    "name": "CGST@10%",
                    "amount": 10,
                    "is_tax_group": 0,
                    "created_by": 1,
                    "woocommerce_tax_rate_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:40:55",
                    "updated_at": "2018-01-04 02:40:55"
                },
                {
                    "id": 3,
                    "business_id": 1,
                    "name": "SGST@8%",
                    "amount": 8,
                    "is_tax_group": 0,
                    "created_by": 1,
                    "woocommerce_tax_rate_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:41:13",
                    "updated_at": "2018-01-04 02:41:13"
                },
                {
                    "id": 4,
                    "business_id": 1,
                    "name": "GST@18%",
                    "amount": 18,
                    "is_tax_group": 1,
                    "created_by": 1,
                    "woocommerce_tax_rate_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:42:19",
                    "updated_at": "2018-01-04 02:42:19"
                }
            ]
        }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $taxes = TaxRate::where('business_id', $business_id)
                        ->get();

        return CommonResource::collection($taxes);
    }

    /**
     * Get the specified tax
     *
     * @urlParam tax required comma separated ids of required taxes Example: 1
     *
     * @response {
            "data": [
                {
                    "id": 1,
                    "business_id": 1,
                    "name": "VAT@10%",
                    "amount": 10,
                    "is_tax_group": 0,
                    "created_by": 1,
                    "woocommerce_tax_rate_id": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:40:07",
                    "updated_at": "2018-01-04 02:40:07"
                }
            ]
        }
     */
    public function show($tax_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $tax_ids = explode(',', $tax_ids);

        $taxes = TaxRate::where('business_id', $business_id)
                        ->whereIn('id', $tax_ids)
                        ->get();

        return CommonResource::collection($taxes);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        // try {
            $input = $request->only(['name', 'amount']);
            $input['business_id'] = $user->business_id;
            $input['created_by'] =$user->id;
            $input['amount'] = $this->taxUtil->num_uf($input['amount']);
            $input['for_tax_group'] = !empty($request->for_tax_group) ? 1 : 0;

            $tax_rate = TaxRate::create($input);
            $output = ['success' => true,
                            'data' => $tax_rate,
                            'msg' => __("tax_rate.added_success")
                        ];
        // } catch (\Exception $e) {
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //     $output = ['success' => false,
        //                     'msg' => __("messages.something_went_wrong")
        //                 ];
        // }

        return $output;
    }

    public function update(Request $request, $id)
    {
        // if (!auth()->user()->can('tax_rate.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // if (request()->ajax()) {
        //     try {
            $user = Auth::user();

            $business_id = $user->business_id;
                $input = $request->only(['name', 'amount']);
                $input['business_id'] = $user->business_id;

                $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                $tax_rate->name = $input['name'];
                $tax_rate->amount = $this->taxUtil->num_uf($input['amount']);
                $tax_rate->for_tax_group = !empty($request->for_tax_group) ? 1 : 0;
                $tax_rate->save();

                //update group tax amount
                $group_taxes = GroupSubTax::where('tax_id', $id)
                                            ->get();
                              
                foreach ($group_taxes as $group_tax) {
                    $this->taxUtil->updateGroupTaxAmount($group_tax->group_tax_id);
                }

                $output = ['success' => true,
                            'msg' => __("tax_rate.updated_success")
                            ];
            // } catch (\Exception $e) {
            //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            //     $output = ['success' => false,
            //                 'msg' => __("messages.something_went_wrong")
            //             ];
            // }

            return $output;
        }
        public function destroy($id)
        {
            
    
            // if (request()->ajax()) {
                // try {
                    //update group tax amount
                    $group_taxes = GroupSubTax::where('tax_id', $id)
                                                ->get();
                    if ($group_taxes->isEmpty()) {
                        $business_id = request()->user()->business_id;
    
                        $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                        $tax_rate->delete();
    
                        $output = ['success' => true,
                                    'msg' => __("tax_rate.deleted_success")
                                    ];
                    } else {
                        $output = ['success' => false,
                                    'msg' => __("tax_rate.can_not_be_deleted")
                                    ];
                    }
                // } catch (\Exception $e) {
                //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                
                //     $output = ['success' => false,
                //                 'msg' => __("messages.something_went_wrong")
                //             ];
                // }
    
                return $output;
            }
            
}
