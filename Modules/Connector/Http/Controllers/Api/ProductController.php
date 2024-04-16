<?php

namespace Modules\Connector\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\ProductResource;
use Modules\Connector\Transformers\VariationResource;
use Modules\Connector\Transformers\CommonResource;
use App\Product;
use App\ProductVariation;
use App\Variation;
use App\SellingPriceGroup;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\VariationTemplate;
use App\VariationValueTemplate;
use DB;
use App\Unit;
use Illuminate\Support\Facades\DB as FacadesDB;
// use Modules\Manufacturing\Entities\MfgIngredientGroup;
// use Modules\Manufacturing\Entities\MfgRecipe;
// use Modules\Manufacturing\Entities\MfgRecipeIngredient;
// use Modules\Manufacturing\Utils\ManufacturingUtil;

/**
 * @group Product management
 * @authenticated
 *
 * APIs for managing products
 */
class ProductController extends ApiController
{


    protected $moduleUtil;
    protected $mfgUtil;
    protected $businessUtil;
    protected $transactionUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, 
    // ManufacturingUtil $mfgUtil, 
    BusinessUtil $businessUtil, TransactionUtil $transactionUtil)
    {
        $this->moduleUtil = $moduleUtil;
        // $this->mfgUtil = $mfgUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * List products
     * @queryParam order_by Values: product_name or newest
     * @queryParam order_direction Values: asc or desc
     * @queryParam brand_id comma separated ids of one or multiple brands
     * @queryParam category_id comma separated ids of one or multiple category
     * @queryParam sub_category_id comma separated ids of one or multiple sub-category
     * @queryParam location_id Example: 1
     * @queryParam selling_price_group (1, 0) 
     * @queryParam send_lot_detail Send lot details in each variation location details(1, 0) 
     * @queryParam name Search term for product name
     * @queryParam sku Search term for product sku
     * @queryParam per_page Total records per page. default: 10, Set -1 for no pagination Example:10
     * @response {
        "data": [
            {
                "id": 1,
                "name": "Men's Reverse Fleece Crew",
                "business_id": 1,
                "type": "single",
                "sub_unit_ids": null,
                "enable_stock": 1,
                "alert_quantity": "5.0000",
                "sku": "AS0001",
                "barcode_type": "C128",
                "expiry_period": null,
                "expiry_period_type": null,
                "enable_sr_no": 0,
                "weight": null,
                "product_custom_field1": null,
                "product_custom_field2": null,
                "product_custom_field3": null,
                "product_custom_field4": null,
                "image": null,
                "woocommerce_media_id": null,
                "product_description": null,
                "created_by": 1,
                "warranty_id": null,
                "is_inactive": 0,
                "repair_model_id": null,
                "not_for_selling": 0,
                "ecom_shipping_class_id": null,
                "ecom_active_in_store": 1,
                "woocommerce_product_id": 356,
                "woocommerce_disable_sync": 0,
                "image_url": "http://local.pos.com/img/default.png",
                "product_variations": [
                    {
                        "id": 1,
                        "variation_template_id": null,
                        "name": "DUMMY",
                        "product_id": 1,
                        "is_dummy": 1,
                        "created_at": "2018-01-03 21:29:08",
                        "updated_at": "2018-01-03 21:29:08",
                        "variations": [
                            {
                                "id": 1,
                                "name": "DUMMY",
                                "product_id": 1,
                                "sub_sku": "AS0001",
                                "product_variation_id": 1,
                                "woocommerce_variation_id": null,
                                "variation_value_id": null,
                                "default_purchase_price": "130.0000",
                                "dpp_inc_tax": "143.0000",
                                "profit_percent": "0.0000",
                                "default_sell_price": "130.0000",
                                "sell_price_inc_tax": "143.0000",
                                "created_at": "2018-01-03 21:29:08",
                                "updated_at": "2020-06-09 00:23:22",
                                "deleted_at": null,
                                "combo_variations": null,
                                "variation_location_details": [
                                    {
                                        "id": 56,
                                        "product_id": 1,
                                        "product_variation_id": 1,
                                        "variation_id": 1,
                                        "location_id": 1,
                                        "qty_available": "20.0000",
                                        "created_at": "2020-06-08 23:46:40",
                                        "updated_at": "2020-06-08 23:46:40"
                                    }
                                ],
                                "media": [
                                    {
                                        "id": 1,
                                        "business_id": 1,
                                        "file_name": "1591686466_978227300_nn.jpeg",
                                        "description": null,
                                        "uploaded_by": 9,
                                        "model_type": "App\\Variation",
                                        "woocommerce_media_id": null,
                                        "model_id": 1,
                                        "created_at": "2020-06-09 00:07:46",
                                        "updated_at": "2020-06-09 00:07:46",
                                        "display_name": "nn.jpeg",
                                        "display_url": "http://local.pos.com/uploads/media/1591686466_978227300_nn.jpeg"
                                    }
                                ],
                                "discounts": [
                                    {
                                        "id": 2,
                                        "name": "FLAT 10%",
                                        "business_id": 1,
                                        "brand_id": null,
                                        "category_id": null,
                                        "location_id": 1,
                                        "priority": 2,
                                        "discount_type": "fixed",
                                        "discount_amount": "5.0000",
                                        "starts_at": "2021-09-01 11:45:00",
                                        "ends_at": "2021-09-30 11:45:00",
                                        "is_active": 1,
                                        "spg": null,
                                        "applicable_in_cg": 1,
                                        "created_at": "2021-09-01 11:46:00",
                                        "updated_at": "2021-09-01 12:12:55",
                                        "formated_starts_at": " 11:45",
                                        "formated_ends_at": " 11:45"
                                    }
                                ],
                                "selling_price_group": [
                                    {
                                        "id": 2,
                                        "variation_id": 1,
                                        "price_group_id": 1,
                                        "price_inc_tax": "140.0000",
                                        "created_at": "2020-06-09 00:23:31",
                                        "updated_at": "2020-06-09 00:23:31"
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "brand": {
                    "id": 1,
                    "business_id": 1,
                    "name": "Levis",
                    "description": null,
                    "created_by": 1,
                    "deleted_at": null,
                    "created_at": "2018-01-03 21:19:47",
                    "updated_at": "2018-01-03 21:19:47"
                },
                "unit": {
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
                },
                "category": {
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
                    "updated_at": "2018-01-03 21:06:34"
                },
                "sub_category": {
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
                },
                "product_tax": {
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
                 "product_locations": [
                {
                    "id": 1,
                    "business_id": 1,
                    "location_id": null,
                    "name": "Awesome Shop",
                    "landmark": "Linking Street",
                    "country": "USA",
                    "state": "Arizona",
                    "city": "Phoenix",
                    "zip_code": "85001",
                    "invoice_scheme_id": 1,
                    "invoice_layout_id": 1,
                    "selling_price_group_id": null,
                    "print_receipt_on_invoice": 1,
                    "receipt_printer_type": "browser",
                    "printer_id": null,
                    "mobile": null,
                    "alternate_number": null,
                    "email": null,
                    "website": null,
                    "featured_products": [
                        "5",
                        "71"
                    ],
                    "is_active": 1,
                    "default_payment_accounts": "{\"cash\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"card\":{\"is_enabled\":\"1\",\"account\":\"3\"},\"cheque\":{\"is_enabled\":\"1\",\"account\":\"2\"},\"bank_transfer\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"other\":{\"is_enabled\":\"1\",\"account\":\"3\"},\"custom_pay_1\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"custom_pay_2\":{\"is_enabled\":\"1\",\"account\":\"2\"},\"custom_pay_3\":{\"is_enabled\":\"1\",\"account\":\"3\"}}",
                    "custom_field1": null,
                    "custom_field2": null,
                    "custom_field3": null,
                    "custom_field4": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:15:20",
                    "updated_at": "2020-06-09 01:07:05",
                    "pivot": {
                        "product_id": 2,
                        "location_id": 1
                    }
                }]
            }
        ],
        "links": {
            "first": "http://local.pos.com/connector/api/product?page=1",
            "last": "http://local.pos.com/connector/api/product?page=32",
            "prev": null,
            "next": "http://local.pos.com/connector/api/product?page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "path": "http://local.pos.com/connector/api/product",
            "per_page": 10,
            "to": 10
        }
    }
     */
    public function index()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $filters = request()->only(['brand_id', 'category_id', 'location_id', 'sub_category_id', 'per_page']);
        $filters['selling_price_group'] = request()->input('selling_price_group') == 1 ? true : false;

        $search = request()->only(['sku', 'name']);

        //order
        $order_by = null;
        $order_direction = null;

        if (!empty(request()->input('order_by'))) {
            $order_by = in_array(request()->input('order_by'), ['product_name', 'newest']) ? request()->input('order_by') : null;
            $order_direction = in_array(request()->input('order_direction'), ['asc', 'desc']) ? request()->input('order_direction') : 'asc';
        }

        $products = $this->__getProducts($business_id, $filters, $search, true, $order_by, $order_direction);

        return $data = [
                'success' => true,
                'msg' => 'Get Products Succesfully',
                'data' => $products,
            ];
        // return ProductResource::collection($products);
    }

    /**
     * Get the specified product
     * @urlParam product required comma separated ids of products Example: 1
     * @queryParam selling_price_group (1, 0) 
     * @queryParam send_lot_detail Send lot details in each variation location details(1, 0) 
     * @response {
            "data": [
                {
                    "id": 1,
                    "name": "Men's Reverse Fleece Crew",
                    "business_id": 1,
                    "type": "single",
                    "sub_unit_ids": null,
                    "enable_stock": 1,
                    "alert_quantity": "5.0000",
                    "sku": "AS0001",
                    "barcode_type": "C128",
                    "expiry_period": null,
                    "expiry_period_type": null,
                    "enable_sr_no": 0,
                    "weight": null,
                    "product_custom_field1": null,
                    "product_custom_field2": null,
                    "product_custom_field3": null,
                    "product_custom_field4": null,
                    "image": null,
                    "woocommerce_media_id": null,
                    "product_description": null,
                    "created_by": 1,
                    "warranty_id": null,
                    "is_inactive": 0,
                    "repair_model_id": null,
                    "not_for_selling": 0,
                    "ecom_shipping_class_id": null,
                    "ecom_active_in_store": 1,
                    "woocommerce_product_id": 356,
                    "woocommerce_disable_sync": 0,
                    "image_url": "http://local.pos.com/img/default.png",
                    "product_variations": [
                        {
                            "id": 1,
                            "variation_template_id": null,
                            "name": "DUMMY",
                            "product_id": 1,
                            "is_dummy": 1,
                            "created_at": "2018-01-03 21:29:08",
                            "updated_at": "2018-01-03 21:29:08",
                            "variations": [
                                {
                                    "id": 1,
                                    "name": "DUMMY",
                                    "product_id": 1,
                                    "sub_sku": "AS0001",
                                    "product_variation_id": 1,
                                    "woocommerce_variation_id": null,
                                    "variation_value_id": null,
                                    "default_purchase_price": "130.0000",
                                    "dpp_inc_tax": "143.0000",
                                    "profit_percent": "0.0000",
                                    "default_sell_price": "130.0000",
                                    "sell_price_inc_tax": "143.0000",
                                    "created_at": "2018-01-03 21:29:08",
                                    "updated_at": "2020-06-09 00:23:22",
                                    "deleted_at": null,
                                    "combo_variations": null,
                                    "variation_location_details": [
                                        {
                                            "id": 56,
                                            "product_id": 1,
                                            "product_variation_id": 1,
                                            "variation_id": 1,
                                            "location_id": 1,
                                            "qty_available": "20.0000",
                                            "created_at": "2020-06-08 23:46:40",
                                            "updated_at": "2020-06-08 23:46:40"
                                        }
                                    ],
                                    "media": [
                                        {
                                            "id": 1,
                                            "business_id": 1,
                                            "file_name": "1591686466_978227300_nn.jpeg",
                                            "description": null,
                                            "uploaded_by": 9,
                                            "model_type": "App\\Variation",
                                            "woocommerce_media_id": null,
                                            "model_id": 1,
                                            "created_at": "2020-06-09 00:07:46",
                                            "updated_at": "2020-06-09 00:07:46",
                                            "display_name": "nn.jpeg",
                                            "display_url": "http://local.pos.com/uploads/media/1591686466_978227300_nn.jpeg"
                                        }
                                    ],
                                    "discounts": [
                                        {
                                            "id": 2,
                                            "name": "FLAT 10%",
                                            "business_id": 1,
                                            "brand_id": null,
                                            "category_id": null,
                                            "location_id": 1,
                                            "priority": 2,
                                            "discount_type": "fixed",
                                            "discount_amount": "5.0000",
                                            "starts_at": "2021-09-01 11:45:00",
                                            "ends_at": "2021-09-30 11:45:00",
                                            "is_active": 1,
                                            "spg": null,
                                            "applicable_in_cg": 1,
                                            "created_at": "2021-09-01 11:46:00",
                                            "updated_at": "2021-09-01 12:12:55",
                                            "formated_starts_at": " 11:45",
                                            "formated_ends_at": " 11:45"
                                        }
                                    ],
                                    "selling_price_group": [
                                        {
                                            "id": 2,
                                            "variation_id": 1,
                                            "price_group_id": 1,
                                            "price_inc_tax": "140.0000",
                                            "created_at": "2020-06-09 00:23:31",
                                            "updated_at": "2020-06-09 00:23:31"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "brand": {
                        "id": 1,
                        "business_id": 1,
                        "name": "Levis",
                        "description": null,
                        "created_by": 1,
                        "deleted_at": null,
                        "created_at": "2018-01-03 21:19:47",
                        "updated_at": "2018-01-03 21:19:47"
                    },
                    "unit": {
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
                    },
                    "category": {
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
                        "updated_at": "2018-01-03 21:06:34"
                    },
                    "sub_category": {
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
                    },
                    "product_tax": {
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
                    "product_locations": [
                    {
                        "id": 1,
                        "business_id": 1,
                        "location_id": null,
                        "name": "Awesome Shop",
                        "landmark": "Linking Street",
                        "country": "USA",
                        "state": "Arizona",
                        "city": "Phoenix",
                        "zip_code": "85001",
                        "invoice_scheme_id": 1,
                        "invoice_layout_id": 1,
                        "selling_price_group_id": null,
                        "print_receipt_on_invoice": 1,
                        "receipt_printer_type": "browser",
                        "printer_id": null,
                        "mobile": null,
                        "alternate_number": null,
                        "email": null,
                        "website": null,
                        "featured_products": [
                            "5",
                            "71"
                        ],
                        "is_active": 1,
                        "default_payment_accounts": "{\"cash\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"card\":{\"is_enabled\":\"1\",\"account\":\"3\"},\"cheque\":{\"is_enabled\":\"1\",\"account\":\"2\"},\"bank_transfer\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"other\":{\"is_enabled\":\"1\",\"account\":\"3\"},\"custom_pay_1\":{\"is_enabled\":\"1\",\"account\":\"1\"},\"custom_pay_2\":{\"is_enabled\":\"1\",\"account\":\"2\"},\"custom_pay_3\":{\"is_enabled\":\"1\",\"account\":\"3\"}}",
                        "custom_field1": null,
                        "custom_field2": null,
                        "custom_field3": null,
                        "custom_field4": null,
                        "deleted_at": null,
                        "created_at": "2018-01-04 02:15:20",
                        "updated_at": "2020-06-09 01:07:05",
                        "pivot": {
                            "product_id": 2,
                            "location_id": 1
                        }
                    }]
                }
            ]
        }
     */
    public function show($product_ids)
    {
        $user = Auth::user();

        // if (!$user->can('api.access')) {
        //     return $this->respondUnauthorized();
        // }

        $business_id = $user->business_id;
        $filters['selling_price_group'] = request()->input('selling_price_group') == 1 ? true : false;

        $filters['product_ids'] = explode(',', $product_ids);

        $products = $this->__getProducts($business_id, $filters);

        return ProductResource::collection($products);
    }

    /**
     * Function to query product
     * @return Response
     */
    private function __getProducts($business_id, $filters = [], $search = [], $pagination = false, $order_by = null, $order_direction = null)
    {
        $query = Product::where('business_id', $business_id);

        $with = ['product_variations.variations.variation_location_details', 'brand', 'unit', 'category', 'sub_category', 'product_tax', 'product_variations.variations.media', 'product_locations'];

        if (!empty($filters['category_id'])) {
            $category_ids = explode(',', $filters['category_id']);
            $query->whereIn('category_id', $category_ids);
        }

        if (!empty($filters['sub_category_id'])) {
            $sub_category_id = explode(',', $filters['sub_category_id']);
            $query->whereIn('sub_category_id', $sub_category_id);
        }

        if (!empty($filters['brand_id'])) {
            $brand_ids = explode(',', $filters['brand_id']);
            $query->whereIn('brand_id', $brand_ids);
        }

        if (!empty($filters['selling_price_group']) && $filters['selling_price_group'] == true) {
            $with[] = 'product_variations.variations.group_prices';
        }
        if (!empty($filters['location_id'])) {
            $location_id = $filters['location_id'];
            $query->whereHas('product_locations', function ($q) use ($location_id) {
                $q->where('product_locations.location_id', $location_id);
            });

            $with['product_variations.variations.variation_location_details'] = function ($q) use ($location_id) {
                $q->where('location_id', $location_id);
            };

            $with['product_locations'] = function ($q) use ($location_id) {
                $q->where('product_locations.location_id', $location_id);
            };
        }

        if (!empty($filters['product_ids'])) {
            $query->whereIn('id', $filters['product_ids']);
        }

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {

                if (!empty($search['name'])) {
                    $query->where('products.name', 'like', '%' . $search['name'] . '%');
                }

                if (!empty($search['sku'])) {
                    $sku = $search['sku'];
                    $query->orWhere('sku', 'like', '%' . $sku . '%');
                    $query->orWhereHas('variations', function ($q) use ($sku) {
                        $q->where('variations.sub_sku', 'like', '%' . $sku . '%');
                    });
                }
            });
        }

        //Order by
        if (!empty($order_by)) {
            if ($order_by == 'product_name') {
                $query->orderBy('products.name', $order_direction);
            }

            if ($order_by == 'newest') {
                $query->orderBy('products.id', $order_direction);
            }
        }

        $query->with($with);

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : $this->perPage;
        if ($pagination && $perPage != -1) {
            $products = $query->paginate($perPage);
            $products->appends(request()->query());
        } else {
            $products = $query->get();
        }

        return $products;
    }

    /**
     * List Variations
     * @urlParam id comma separated ids of variations Example: 2
     * @queryParam product_id Filter by comma separated products ids
     * @queryParam location_id Example: 1
     * @queryParam brand_id
     * @queryParam category_id
     * @queryParam sub_category_id
     * @queryParam not_for_selling Values: 0 or 1
     * @queryParam name Search term for product name
     * @queryParam sku Search term for product sku
     * @queryParam per_page Total records per page. default: 10, Set -1 for no pagination Example:10
     * @response {
        "data": [
            {
                "variation_id": 1,
                "variation_name": "",
                "sub_sku": "AS0001",
                "product_id": 1,
                "product_name": "Men's Reverse Fleece Crew",
                "sku": "AS0001",
                "type": "single",
                "business_id": 1,
                "barcode_type": "C128",
                "expiry_period": null,
                "expiry_period_type": null,
                "enable_sr_no": 0,
                "weight": null,
                "product_custom_field1": null,
                "product_custom_field2": null,
                "product_custom_field3": null,
                "product_custom_field4": null,
                "product_image": "1528728059_fleece_crew.jpg",
                "product_description": null,
                "warranty_id": null,
                "brand_id": 1,
                "brand_name": "Levis",
                "unit_id": 1,
                "enable_stock": 1,
                "not_for_selling": 0,
                "unit_name": "Pc(s)",
                "unit_allow_decimal": 0,
                "category_id": 1,
                "category": "Men's",
                "sub_category_id": 5,
                "sub_category": "Shirts",
                "tax_id": 1,
                "tax_type": "exclusive",
                "tax_name": "VAT@10%",
                "tax_amount": 10,
                "product_variation_id": 1,
                "default_purchase_price": "130.0000",
                "dpp_inc_tax": "143.0000",
                "profit_percent": "0.0000",
                "default_sell_price": "130.0000",
                "sell_price_inc_tax": "143.0000",
                "product_variation_name": "",
                "variation_location_details": [],
                "media": [],
                "selling_price_group": [],
                "product_image_url": "http://local.pos.com/uploads/img/1528728059_fleece_crew.jpg",
                "product_locations": [
                    {
                        "id": 1,
                        "business_id": 1,
                        "location_id": null,
                        "name": "Awesome Shop",
                        "landmark": "Linking Street",
                        "country": "USA",
                        "state": "Arizona",
                        "city": "Phoenix",
                        "zip_code": "85001",
                        "invoice_scheme_id": 1,
                        "invoice_layout_id": 1,
                        "selling_price_group_id": null,
                        "print_receipt_on_invoice": 1,
                        "receipt_printer_type": "browser",
                        "printer_id": null,
                        "mobile": null,
                        "alternate_number": null,
                        "email": null,
                        "website": null,
                        "featured_products": null,
                        "is_active": 1,
                        "default_payment_accounts": "",
                        "custom_field1": null,
                        "custom_field2": null,
                        "custom_field3": null,
                        "custom_field4": null,
                        "deleted_at": null,
                        "created_at": "2018-01-04 02:15:20",
                        "updated_at": "2019-12-11 04:53:39",
                        "pivot": {
                            "product_id": 1,
                            "location_id": 1
                        }
                    }
                ]
            },
            {
                "variation_id": 2,
                "variation_name": "28",
                "sub_sku": "AS0002-1",
                "product_id": 2,
                "product_name": "Levis Men's Slimmy Fit Jeans",
                "sku": "AS0002",
                "type": "variable",
                "business_id": 1,
                "barcode_type": "C128",
                "expiry_period": null,
                "expiry_period_type": null,
                "enable_sr_no": 0,
                "weight": null,
                "product_custom_field1": null,
                "product_custom_field2": null,
                "product_custom_field3": null,
                "product_custom_field4": null,
                "product_image": "1528727964_levis_jeans.jpg",
                "product_description": null,
                "warranty_id": null,
                "brand_id": 1,
                "brand_name": "Levis",
                "unit_id": 1,
                "enable_stock": 1,
                "not_for_selling": 0,
                "unit_name": "Pc(s)",
                "unit_allow_decimal": 0,
                "category_id": 1,
                "category": "Men's",
                "sub_category_id": 4,
                "sub_category": "Jeans",
                "tax_id": 1,
                "tax_type": "exclusive",
                "tax_name": "VAT@10%",
                "tax_amount": 10,
                "product_variation_id": 2,
                "default_purchase_price": "70.0000",
                "dpp_inc_tax": "77.0000",
                "profit_percent": "0.0000",
                "default_sell_price": "70.0000",
                "sell_price_inc_tax": "77.0000",
                "product_variation_name": "Waist Size",
                "variation_location_details": [
                    {
                        "id": 1,
                        "product_id": 2,
                        "product_variation_id": 2,
                        "variation_id": 2,
                        "location_id": 1,
                        "qty_available": "50.0000",
                        "created_at": "2018-01-06 06:57:11",
                        "updated_at": "2020-08-04 04:11:27"
                    }
                ],
                "media": [
                    {
                        "id": 1,
                        "business_id": 1,
                        "file_name": "1596701997_743693452_test.jpg",
                        "description": null,
                        "uploaded_by": 9,
                        "model_type": "App\\Variation",
                        "woocommerce_media_id": null,
                        "model_id": 2,
                        "created_at": "2020-08-06 13:49:57",
                        "updated_at": "2020-08-06 13:49:57",
                        "display_name": "test.jpg",
                        "display_url": "http://local.pos.com/uploads/media/1596701997_743693452_test.jpg"
                    }
                ],
                "selling_price_group": [],
                "product_image_url": "http://local.pos.com/uploads/img/1528727964_levis_jeans.jpg",
                "product_locations": [
                    {
                        "id": 1,
                        "business_id": 1,
                        "location_id": null,
                        "name": "Awesome Shop",
                        "landmark": "Linking Street",
                        "country": "USA",
                        "state": "Arizona",
                        "city": "Phoenix",
                        "zip_code": "85001",
                        "invoice_scheme_id": 1,
                        "invoice_layout_id": 1,
                        "selling_price_group_id": null,
                        "print_receipt_on_invoice": 1,
                        "receipt_printer_type": "browser",
                        "printer_id": null,
                        "mobile": null,
                        "alternate_number": null,
                        "email": null,
                        "website": null,
                        "featured_products": null,
                        "is_active": 1,
                        "default_payment_accounts": "",
                        "custom_field1": null,
                        "custom_field2": null,
                        "custom_field3": null,
                        "custom_field4": null,
                        "deleted_at": null,
                        "created_at": "2018-01-04 02:15:20",
                        "updated_at": "2019-12-11 04:53:39",
                        "pivot": {
                            "product_id": 2,
                            "location_id": 1
                        }
                    }
                ],
                "discounts": [
                    {
                        "id": 2,
                        "name": "FLAT 10%",
                        "business_id": 1,
                        "brand_id": null,
                        "category_id": null,
                        "location_id": 1,
                        "priority": 2,
                        "discount_type": "fixed",
                        "discount_amount": "5.0000",
                        "starts_at": "2021-09-01 11:45:00",
                        "ends_at": "2021-09-30 11:45:00",
                        "is_active": 1,
                        "spg": null,
                        "applicable_in_cg": 1,
                        "created_at": "2021-09-01 11:46:00",
                        "updated_at": "2021-09-01 12:12:55",
                        "formated_starts_at": " 11:45",
                        "formated_ends_at": " 11:45"
                    }
                ]
            }
        ],
        "links": {
            "first": "http://local.pos.com/connector/api/variation?page=1",
            "last": null,
            "prev": null,
            "next": "http://local.pos.com/connector/api/variation?page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "path": "http://local.pos.com/connector/api/variation",
            "per_page": "2",
            "to": 2
        }
    }
     */
    public function listVariations($variation_ids = null)
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $query = Variation::join('products AS p', 'variations.product_id', '=', 'p.id')
            ->join('product_variations AS pv', 'variations.product_variation_id', '=', 'pv.id')
            ->leftjoin('units', 'p.unit_id', '=', 'units.id')
            ->leftjoin('tax_rates as tr', 'p.tax', '=', 'tr.id')
            ->leftjoin('brands', function ($join) {
                $join->on('p.brand_id', '=', 'brands.id')
                    ->whereNull('brands.deleted_at');
            })
            ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftjoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
            ->where('p.business_id', $business_id)
            ->select(
                'variations.id',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.id as product_id',
                'p.name as product_name',
                'p.sku',
                'p.type as type',
                'p.business_id',
                'p.barcode_type',
                'p.expiry_period',
                'p.expiry_period_type',
                'p.enable_sr_no',
                'p.weight',
                'p.product_custom_field1',
                'p.product_custom_field2',
                'p.product_custom_field3',
                'p.product_custom_field4',
                'p.image as product_image',
                'p.product_description',
                'p.warranty_id',
                'p.brand_id',
                'brands.name as brand_name',
                'p.unit_id',
                'p.enable_stock',
                'p.not_for_selling',
                'units.short_name as unit_name',
                'units.allow_decimal as unit_allow_decimal',
                'p.category_id',
                'c.name as category',
                'p.sub_category_id',
                'sc.name as sub_category',
                'p.tax as tax_id',
                'p.tax_type',
                'tr.name as tax_name',
                'tr.amount as tax_amount',
                'variations.product_variation_id',
                'variations.default_purchase_price',
                'variations.dpp_inc_tax',
                'variations.profit_percent',
                'variations.default_sell_price',
                'variations.sell_price_inc_tax',
                'pv.id as product_variation_id',
                'pv.name as product_variation_name'
            );

        $with = [
            'variation_location_details',
            'media',
            'group_prices',
            'product',
            'product.product_locations'
        ];

        if (!empty(request()->input('category_id'))) {
            $query->where('category_id', request()->input('category_id'));
        }

        if (!empty(request()->input('sub_category_id'))) {
            $query->where('p.sub_category_id', request()->input('sub_category_id'));
        }

        if (!empty(request()->input('brand_id'))) {
            $query->where('p.brand_id', request()->input('brand_id'));
        }

        if (request()->has('not_for_selling')) {
            $not_for_selling = request()->input('not_for_selling') == 1 ? 1 : 0;
            $query->where('p.not_for_selling', $not_for_selling);
        }
        $filters['selling_price_group'] = request()->input('selling_price_group') == 1 ? true : false;

        if (!empty(request()->input('location_id'))) {
            $location_id = request()->input('location_id');
            $query->whereHas('product.product_locations', function ($q) use ($location_id) {
                $q->where('product_locations.location_id', $location_id);
            });

            $with['variation_location_details'] = function ($q) use ($location_id) {
                $q->where('location_id', $location_id);
            };

            $with['product.product_locations'] = function ($q) use ($location_id) {
                $q->where('product_locations.location_id', $location_id);
            };
        }

        $search = request()->only(['sku', 'name']);

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {

                if (!empty($search['name'])) {
                    $query->where('p.name', 'like', '%' . $search['name'] . '%');
                }

                if (!empty($search['sku'])) {
                    $sku = $search['sku'];
                    $query->orWhere('p.sku', 'like', '%' . $sku . '%')
                        ->where('variations.sub_sku', 'like', '%' . $sku . '%');
                }
            });
        }

        //filter by variations ids
        if (!empty($variation_ids)) {
            $variation_ids = explode(',', $variation_ids);
            $query->whereIn('variations.id', $variation_ids);
        }

        //filter by product ids
        if (!empty(request()->input('product_id'))) {
            $product_ids = explode(',', request()->input('product_id'));
            $query->whereIn('p.id', $product_ids);
        }

        $query->with($with);

        $perPage = !empty(request()->input('per_page')) ? request()->input('per_page') : $this->perPage;
        if ($perPage == -1) {
            $variations = $query->get();
        } else {
            //paginate
            $variations = $query->paginate($perPage);
            $variations->appends(request()->query());
        }

        return VariationResource::collection($variations);
    }

    /**
     * List Selling Price Group
     *
     * @response {
        "data": [
            {
                "id": 1,
                "name": "Retail",
                "description": null,
                "business_id": 1,
                "is_active": 1,
                "deleted_at": null,
                "created_at": "2020-10-21 04:30:06",
                "updated_at": "2020-11-16 18:23:15"
            },
            {
                "id": 2,
                "name": "Wholesale",
                "description": null,
                "business_id": 1,
                "is_active": 1,
                "deleted_at": null,
                "created_at": "2020-10-21 04:30:21",
                "updated_at": "2020-11-16 18:23:00"
            }
        ]
    }
     */
    public function getSellingPriceGroup()
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->get();

        return CommonResource::collection($price_groups);
    }


    public function get_Variations()
    {

        $user = Auth::user();
        $business_id = $user->business_id;

        $variations = VariationTemplate::where('business_id', $business_id)
            ->with(['values'])
            // ->select('id', 'name', DB::raw("(SELECT COUNT(id) FROM product_variations WHERE product_variations.variation_template_id=variation_templates.id) as total_pv"))
        ;
        $filters = request()->only(['per_page']);
        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : $perPage = 10;
        if ($perPage == -1) {
            $data = $variations->get();
        } else {
            $data = $variations->Paginate($perPage);
            $data->appends(request()->query());
        }

        // dd($variations);
        return CommonResource::collection($data);
    }

    public function store_variation(Request $request)
    {
        
        $input = $request->only(['name']);
        $user = Auth::user();
        $business_id = $user->business_id;
        $input['business_id'] =  $user->business_id;
        $variation = VariationTemplate::create($input);

        //craete variation values
        if (!empty($request->input('variation_values'))) {

            $values = $request->input('variation_values');
            $data = [];
            foreach ($values as $value) {
                if (!empty($value)) {
                    $data[] = ['name' => $value];
                }
            }
            $variation->values()->createMany($data);
        }
      
        $output = [
            'success' => true,
            'data' => $variation,
            'msg' => 'Variation added succesfully'
        ];
   
        

        return $output;
    }

    public function show_variation($id)
    {
        $user = Auth::user();
                $business_id = $user->business_id; 
     
            $variation = VariationTemplate::where('business_id', $business_id)
                            ->with(['values'])->find($id);

            return $variation
            ;
     
    }
//     public function update_variation(Request $request, $id)
//     {
//         // if (request()->ajax()) {
//         //     try {
//                 $input = $request->only(['name']);
//                 $user = Auth::user();
//                 $business_id = $user->business_id;

//                 $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);
// // dd($variation);
//                 if (isset($input['name']) && $variation->name != $input['name']) {
//                     $variation->name = $input['name'];
//                     $variation->save();
//                 }
                
//                     ProductVariation::where('variation_template_id', $variation->id)
//                                 ->update(['name' => $variation->name]);
//                 // }
                
//                 //update variation
//                 $data = [];
//                 if (!empty($request->input('edit_variation_values'))) {
//                     $values = $request->input('edit_variation_values');
//                     foreach ($values as $key => $value) {
//                         if (!empty($value)) {
//                             $variation_val = VariationValueTemplate::find($key);

//                             if ($variation_val->name != $value) {
//                                 $variation_val->name = $value;
//                                 $data[] = $variation_val;
//                                 Variation::where('variation_value_id', $key)
//                                     ->update(['name' => $value]);
//                             }
//                         }
//                     }
//                     $variation->values()->saveMany($data);
//                 }
//                 if (!empty($request->input('variation_values'))) {
//                     $values = $request->input('variation_values');
//                     foreach ($values as $value) {
//                         if (!empty($value)) {
//                             $data[] = new VariationValueTemplate([ 'name' => $value]);
//                         }
//                     }
//                 }
//                 $variation->values()->saveMany($data);

//                 $output = ['success' => true,
//                             'msg' => 'Variation updated succesfully'
//                             ];
//             // } catch (\Exception $e) {
//             //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
//             //     $output = ['success' => false,
//             //                 'msg' => 'Something went wrong, please try again'
//             //             ];
//             // }

//             return $output;
//         // }
//     }


public function update_variation(Request $request, $id)
    {
        $input = $request->only(['name']);
        $user = Auth::user();
        $business_id = $user->business_id;

        $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);

        if ($variation->name != $input['name']) {
            $variation->name = $input['name'];
            $variation->save();

            ProductVariation::where('variation_template_id', $variation->id)
                ->update(['name' => $variation->name]);
        }

        // Update variation
        if (!empty($request->input('edit_variation_values'))) {

            $values = $request->input('edit_variation_values');
            foreach ($values as $value) {
                foreach ($value as $key => $name) {
                    $variation_val = VariationValueTemplate::where('id', $key)->first();

                    if ($variation_val) {
                        $variation_val->name = $name;
                        $variation_val->save();

                        Variation::where('variation_value_id', $id)
                            ->update(['name' => strval($name)]);
                    }
                }
            }
        }



        $output = [
            'success' => true,
            'msg' => 'Variation updated succesfully'
        ];
        return $output;
    }
    
    
    
    
    
    public function delete($id)
    {
        // if (request()->ajax()) {
        //     try {
            $user = Auth::user();
            $business_id = $user->business_id;

                $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);
                $variation->delete();

                $output = ['success' => true,
                            'msg' => 'Category deleted succesfully'
                            ];
            // } catch (\Eexception $e) {
            //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            //     $output = ['success' => false,
            //                 'msg' => 'Something went wrong, please try again'
            //             ];
            // }

            return $output;
        // }
    }
//       public function indexMfgRecipe()
//     {

//         $user = Auth::user();

//         $business_id = $user->business_id;
      


//         $recipes = MfgRecipe::join('variations as v', 'mfg_recipes.variation_id', '=', 'v.id')
//             ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
//             ->join('products as p', 'v.product_id', '=', 'p.id')
//             ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
//             ->leftJoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
//             ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
//             ->where('p.business_id', $business_id)
//             ->with([
//                 'ingredients',
//                 'ingredients.variation',
//                 'ingredients', 
//                 'ingredients.sub_unit',
//                 'variation',
//                 'variation.product',
//                 'variation.product_variation',
//                 'sub_unit',
//                 'variation.product.unit',


//             ])
//             ->select(
//                 'mfg_recipes.id',
//                 DB::raw('IF(p.type = "variable", CONCAT(p.name, " - ", pv.name, " - ", v.name, " (", v.sub_sku, ")"), CONCAT(p.name, " (", v.sub_sku, ")")) as recipe_name'),
//                 'mfg_recipes.extra_cost',
//                 'mfg_recipes.final_price',
//                 'mfg_recipes.variation_id',
//                 'mfg_recipes.total_quantity',
//                 'mfg_recipes.production_cost_type',
//                 'mfg_recipes.waste_percent',
//                 'mfg_recipes.sub_unit_id',
//                 'u.short_name as unit_name',
//                 'c.name as category',
//                 'sc.name as sub_category',
//                 'p.name as product_name'



//             )
//             ->get();

//         //  dd($ingredients);
//         $data = [
        
//             "recipes" => $recipes
//         ];

//         return[
//             "data"=>$data
//             ];
//     }

//  public function showMfgRecipe($id)
//     {

//         $user = Auth::user();

//         $business_id = $user->business_id;
//         $recipes = MfgRecipe::join('variations as v', 'mfg_recipes.variation_id', '=', 'v.id')
//             ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
//             ->join('products as p', 'v.product_id', '=', 'p.id')
//             ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
//             ->leftjoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
//             ->join('units as u', 'p.unit_id', '=', 'u.id')
//             ->where('p.business_id', $business_id)
//             ->with(['ingredients', 'ingredients.variation', 'ingredients.sub_unit', 'sub_unit'])
//             ->select(
//                 'mfg_recipes.id',
//                 DB::raw('IF(
//                                         p.type="variable", 
//                                         CONCAT(p.name, " - ", pv.name, " - ", v.name, " (", v.sub_sku, ")"), 
//                                         CONCAT(p.name, " (", v.sub_sku, ")") 
//                                         ) as recipe_name'),
//                 'mfg_recipes.extra_cost',
//                 'mfg_recipes.final_price',
//                 'mfg_recipes.variation_id',
//                 'mfg_recipes.total_quantity',
//                 'mfg_recipes.production_cost_type',
//                 'mfg_recipes.waste_percent',
//                 'mfg_recipes.sub_unit_id',
//                 'u.short_name as unit_name',
//                 'c.name as category',
//                 'sc.name as sub_category'
//             )
//             ->find($id);







//         return [
//             "recipes" => $recipes
//         ];
//     }
 
//     public function storeMfgRecipe(Request $request)
//     {
        
//         $user = Auth::user();
//         $business_id = $user->business_id;
       
//         // try {
//             $input = $request->only(['variation_id', 'ingredients', 'total', 'instructions',
//             'ingredients_cost', 'waste_percent', 'total_quantity', 'extra_cost', 'production_cost_type']);
//         if (!empty($input['ingredients'])) {
//             $variation = Variation::findOrFail($input['variation_id']);


//             $recipe = MfgRecipe::updateOrCreate(
//                 [
//                     'variation_id' => $input['variation_id'],
//                 ],
//                 [
//                     'product_id' => $variation->product_id,
//                     'final_price' => $this->moduleUtil->num_uf($input['total']),
//                     'ingredients_cost' => $input['ingredients_cost'],
//                     'waste_percent' => $this->moduleUtil->num_uf($input['waste_percent']),
//                     'total_quantity' => $this->moduleUtil->num_uf($input['total_quantity']),
//                     'extra_cost' => $this->moduleUtil->num_uf($input['extra_cost']),
//                     'production_cost_type' => $input['production_cost_type'],
//                     'instructions' => $input['instructions'],
//                     'sub_unit_id' => !empty($request->input('sub_unit_id')) ? $request->input('sub_unit_id') : null
//                 ]
//             );

//             $ingredients = [];
//             $edited_ingredients = [];
//             $ingredient_groups = $request->input('ingredient_groups');
//             $ingredient_group_descriptions = $request->input('ingredient_group_description');
//             $created_ig_groups = [];
            
//             foreach ($input['ingredients'] as $key => $value) {
//                 $variation = Variation::with(['product'])
//                                     ->findOrFail($value['variation_id']);

//                 if (!empty($value['ingredient_line_id'])) {
//                     $ingredient = MfgRecipeIngredient::find($value['ingredient_line_id']);
//                     $edited_ingredients[] = $ingredient->id;
//                 } else {
//                     $ingredient = new MfgRecipeIngredient(['variation_id' => $value['variation_id']]);
//                 }

//                 $ingredient->quantity = $this->moduleUtil->num_uf($value['quantity']);
//                 $ingredient->waste_percent = $this->moduleUtil->num_uf($value['waste_percent']);
//                 $ingredient->sort_order = $this->moduleUtil->num_uf($value['sort_order']);

//                 $ingredient->sub_unit_id = !empty($value['sub_unit_id']) && $value['sub_unit_id'] != $variation->product->unit_id ? $value['sub_unit_id'] : null;

//                 //Set ingredient group
//                 if (isset($value['ig_index'])) {
//                     $ig_name = $ingredient_groups[$value['ig_index']];
//                     $ig_description = $ingredient_group_descriptions[$value['ig_index']];

//                     //Create ingredient group if not created already
//                     if (!empty($created_ig_groups[$value['ig_index']])) {
//                         $ingredient_group = $created_ig_groups[$value['ig_index']];
//                     } elseif (empty($value['mfg_ingredient_group_id'])) {
//                         $ingredient_group = MfgIngredientGroup::create(
//                             [
//                                 'name' => $ig_name,
//                                 'business_id' => $business_id,
//                                 'description' => $ig_description
//                             ]
//                         );
//                     } else {
//                         $ingredient_group = MfgIngredientGroup::where('business_id', $business_id)
//                                                             ->find($value['mfg_ingredient_group_id']);
//                         if ($ingredient_group->name != $ig_name || $ingredient_group->description != $ig_description) {
//                             $ingredient_group->name = $ig_name;
//                             $ingredient_group->description = $ig_description;
//                             $ingredient_group->save();
//                         }

//                         $ingredient_group = MfgIngredientGroup::firstOrNew(
//                             ['business_id' => $business_id, 'id' => $value['mfg_ingredient_group_id']],
//                             ['name' => $ig_name, 'description' => $ig_description]
//                         );
//                     }

//                     $created_ig_groups[$value['ig_index']] = $ingredient_group;

//                     $ingredient->mfg_ingredient_group_id = $ingredient_group->id;
//                 }

//                 $ingredients[] = $ingredient;
//             }
//             if (!empty($edited_ingredients)) {
//                 MfgRecipeIngredient::where('mfg_recipe_id', $recipe->id)
//                                             ->whereNotIn('id', $edited_ingredients)
//                                             ->delete();
//             }

//             $recipe->ingredients()->saveMany($ingredients);
//         }
//         $output = ['success' => 1,
//                         'msg' => __('lang_v1.added_success')
//                     ];
//         // } catch (\Exception $e) {
//         //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
//         //     $output = ['success' => 0,
//         //                     'msg' => __("messages.something_went_wrong")
//         //                 ];
//         // }

//         return $output;
//     }
//     public function destroy($id)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;
//         // if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.add_recipe')) {
//         //     abort(403, 'Unauthorized action.');
//         // }

//         // try {
//             $recipe = MfgRecipe::where('id', $id)
//                         ->delete();

//             $output = ['success' => 1,
//                             'msg' => __('lang_v1.deleted_success')
//                         ];
//         // } catch (\Exception $e) {
//         //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
//         //     $output = ['success' => 0,
//         //                     'msg' => __('messages.something_went_wrong')
//         //                 ];
//         // }

//         return $output;
//     }
//     public function edit($id)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $recipe = MfgRecipe::with(['variation', 'variation.product', 'variation.product_variation', 'variation.media', 'sub_unit', 'variation.product.unit'])
//                         ->findOrFail($id);

//         $ingredients = $this->mfgUtil->getIngredientDetails($recipe, $business_id);
//         return  $ingredients;
//     }




    public function indexMfgRecipe()
    {

        $user = Auth::user();

        $business_id = $user->business_id;



        $recipes = MfgRecipe::join('variations as v', 'mfg_recipes.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('p.business_id', $business_id)
            ->with([
                'ingredients',
                'ingredients.variation',
                'ingredients',
                'ingredients.sub_unit',
                'variation',
                'variation.product',
                'variation.product_variation',
                'sub_unit',
                'variation.product.unit',
                'ingredients.variation.product',

    
            ])
            ->select(
                'mfg_recipes.id',
                DB::raw('IF(p.type = "variable", CONCAT(p.name, " - ", pv.name, " - ", v.name, " (", v.sub_sku, ")"), CONCAT(p.name, " (", v.sub_sku, ")")) as recipe_name'),
                'mfg_recipes.extra_cost',
                'mfg_recipes.final_price',
                'mfg_recipes.variation_id',
                'mfg_recipes.total_quantity',
                'mfg_recipes.production_cost_type',
                'mfg_recipes.waste_percent',
                'mfg_recipes.sub_unit_id',
                'u.short_name as unit_name',
                'c.name as category',
                'sc.name as sub_category',
                'p.name as product_name'



            )
            ->get();
        return [
            "recipes" => $recipes
        ];
    }



    public function storeMfgRecipe(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->business_id;

        // try {
        $input = $request->only([
            'variation_id', 'ingredients', 'total', 'instructions',
            'ingredients_cost', 'waste_percent', 'total_quantity', 'extra_cost', 'production_cost_type'
        ]);
        if (!empty($input['ingredients'])) {
            $variation = Variation::findOrFail($input['variation_id']);


            $recipe = MfgRecipe::updateOrCreate(
                [
                    'variation_id' => $input['variation_id'],
                ],
                [
                    'product_id' => $variation->product_id,
                    'final_price' => $this->moduleUtil->num_uf($input['total']),
                    'ingredients_cost' => $input['ingredients_cost'],
                    'waste_percent' => $this->moduleUtil->num_uf($input['waste_percent']),
                    'total_quantity' => $this->moduleUtil->num_uf($input['total_quantity']),
                    'extra_cost' => $this->moduleUtil->num_uf($input['extra_cost']),
                    'production_cost_type' => $input['production_cost_type'],
                    'instructions' => $input['instructions'],
                    'sub_unit_id' => !empty($request->input('sub_unit_id')) ? $request->input('sub_unit_id') : null
                ]
            );

            $ingredients = [];
            $edited_ingredients = [];
            $ingredient_groups = $request->input('ingredient_groups');
            $ingredient_group_descriptions = $request->input('ingredient_group_description');
            $created_ig_groups = [];

            foreach ($input['ingredients'] as $key => $value) {
                $variation = Variation::with(['product'])
                    ->findOrFail($value['variation_id']);

                if (!empty($value['ingredient_line_id'])) {
                    $ingredient = MfgRecipeIngredient::find($value['ingredient_line_id']);
                    $edited_ingredients[] = $ingredient->id;
                } else {
                    $ingredient = new MfgRecipeIngredient(['variation_id' => $value['variation_id']]);
                }

                $ingredient->quantity = $this->moduleUtil->num_uf($value['quantity']);
                $ingredient->waste_percent = $this->moduleUtil->num_uf($value['waste_percent']);
                $ingredient->sort_order = $this->moduleUtil->num_uf($value['sort_order']);

                $ingredient->sub_unit_id = !empty($value['sub_unit_id']) && $value['sub_unit_id'] != $variation->product->unit_id ? $value['sub_unit_id'] : null;

                //Set ingredient group
                if (isset($value['ig_index'])) {
                    $ig_name = $ingredient_groups[$value['ig_index']];
                    $ig_description = $ingredient_group_descriptions[$value['ig_index']];

                    //Create ingredient group if not created already
                    if (!empty($created_ig_groups[$value['ig_index']])) {
                        $ingredient_group = $created_ig_groups[$value['ig_index']];
                    } elseif (empty($value['mfg_ingredient_group_id'])) {
                        $ingredient_group = MfgIngredientGroup::create(
                            [
                                'name' => $ig_name,
                                'business_id' => $business_id,
                                'description' => $ig_description
                            ]
                        );
                    } else {
                        $ingredient_group = MfgIngredientGroup::where('business_id', $business_id)
                            ->find($value['mfg_ingredient_group_id']);
                        if ($ingredient_group->name != $ig_name || $ingredient_group->description != $ig_description) {
                            $ingredient_group->name = $ig_name;
                            $ingredient_group->description = $ig_description;
                            $ingredient_group->save();
                        }

                        $ingredient_group = MfgIngredientGroup::firstOrNew(
                            ['business_id' => $business_id, 'id' => $value['mfg_ingredient_group_id']],
                            ['name' => $ig_name, 'description' => $ig_description]
                        );
                    }

                    $created_ig_groups[$value['ig_index']] = $ingredient_group;

                    $ingredient->mfg_ingredient_group_id = $ingredient_group->id;
                }

                $ingredients[] = $ingredient;
            }
            if (!empty($edited_ingredients)) {
                MfgRecipeIngredient::where('mfg_recipe_id', $recipe->id)
                    ->whereNotIn('id', $edited_ingredients)
                    ->delete();
            }

            $recipe->ingredients()->saveMany($ingredients);
       
        }
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.added_success')
        ];
     

        return $output;
    }
    public function destroy($id)
    {
        $user = Auth::user();
        $business_id = $user->business_id;
     
        $recipe = MfgRecipe::where('id', $id)
            ->delete();

        $output = [
            'success' => 1,
            'msg' => __('lang_v1.deleted_success')
        ];
       

        return $output;
    }
    public function edit($id)
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $recipe = MfgRecipe::with(['variation', 'variation.product', 'variation.product_variation', 'variation.media', 'sub_unit', 'variation.product.unit'])
            ->findOrFail($id);

        $ingredients = $this->mfgUtil->getIngredientDetails($recipe, $business_id);
        return  $ingredients;
    }
    // public function updateMfgRecipe(Request $request, $recipe_ids)
    // {

    //     $user = Auth::user();
    //     $business_id = $user->business_id;

    //     // try {
    //     $input = $request->only([
    //         'product_id', 'variation_id', 'ingredients', 'total', 'instructions',
    //         'ingredients_cost', 'waste_percent', 'total_quantity', 'extra_cost', 'production_cost_type'
    //     ]);

    //     if (!empty($input['ingredients'])) {
    //         $variation = Variation::findOrFail($input['variation_id']);


    //         $recipe = MfgRecipe::findOrFail($recipe_ids);

    //         $recipe->fill(

    //             [
    //                 'variation_id' => $input['variation_id'],
    //             ],

    //             [
    //                 'product_id' => $variation->product_id,
    //                 'final_price' => $this->moduleUtil->num_uf($input['total']),
    //                 'ingredients_cost' => $input['ingredients_cost'],
    //                 'waste_percent' => $this->moduleUtil->num_uf($input['waste_percent']),
    //                 'total_quantity' => $this->moduleUtil->num_uf($input['total_quantity']),
    //                 'extra_cost' => $this->moduleUtil->num_uf($input['extra_cost']),
    //                 'production_cost_type' => $input['production_cost_type'],
    //                 'instructions' => $input['instructions'],
    //                 'sub_unit_id' => !empty($request->input('sub_unit_id')) ? $request->input('sub_unit_id') : null
    //             ]
    //         );
    //         $recipe->save();
    //         $ingredient = [];


    //         foreach ($input['ingredients'] as $key => $value) {
    //             // dd($value['mfg_recipe_id']);
    //             $ingredient = MfgRecipeIngredient::find($value['id']);

    //             // dd($ingredient);

    //             $ingredient->variation_id = $value['variation_id'];
    //             $ingredient->quantity = $this->moduleUtil->num_uf($value['quantity']);
    //             $ingredient->waste_percent = $this->moduleUtil->num_uf($value['waste_percent']);
    //             $ingredient->sort_order = $this->moduleUtil->num_uf($value['sort_order']);
    //             $ingredient->sub_unit_id = !empty($value['sub_unit_id']) && $value['sub_unit_id'] != $variation->product->unit_id ? $value['sub_unit_id'] : null;

    //             $ingredient->save();
    //         }
    //         $output = [
    //             'success' => 1,
    //             'msg' => __('lang_v1.added_success')
    //         ];
    //         return $output;
    //     }
    // }
    
    
     public function updateMfgRecipe(Request $request, $recipe_ids)
    {

        $user = Auth::user();
        $business_id = $user->business_id;

        // try {
        $input = $request->only([
            'product_id', 'variation_id', 'ingredients', 'total', 'instructions',
            'ingredients_cost', 'waste_percent', 'total_quantity', 'extra_cost', 'production_cost_type'
        ]);
     

        if (!empty($input['ingredients'])) {
            $variation = Variation::findOrFail($input['variation_id']);


            $recipe = MfgRecipe::updateOrCreate(
                [
                    'variation_id' => $input['variation_id'],
                ],
                [
                    'product_id' => $variation->product_id,
                    'final_price' => $this->moduleUtil->num_uf($input['total']),
                    'ingredients_cost' => $input['ingredients_cost'],
                    'waste_percent' => $this->moduleUtil->num_uf($input['waste_percent']),
                    'total_quantity' => $this->moduleUtil->num_uf($input['total_quantity']),
                    'extra_cost' => $this->moduleUtil->num_uf($input['extra_cost']),
                    'production_cost_type' => $input['production_cost_type'],
                    'instructions' => $input['instructions'],
                    'sub_unit_id' => !empty($request->input('sub_unit_id')) ? $request->input('sub_unit_id') : null
                ]
            );

            
            $ingredients = [];
            $edited_ingredients = [];
            $ingredient_groups = $request->input('ingredient_groups');
            $ingredient_group_descriptions = $request->input('ingredient_group_description');
            $created_ig_groups = [];

            foreach ($input['ingredients'] as $key => $value) {
            
                $variation = Variation::with(['product'])
                ->find($value['ingredient_id']);
                   
                   
                if (!empty($value['ingredient_line_id'])) {
                    $ingredient = MfgRecipeIngredient::find($value['ingredient_line_id']);
                    $edited_ingredients[] = $ingredient->id;
                } else {
                    $ingredient = new MfgRecipeIngredient(['variation_id' => $value['ingredient_id']]);
                }

                $ingredient->quantity = $this->moduleUtil->num_uf($value['quantity']);
            
                $ingredient->waste_percent = $this->moduleUtil->num_uf($value['waste_percent']);
                $ingredient->sort_order = $this->moduleUtil->num_uf($value['sort_order']);

                $ingredient['sub_unit_id'] = empty($value['sub_unit_id']) && $value['sub_unit_id'] != $variation->product->unit_id ? $value['sub_unit_id'] : null;
              
                //Set ingredient group
                if (isset($value['ig_index'])) {
                    $ig_name = $ingredient_groups[$value['ig_index']];
                    $ig_description = $ingredient_group_descriptions[$value['ig_index']];

                    //Create ingredient group if not created already
                    if (!empty($created_ig_groups[$value['ig_index']])) {
                        $ingredient_group = $created_ig_groups[$value['ig_index']];
                    } elseif (empty($value['mfg_ingredient_group_id'])) {
                        $ingredient_group = MfgIngredientGroup::create(
                            [
                                'name' => $ig_name,
                                'business_id' => $business_id,
                                'description' => $ig_description
                            ]
                        );
                    } else {
                        $ingredient_group = MfgIngredientGroup::where('business_id', $business_id)
                            ->find($value['mfg_ingredient_group_id']);
                        if ($ingredient_group->name != $ig_name || $ingredient_group->description != $ig_description) {
                            $ingredient_group->name = $ig_name;
                            $ingredient_group->description = $ig_description;
                            $ingredient_group->save();
                        }

                        $ingredient_group = MfgIngredientGroup::firstOrNew(
                            ['business_id' => $business_id, 'id' => $value['mfg_ingredient_group_id']],
                            ['name' => $ig_name, 'description' => $ig_description]
                        );
                    }

                    $created_ig_groups[$value['ig_index']] = $ingredient_group;

                    $ingredient->mfg_ingredient_group_id = $ingredient_group->id;
                }

                $ingredients[] = $ingredient;
            }
            if (!empty($edited_ingredients)) {
                MfgRecipeIngredient::where('mfg_recipe_id', $recipe->id)
                    ->whereNotIn('id', $edited_ingredients)
                    ->delete();
            }

            $recipe->ingredients()->saveMany($ingredients);
            $output = [
                        'success' => 1,
                        'msg' => __('lang_v1.added_success')
                    ];
                    return $output;
        }
        // if (!empty($input['ingredients'])) {
          
        //     $variation = Variation::findOrFail($input['variation_id']);

           
        //     $recipe = MfgRecipe::findOrFail($recipe_ids);
            
        //     $recipe->fill(

            
                  
                

        //         [
        //             'variation_id' => $input['variation_id'],
        //             'product_id' => $variation->product_id,
        //             'final_price' => $this->moduleUtil->num_uf($input['total']),
        //             'ingredients_cost' => $input['ingredients_cost'],
        //             'waste_percent' => $this->moduleUtil->num_uf($input['waste_percent']),
        //             'total_quantity' => $this->moduleUtil->num_uf($input['total_quantity']),
        //             'extra_cost' => $this->moduleUtil->num_uf($input['extra_cost']),
        //             'production_cost_type' => $input['production_cost_type'],
        //             'instructions' => $input['instructions'],
        //             'sub_unit_id' => !empty($request->input('sub_unit_id')) ? $request->input('sub_unit_id') : null
        //         ]
        //     );
        //     $recipe->save();
        //     // $ingredient = [];

        //     foreach ($input as $key => $value) {
               
        //         $ingredient = MfgRecipeIngredient::findOrCreate($value);

        //         dd($ingredient);

             
        //             $ingredient->variation_id = $value['variation_id'];
        //             $ingredient->quantity = $this->moduleUtil->num_uf($value['quantity']);
        //             $ingredient->waste_percent = $this->moduleUtil->num_uf($value['waste_percent']);
        //             $ingredient->sort_order = $this->moduleUtil->num_uf($value['sort_order']);
        //             $ingredient->sub_unit_id = !empty($value['sub_unit_id']) && $value['sub_unit_id'] != $variation->product->unit_id ? $value['sub_unit_id'] : null;
                    
        //             $ingredient->save();
               
        //     }
        //     $output = [
        //         'success' => 1,
        //         'msg' => __('lang_v1.added_success')
        //     ];
        //     return $output;
        // }
    }
        public function showMfgRecipe($id)
        {

            $user = Auth::user();

            $business_id = $user->business_id;
            $recipes = MfgRecipe::join('variations as v', 'mfg_recipes.variation_id', '=', 'v.id')
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'v.product_id', '=', 'p.id')
                ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
                ->leftjoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
                ->join('units as u', 'p.unit_id', '=', 'u.id')
                ->where('p.business_id', $business_id)
                ->with([ 'ingredients',
                'ingredients.variation',
                'ingredients',
                'ingredients.sub_unit',
                'variation',
                'variation.product',
                'variation.product_variation',
                'sub_unit',
                'variation.product.unit',
                'ingredients.variation.product',])
                ->select(
                    'mfg_recipes.id',
                    DB::raw('IF(
                                            p.type="variable", 
                                            CONCAT(p.name, " - ", pv.name, " - ", v.name, " (", v.sub_sku, ")"), 
                                            CONCAT(p.name, " (", v.sub_sku, ")") 
                                            ) as recipe_name'),
                    'mfg_recipes.extra_cost',
                    'mfg_recipes.final_price',
                    'mfg_recipes.variation_id',
                    'mfg_recipes.total_quantity',
                    'mfg_recipes.production_cost_type',
                    'mfg_recipes.waste_percent',
                    'mfg_recipes.sub_unit_id',
                    'u.short_name as unit_name',
                    'c.name as category',
                    'sc.name as sub_category'
                )
                ->find($id);







            return [
                "recipes" => $recipes
            ];
        }

}
