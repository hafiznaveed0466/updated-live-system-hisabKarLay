<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\ExpenseCategory;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Product;
use App\ProductVariation;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $records = $this->getData('mysql_2', new Business());
        $recordCount = $records->count();
        if ($recordCount > 0) {
            $businessBar = $this->output->createProgressBar($recordCount);
            $businessBar->start();
            foreach ($records as $record) {
                if ($record->owner_id) {
                    $user = $this->getData('mysql_2', new User(), 'id', $record->owner_id, true);
                    $userData = $this->prepareUserData($user);
                    $ownerId = $this->insertData('mysql', 'users', $userData);
                }
                $businessData = $this->prepareBusinessData($record, $ownerId);
                $businessId = $this->insertData('mysql', 'business', $businessData);
                if ($record->owner_id) {
                    $this->updateData('mysql', 'users', 'id', $ownerId, ['business_id' => $businessId]);
                }
                $allUsers = $this->getData('mysql_2', new User(), 'business_id', $record->id);
                $userIDs = [];
                $allContactIds = [];
                if ($allUsers->count() > 0) {
                    $usersBar = $this->output->createProgressBar($allUsers->count());
                    $usersBar->start();
                    foreach ($allUsers as $allUser) {
                        if ($record->owner_id != $allUser->id) {
                            $allUserData = $this->prepareUserData($allUser, $businessId);
                            $insertedUserId = $this->insertData('mysql', 'users', $allUserData);
                        }
                        $contacts = $this->getData('mysql_2', new Contact(), 'created_by', $allUser->id);
                        if ($record->owner_id ==  $allUser->id) {
                            $userId = $ownerId;
                        } else {
                            $userId = $insertedUserId;
                        }
                        $userIDs[$allUser->id] = $userId;
                        if ($contacts->count() > 0) {
                            foreach ($contacts as $contact) {
                                $contactData = $this->prepareContactData($contact, $businessId, $userId);
                                $contactId = $this->insertData('mysql', 'contacts', $contactData);
                                $allContactIds[$contact->id] = $contactId;
                            }
                        }
                        $usersBar->advance();
                    }
                    $usersBar->finish();
                    $this->info(" Users Importing Operation completed!");
                }

                $products = $this->getData('mysql_2', new Product());
                $productIds = [];
                if ($products->count() > 0) {
                    $productsBar = $this->output->createProgressBar($products->count());
                    $productsBar->start();

                    foreach ($products as $product) {
                        $unitRecord = $this->getData('mysql_2', new Unit(), 'id', $product->unit_id, true);
                        $insertedUnitId = null;
                        if ($unitRecord) {
                            $forUnitUserID = $userIDs[$unitRecord->created_by] ?? null;
                            $unitData = $this->prepareUnitData($unitRecord, $businessId, $forUnitUserID);
                            $insertedUnitId = $this->insertOrGet('mysql', 'units', $unitData, 'business_id', $businessId, 'actual_name', $unitRecord->actual_name);
                        }
                        $brandRecord = $this->getData('mysql_2', new Brands(), 'id', $product->brand_id, true);
                        
                        $insertedbrandId = null;
                        if ($brandRecord) {
                            $forBrandUserID = $userIDs[$brandRecord->created_by] ?? null;
                            $brandData = $this->prepareBrandData($brandRecord, $businessId, $forBrandUserID);
                            $insertedbrandId = $this->insertOrGet('mysql', 'brands', $brandData, 'business_id', $businessId, 'name', $brandRecord->name);
                        }
                        $categoryRecord = $this->getData('mysql_2', new Category(), 'id', $product->category_id, true);
                        $insertedcategoryId = null;
                        if ($categoryRecord) {
                            $forCategoryUserID = $userIDs[$categoryRecord->created_by] ?? null;
                            $categoryData = $this->prepareCategoryData($categoryRecord, $businessId, $forCategoryUserID);
                            $insertedcategoryId = $this->insertOrGet('mysql', 'categories', $categoryData, 'business_id', $businessId, 'name', $categoryRecord->name);
                        }
                        $forProductUserID = $userIDs[$product->created_by] ?? null;
                        $productData = $this->prepareProductData($product, $businessId, $insertedUnitId, $insertedbrandId, $insertedcategoryId, $forProductUserID);
                        $product_id = $this->insertData('mysql', 'products', $productData);
                        $productIds[$product->id] = $product_id;

                        $productsBar->start();
                    }
                    $productsBar->finish();
                    $this->info(" Products Importing Operation completed!");
                }
                $businessLocationIds = [];
                $businessLocations = $this->getData('mysql_2', new BusinessLocation());
                $businessLocationBar = $this->output->createProgressBar($businessLocations->count());
                $businessLocationBar->start();
                foreach ($businessLocations as $businessLocation) {
                    $invoiceSchemeRecord = $this->getData('mysql_2', new InvoiceScheme(), 'id', $businessLocation->invoice_scheme_id, true);
                    $invoiceSchemeData = $this->prepareInvoiceSchemeData($invoiceSchemeRecord, $businessId);
                    $invoiceSchemeId = $this->insertOrGet('mysql', 'invoice_schemes', $invoiceSchemeData, 'business_id', $businessId, 'name', $invoiceSchemeRecord->name);

                    $invoiceLayoutRecord = $this->getData('mysql_2', new InvoiceLayout(), 'id', $businessLocation->sale_invoice_layout_id, true);
                    $invoiceLayoutData = $this->prepareInvoiceLayoutData($invoiceLayoutRecord, $businessId);
                    $invoiceLayoutId = $this->insertOrGet('mysql', 'invoice_layouts', $invoiceLayoutData, 'business_id', $businessId, 'name', $invoiceLayoutRecord->name);

                    $businessLocationsData = $this->prepareBusinessLocationData($businessLocation, $businessId, $invoiceSchemeId, $invoiceLayoutId);
                    $businessLocationInsertedId = $this->insertOrGet('mysql', 'business_locations', $businessLocationsData, 'business_id', $businessId, 'location_id', $businessLocation->location_id);
                    $businessLocationIds[$businessLocation->id] = $businessLocationInsertedId;

                    $businessLocationBar->advance();
                }
                $businessLocationBar->finish();
                $this->info(" Business Location Importing Operation completed!");

                $expenseCategorys = $this->getData('mysql_2', new ExpenseCategory());
                $expenseCategoryIds = [];
                $categoryExpenseBar = $this->output->createProgressBar($expenseCategorys->count());
                $categoryExpenseBar->start();
                foreach ($expenseCategorys as $expenseCategory) {
                    $expenseCategoryData = $this->prepareExpenseCategoryData($expenseCategory, $businessId);
                    $expenseCategoryId = $this->insertOrGet('mysql', 'expense_categories', $expenseCategoryData, 'name', $expenseCategory->name, 'business_id', $businessId);
                    $expenseCategoryIds[$expenseCategory->id] = $expenseCategoryId;

                    $categoryExpenseBar->advance();
                }
                $categoryExpenseBar->finish();
                $this->info(" Expense Category Importing Operation completed!");

                $transactions = $this->getData('mysql_2', new Transaction());
                $transactionIds = [];
                $transactionBar = $this->output->createProgressBar($transactions->count());
                $transactionBar->start();
                foreach ($transactions as $transaction) {
                    $businessLocationId = $businessLocationIds[$transaction->location_id] ?? null;
                    $insertedContactId = $allContactIds[$transaction->contact_id] ?? null;
                    $insertedUserIdForTransection = $userIDs[$transaction->created_by] ?? null;
                    $insertedExpenseCatId =  $expenseCategoryIds[$transaction->expense_category_id] ?? null;
                    $expenseForId = $userIDs[$transaction->expense_for] ?? null;
                    $transactionData = $this->prepareTransactionData($transaction, $businessId, $businessLocationId, $insertedContactId, $insertedUserIdForTransection, $insertedExpenseCatId, $expenseForId);
                    $insertedTransectionId = $this->insertOrGet('mysql', 'transactions', $transactionData, 'created_at', $transaction->created_at);
                    $transactionIds[$transaction->id] = $insertedTransectionId;

                    $transactionBar->advance();
                }
                $transactionBar->finish();
                $this->info(" Transactions Importing Operation completed!");

                $product_variations_records = $this->getData('mysql_2', new ProductVariation());
                $productVariationIds = [];
                $productVariationBar = $this->output->createProgressBar($product_variations_records->count());
                $productVariationBar->start();
                foreach ($product_variations_records as $product_variation) {
                    $prouductIdForProductVariation = $productIds[$product_variation->product_id] ?? null;
                    $productVariationData =  $this->prepareProductVariationData($product_variation, $prouductIdForProductVariation);
                    $product_variation_id = $this->insertData('mysql', 'product_variations', $productVariationData);
                    $productVariationIds[$product_variation->id] = $product_variation_id;

                    $productVariationBar->advance();
                }
                $productVariationBar->finish();
                $this->info(" Product Variations Importing Operation completed!");

                $variationsRecords = $this->getData('mysql_2', new Variation());
                $variationIds = [];
                $variationBar = $this->output->createProgressBar($variationsRecords->count());
                $variationBar->start();
                foreach ($variationsRecords as $variation) {
                    $productVariationId = $productVariationIds[$variation->product_variation_id] ?? null;
                    $productIdForVariation = $productIds[$variation->product_id] ?? null;
                    $variationData = $this->prepareVariationData($variation, $productIdForVariation, $productVariationId);
                    $productVariationInsertedId = $this->insertData('mysql', 'variations', $variationData);
                    $variationIds[$variation->id] = $productVariationInsertedId;

                    $variationBar->advance();
                }
                $variationBar->finish();
                $this->info(" Variations Importing Operation completed!");

                $transactionSellLinesRecords = $this->getData('mysql_2', new TransactionSellLine());
                $transactionSellLineBar = $this->output->createProgressBar($transactionSellLinesRecords->count());
                $transactionSellLineBar->start();
                foreach ($transactionSellLinesRecords as $transactionSellLine) {
                    $transactionIdForSellLine = $transactionIds[$transactionSellLine->transaction_id] ?? null;
                    $productIdForSellLine = $productIds[$transactionSellLine->product_id] ?? null;
                    $variationIdForSellLine = $variationIds[$transactionSellLine->variation_id] ?? null;
                    $sellLineData = $this->prepareSellLineData($transactionSellLine, $transactionIdForSellLine, $productIdForSellLine, $variationIdForSellLine);
                    $this->insertData('mysql', 'transaction_sell_lines', $sellLineData);

                    $transactionSellLineBar->advance();
                }
                $transactionSellLineBar->finish();
                $this->info(" Transaction Sell Line Importing Operation completed!");

                $transactionPaymentsRecords = $this->getData('mysql_2', new TransactionPayment());
                $parentIds = [];
                $transactionPaymentBar = $this->output->createProgressBar($transactionPaymentsRecords->count());
                $transactionPaymentBar->start();
                foreach ($transactionPaymentsRecords as $transactionPayment) {
                    $transactionIdForPayment = $transactionIds[$transactionPayment->transaction_id] ?? null;
                    $userIdForPayment = $userIds[$transactionPayment->created_by] ?? null;
                    $contactIdForPayment = $allContactIds[$transactionPayment->payment_for] ?? null;
                    $parentId = $parentIds[$transactionPayment->parent_id] ?? null;
                    $paymentData = $this->preparePaymentData($transactionPayment, $transactionIdForPayment, $businessId, $userIdForPayment, $contactIdForPayment, $parentId);
                    $insertPaymentId = $this->insertData('mysql', 'transaction_payments', $paymentData);
                    $parentids[$transactionPayment->id] = $insertPaymentId;

                    $transactionPaymentBar->advance();
                }
                $transactionPaymentBar->finish();
                $this->info(" Transaction Payment Importing Operation completed!");

                $businessBar->advance();
            }
            $businessBar->finish();
            $this->info(" Importing Operation completed!");
        }
    }

    protected function getData($connection, $model, $where = null, $whereValue = null, $first = false)
    {
        $model->setConnection($connection);
        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses($model->getModel()));

        if ($usesSoftDeletes) {
            $model = $model->withTrashed();
        }

        if ($where && $whereValue) {
            $model->where($where, $whereValue);
        }
        if ($first) {
            return $model->toBase()->first();
        }
        return $model->toBase()->get();
    }
    protected function insertData($connection, $table, $data)
    {
        $insert = DB::connection($connection)->table($table);
        return $insert->insertGetId($data);
    }
    protected function updateData($connection, $table, $column, $value, $data)
    {
        $update = DB::connection($connection)->table($table);
        $update->where($column, $value)->update($data);
        return $update;
    }
    protected function insertOrGet($connection, $table, $data, $where = null, $whereValue = null, $where_1 = null, $where_1_value = null)
    {
        $model = DB::connection($connection)->table($table);
        if ($where && $whereValue) {
            $model->where($where, $whereValue);
            if ($where_1 && $where_1_value) {
                $model->where($where_1, $where_1_value);
            }
            $record = $model->first();
            if ($record) {
                return $record->id;
            }
        }
        $model = DB::connection($connection)->table($table);
        return $model->insertGetId($data);
    }
    protected function prepareBusinessData($record, $ownerId)
    {
        return  [
            'name' => $record->name,
            'currency_id' => $record->currency_id,
            'start_date' => $record->start_date,
            'tax_number_1' => $record->tax_number_1,
            'tax_label_1' => $record->tax_label_1,
            'tax_number_2' => $record->tax_number_2,
            'tax_label_2' => $record->tax_label_2,
            'code_label_1' => $record->code_label_1,
            'code_1' => $record->code_1,
            'code_label_2' => $record->code_label_2,
            'code_2' => $record->code_2,
            'default_sales_tax' => $record->default_sales_tax,
            'default_profit_percent' => $record->default_profit_percent,
            'owner_id' => $ownerId,
            'time_zone' => $record->time_zone,
            'fy_start_month' => $record->fy_start_month,
            'accounting_method' => $record->accounting_method,
            'default_sales_discount' => $record->default_sales_discount,
            'sell_price_tax' => $record->sell_price_tax,
            'logo' => $record->logo,
            'sku_prefix' => $record->sku_prefix,
            'enable_product_expiry' => $record->enable_product_expiry,
            'expiry_type' => $record->expiry_type,
            'on_product_expiry' => $record->on_product_expiry,
            'stop_selling_before' => $record->stop_selling_before,
            'enable_tooltip' => $record->enable_tooltip,
            'purchase_in_diff_currency' => $record->purchase_in_diff_currency,
            'purchase_currency_id' => $record->purchase_currency_id,
            'p_exchange_rate' => $record->p_exchange_rate,
            'transaction_edit_days' => $record->transaction_edit_days,
            'stock_expiry_alert_days' => $record->stock_expiry_alert_days,
            'keyboard_shortcuts' => $record->keyboard_shortcuts,
            'pos_settings' => $record->pos_settings,
            'weighing_scale_setting' => $record->weighing_scale_setting,
            'enable_brand' => $record->enable_brand,
            'enable_category' => $record->enable_category,
            'enable_sub_category' => $record->enable_sub_category,
            'enable_price_tax' => $record->enable_price_tax,
            'enable_purchase_status' => $record->enable_purchase_status,
            'enable_lot_number' => $record->enable_lot_number,
            'default_unit' => $record->default_unit,
            'enable_sub_units' => $record->enable_sub_units,
            'enable_racks' => $record->enable_racks,
            'enable_row' => $record->enable_row,
            'enable_position' => $record->enable_position,
            'enable_editing_product_from_purchase' => $record->enable_editing_product_from_purchase,
            'sales_cmsn_agnt' => $record->sales_cmsn_agnt,
            'item_addition_method' => $record->item_addition_method,
            'enable_inline_tax' => $record->enable_inline_tax,
            'currency_symbol_placement' => $record->currency_symbol_placement,
            'enabled_modules' => $record->enabled_modules,
            'date_format' => $record->date_format,
            'time_format' => $record->time_format,
            'ref_no_prefixes' => $record->ref_no_prefixes,
            'theme_color' => $record->theme_color,
            'created_by' => $record->created_by,
            'enable_rp' => $record->enable_rp,
            'rp_name' => $record->rp_name,
            'amount_for_unit_rp' => $record->amount_for_unit_rp,
            'min_order_total_for_rp' => $record->min_order_total_for_rp,
            'max_rp_per_order' => $record->max_rp_per_order,
            'redeem_amount_per_unit_rp' => $record->redeem_amount_per_unit_rp,
            'min_order_total_for_redeem' => $record->min_order_total_for_redeem,
            'min_redeem_point' => $record->min_redeem_point,
            'max_redeem_point' => $record->max_redeem_point,
            'rp_expiry_period' => $record->rp_expiry_period,
            'rp_expiry_type' => $record->rp_expiry_type,
            'email_settings' => $record->email_settings,
            'sms_settings' => $record->sms_settings,
            'custom_labels' => $record->custom_labels,
            'common_settings' => $record->common_settings,
            'is_active' => $record->is_active,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }
    protected function prepareUserData($user, $businessId = null)
    {
        
        return [
            'user_type' => $user->user_type,
            'surname' => $user->surname,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'language' => $user->language,
            'contact_no' => $user->contact_no,
            'address' => $user->address,
            'remember_token' => $user->remember_token,
            'business_id' => $businessId ? $businessId : null,
            'max_sales_discount_percent' => $user->max_sales_discount_percent,
            'allow_login' => $user->allow_login,
            'status' => $user->status,
            'crm_contact_id' => $user->crm_contact_id,
            'is_cmmsn_agnt' => $user->is_cmmsn_agnt,
            'cmmsn_percent' => $user->cmmsn_percent,
            'selected_contacts' => $user->selected_contacts,
            'dob' => $user->dob,
            'gender' => $user->gender,
            'marital_status' => $user->marital_status,
            'blood_group' => $user->blood_group,
            'contact_number' => $user->contact_number,
            'alt_number' => $user->alt_number,
            'family_number' => $user->family_number,
            'fb_link' => $user->fb_link,
            'twitter_link' => $user->twitter_link,
            'social_media_1' => $user->social_media_1,
            'social_media_2' => $user->social_media_2,
            'permanent_address' => $user->permanent_address,
            'current_address' => $user->current_address,
            'guardian_name' => $user->guardian_name,
            'custom_field_1' => $user->custom_field_1,
            'custom_field_2' => $user->custom_field_2,
            'custom_field_3' => $user->custom_field_3,
            'custom_field_4' => $user->custom_field_4,
            'bank_details' => $user->bank_details,
            'id_proof_name' => $user->id_proof_name,
            'id_proof_number' => $user->id_proof_number,
            'deleted_at' => $user->deleted_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
    protected function prepareContactData($contact, $businessId, $ownerId)
    {
        return  [
            'business_id' => $businessId,
            'type' => $contact->type,
            'supplier_business_name' => $contact->supplier_business_name,
            'name' => $contact->name,
            'prefix' => $contact->prefix,
            'first_name' => $contact->first_name,
            'middle_name' => $contact->middle_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'contact_id' => $contact->contact_id,
            'contact_status' => $contact->contact_status,
            'tax_number' => $contact->tax_number,
            'city' => $contact->city,
            'state' => $contact->state,
            'country' => $contact->country,
            'address_line_1' => $contact->address_line_1,
            'address_line_2' => $contact->address_line_2,
            'zip_code' => $contact->zip_code,
            'dob' => $contact->dob,
            'mobile' => $contact->mobile,
            'landline' => $contact->landline,
            'alternate_number' => $contact->alternate_number,
            'pay_term_number' => $contact->pay_term_number,
            'pay_term_type' => $contact->pay_term_type,
            'credit_limit' => $contact->credit_limit,
            'created_by' => $ownerId,
            'balance' => $contact->balance,
            'total_rp' => $contact->total_rp,
            'total_rp_used' => $contact->total_rp_used,
            'total_rp_expired' => $contact->total_rp_expired,
            'is_default' => $contact->is_default,
            'shipping_address' => $contact->shipping_address,
            'shipping_custom_field_details' => $contact->shipping_custom_field_details,
            'is_export' => $contact->is_export,
            'export_custom_field_1' => $contact->export_custom_field_1,
            'export_custom_field_2' => $contact->export_custom_field_2,
            'export_custom_field_3' => $contact->export_custom_field_3,
            'export_custom_field_4' => $contact->export_custom_field_4,
            'export_custom_field_5' => $contact->export_custom_field_5,
            'export_custom_field_6' => $contact->export_custom_field_6,
            'position' => $contact->position,
            'customer_group_id' => $contact->customer_group_id,
            'custom_field1' => $contact->custom_field1,
            'custom_field2' => $contact->custom_field2,
            'custom_field3' => $contact->custom_field3,
            'custom_field4' => $contact->custom_field4,
            'custom_field5' => $contact->custom_field5,
            'custom_field6' => $contact->custom_field6,
            'custom_field7' => $contact->custom_field7,
            'custom_field8' => $contact->custom_field8,
            'custom_field9' => $contact->custom_field9,
            'custom_field10' => $contact->custom_field10,
            'deleted_at' => $contact->deleted_at,
            'created_at' => $contact->created_at,
            'updated_at' => $contact->updated_at,
        ];
    }
    protected function prepareUnitData($unit, $businessiId, $userId)
    {
        return [
            'business_id' => $businessiId,
            'actual_name' => $unit->actual_name,
            'short_name' => $unit->short_name,
            'allow_decimal' => $unit->allow_decimal,
            'base_unit_id' => $unit->base_unit_id,
            'base_unit_multiplier' => $unit->base_unit_multiplier,
            'created_by' => $userId,
            'deleted_at' => $unit->deleted_at,
            'created_at' => $unit->created_at,
            'updated_at' => $unit->updated_at
        ];
    }
    protected function prepareBrandData($brand, $businessiId, $userId)
    {
        return [
            'business_id' => $businessiId,
            'name' => $brand->name,
            'description' => $brand->description,
            'created_by' => $userId,
            'deleted_at' => $brand->deleted_at,
            'created_at' => $brand->created_at,
            'updated_at' => $brand->updated_at
        ];
    }
    protected function prepareCategoryData($cateogory, $businessiId, $userId)
    {
        return [
            'name' => $cateogory->name,
            'business_id' => $businessiId,
            'short_code' => $cateogory->short_code,
            'parent_id' => $cateogory->parent_id,
            'created_by' => $userId,
            'category_type' => $cateogory->category_type,
            'description' => $cateogory->description,
            'slug' => $cateogory->slug,
            'deleted_at' => $cateogory->deleted_at,
            'created_at' => $cateogory->created_at,
            'updated_at' => $cateogory->updated_at
        ];
    }
    protected function prepareProductData($product, $businessId, $unitId, $brandId, $categoryId, $userId)
    {
        return [
            'name' => $product->name,
            'business_id' => $businessId,
            'type' => $product->type,
            'unit_id' => $unitId,
            'sub_unit_ids' => $product->sub_unit_ids,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'sub_category_id' => $product->sub_category_id,
            'tax' => $product->tax,
            'tax_type' => $product->tax_type,
            'enable_stock' => $product->enable_stock,
            'alert_quantity' => $product->alert_quantity,
            'sku' => $product->sku,
            'barcode_type' => $product->barcode_type,
            'expiry_period' => $product->expiry_period,
            'expiry_period_type' => $product->expiry_period_type,
            'enable_sr_no' => $product->enable_sr_no,
            'weight' => $product->weight,
            'product_custom_field1' => $product->product_custom_field1,
            'product_custom_field2' => $product->product_custom_field2,
            'product_custom_field3' => $product->product_custom_field3,
            'product_custom_field4' => $product->product_custom_field4,
            'image' => $product->image,
            'product_description' => $product->product_description,
            'created_by' => $userId,
            'warranty_id' => $product->warranty_id,
            'is_inactive' => $product->is_inactive,
            'not_for_selling' => $product->not_for_selling,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at
        ];
    }
    protected function prepareBusinessLocationData($businessLocation, $businessId, $shemeId, $layoutId)
    {
        return [
            'business_id' => $businessId,
            'location_id' => $businessLocation->location_id,
            'name' => $businessLocation->name,
            'landmark' => $businessLocation->landmark, 
            'country' => $businessLocation->country,
            'state' => $businessLocation->state,
            'city' => $businessLocation->city,
            'zip_code' => $businessLocation->zip_code,
            'invoice_scheme_id' => $shemeId,
            'invoice_layout_id' => $layoutId,
            'sale_invoice_layout_id' => $businessLocation->sale_invoice_layout_id,
            'selling_price_group_id' => $businessLocation->selling_price_group_id,
            'print_receipt_on_invoice' => $businessLocation->print_receipt_on_invoice,
            'receipt_printer_type' => $businessLocation->receipt_printer_type,
            'printer_id' => $businessLocation->printer_id,
            'mobile' => $businessLocation->mobile,
            'alternate_number' => $businessLocation->alternate_number,
            'email' => $businessLocation->email,
            'website' => $businessLocation->website,
            'featured_products' => $businessLocation->featured_products,
            'is_active' => $businessLocation->is_active,
            'default_payment_accounts' => $businessLocation->default_payment_accounts,
            'custom_field1' => $businessLocation->custom_field1,
            'custom_field2' => $businessLocation->custom_field2,
            'custom_field3' => $businessLocation->custom_field3,
            'custom_field4' => $businessLocation->custom_field4,
            'deleted_at' => $businessLocation->deleted_at,
            'created_at' => $businessLocation->created_at,
            'updated_at' => $businessLocation->updated_at
        ];
    }
    protected function prepareInvoiceSchemeData($invoiceScheme, $businessId)
    {
        return [
            'business_id' => $businessId,
            'name' => $invoiceScheme->name,
            'scheme_type' => $invoiceScheme->scheme_type,
            'prefix' => $invoiceScheme->prefix,
            'start_number' => $invoiceScheme->start_number,
            'invoice_count' => $invoiceScheme->invoice_count,
            'total_digits' => $invoiceScheme->total_digits,
            'is_default' => $invoiceScheme->is_default,
            'created_at' => $invoiceScheme->created_at,
            'updated_at' => $invoiceScheme->updated_at
        ];
    }
    protected function prepareInvoiceLayoutData($invoiceLayout, $businessId)
    {
        return [
            'name' => $invoiceLayout->name,
            'header_text' => $invoiceLayout->header_text,
            'invoice_no_prefix' => $invoiceLayout->invoice_no_prefix,
            'quotation_no_prefix' => $invoiceLayout->quotation_no_prefix,
            'invoice_heading' => $invoiceLayout->invoice_heading,
            'sub_heading_line1' => $invoiceLayout->sub_heading_line1,
            'sub_heading_line2' => $invoiceLayout->sub_heading_line2,
            'sub_heading_line3' => $invoiceLayout->sub_heading_line3,
            'sub_heading_line4' => $invoiceLayout->sub_heading_line4,
            'sub_heading_line5' => $invoiceLayout->sub_heading_line5,
            'invoice_heading_not_paid' => $invoiceLayout->invoice_heading_not_paid,
            'invoice_heading_paid' => $invoiceLayout->invoice_heading_paid,
            'quotation_heading' => $invoiceLayout->quotation_heading,
            'sub_total_label' => $invoiceLayout->sub_total_label,
            'discount_label' => $invoiceLayout->discount_label,
            'tax_label' => $invoiceLayout->tax_label,
            'total_label' => $invoiceLayout->total_label,
            'round_off_label' => $invoiceLayout->round_off_label,
            'total_due_label' => $invoiceLayout->total_due_label,
            'paid_label' => $invoiceLayout->paid_label,
            'show_client_id' => $invoiceLayout->show_client_id,
            'client_id_label' => $invoiceLayout->client_id_label,
            'client_tax_label' => $invoiceLayout->client_tax_label,
            'date_label' => $invoiceLayout->date_label,
            'date_time_format' => $invoiceLayout->date_time_format,
            'show_time' => $invoiceLayout->show_time,
            'show_brand' => $invoiceLayout->show_brand,
            'show_sku' => $invoiceLayout->show_sku,
            'show_cat_code' => $invoiceLayout->show_cat_code,
            'show_expiry' => $invoiceLayout->show_expiry,
            'show_lot' => $invoiceLayout->show_lot,
            'show_image' => $invoiceLayout->show_image,
            'show_sale_description' => $invoiceLayout->show_sale_description,
            'sales_person_label' => $invoiceLayout->sales_person_label,
            'show_sales_person' => $invoiceLayout->show_sales_person,
            'table_product_label' => $invoiceLayout->table_product_label,
            'table_qty_label' => $invoiceLayout->table_qty_label,
            'table_unit_price_label' => $invoiceLayout->table_unit_price_label,
            'table_subtotal_label' => $invoiceLayout->table_subtotal_label,
            'cat_code_label' => $invoiceLayout->cat_code_label,
            'logo' => $invoiceLayout->logo,
            'show_logo' => $invoiceLayout->show_logo,
            'show_business_name' => $invoiceLayout->show_business_name,
            'show_location_name' => $invoiceLayout->show_location_name,
            'show_landmark' => $invoiceLayout->show_landmark,
            'show_city' => $invoiceLayout->show_city,
            'show_state' => $invoiceLayout->show_state,
            'show_zip_code' => $invoiceLayout->show_zip_code,
            'show_country' => $invoiceLayout->show_country,
            'show_mobile_number' => $invoiceLayout->show_mobile_number,
            'show_alternate_number' => $invoiceLayout->show_alternate_number,
            'show_email' => $invoiceLayout->show_email,
            'show_tax_1' => $invoiceLayout->show_tax_1,
            'show_tax_2' => $invoiceLayout->show_tax_2,
            'show_barcode' => $invoiceLayout->show_barcode,
            'show_payments' => $invoiceLayout->show_payments,
            'show_customer' => $invoiceLayout->show_customer,
            'customer_label' => $invoiceLayout->customer_label,
            'commission_agent_label' => $invoiceLayout->commission_agent_label,
            'show_commission_agent' => $invoiceLayout->show_commission_agent,
            'show_reward_point' => $invoiceLayout->show_reward_point,
            'highlight_color' => $invoiceLayout->highlight_color,
            'footer_text' => $invoiceLayout->footer_text,
            'module_info' => $invoiceLayout->module_info,
            'common_settings' => $invoiceLayout->common_settings,
            'is_default' => $invoiceLayout->is_default,
            'business_id' => $businessId,
            'show_qr_code' => $invoiceLayout->show_qr_code,
            'qr_code_fields' => $invoiceLayout->qr_code_fields,
            'design' => $invoiceLayout->design,
            'cn_heading' => $invoiceLayout->cn_heading,
            'cn_no_label' => $invoiceLayout->cn_no_label,
            'cn_amount_label' => $invoiceLayout->cn_amount_label,
            'table_tax_headings' => $invoiceLayout->table_tax_headings,
            'show_previous_bal' => $invoiceLayout->show_previous_bal,
            'prev_bal_label' => $invoiceLayout->prev_bal_label,
            'change_return_label' => $invoiceLayout->change_return_label,
            'product_custom_fields' => $invoiceLayout->product_custom_fields,
            'contact_custom_fields' => $invoiceLayout->contact_custom_fields,
            'location_custom_fields' => $invoiceLayout->location_custom_fields,
            'created_at' => $invoiceLayout->created_at,
            'updated_at' => $invoiceLayout->updated_at,
        ];
    }

    protected function prepareTransactionData($transaction, $businessId, $locationId, $contactId, $userId, $expenseCatId, $expenseForId)
    {
        return [
            'business_id' => $businessId,
            'location_id' => $locationId,
            'res_table_id' => $transaction->res_table_id,
            'res_waiter_id' => $transaction->res_waiter_id,
            'res_order_status' => $transaction->res_order_status,
            'type' => $transaction->type,
            'sub_type' => $transaction->sub_type,
            'status' => $transaction->status,
            'sub_status' => $transaction->sub_status,
            'is_quotation' => $transaction->is_quotation,
            'payment_status' => $transaction->payment_status,
            'adjustment_type' => $transaction->adjustment_type,
            'contact_id' => $contactId,
            'customer_group_id' => $transaction->customer_group_id,
            'invoice_no' => $transaction->invoice_no,
            'ref_no' => $transaction->ref_no,
            'subscription_no' => $transaction->subscription_no,
            'subscription_repeat_on' => $transaction->subscription_repeat_on,
            'transaction_date' => $transaction->transaction_date,
            'total_before_tax' => $transaction->total_before_tax,
            'tax_id' => $transaction->tax_id,
            'tax_amount' => $transaction->tax_amount,
            'discount_type' => $transaction->discount_type,
            'discount_amount' => $transaction->discount_amount,
            'rp_redeemed' => $transaction->rp_redeemed,
            'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
            'shipping_details' => $transaction->shipping_details,
            'shipping_address' => $transaction->shipping_address,
            'shipping_status' => $transaction->shipping_status,
            'delivered_to' => $transaction->delivered_to,
            'shipping_charges' => $transaction->shipping_charges,
            'shipping_custom_field_1' => $transaction->shipping_custom_field_1,
            'shipping_custom_field_2' => $transaction->shipping_custom_field_2,
            'shipping_custom_field_3' => $transaction->shipping_custom_field_3,
            'shipping_custom_field_4' => $transaction->shipping_custom_field_4,
            'shipping_custom_field_5' => $transaction->shipping_custom_field_5,
            'additional_notes' => $transaction->additional_notes,
            'staff_note' => $transaction->staff_note,
            'is_export' => $transaction->is_export,
            'export_custom_fields_info' => $transaction->export_custom_fields_info,
            'round_off_amount' => $transaction->round_off_amount,
            'additional_expense_key_1' => $transaction->additional_expense_key_1,
            'additional_expense_value_1' => $transaction->additional_expense_value_1,
            'additional_expense_key_2' => $transaction->additional_expense_key_2,
            'additional_expense_value_2' => $transaction->additional_expense_value_2,
            'additional_expense_key_3' => $transaction->additional_expense_key_3,
            'additional_expense_value_3' => $transaction->additional_expense_value_3,
            'additional_expense_key_4' => $transaction->additional_expense_key_4,
            'additional_expense_value_4' => $transaction->additional_expense_value_4,
            'final_total' => $transaction->final_total,
            'expense_category_id' => $expenseCatId,
            'expense_for' => $expenseForId,
            'commission_agent' => $transaction->commission_agent,
            'document' => $transaction->document,
            'is_direct_sale' => $transaction->is_direct_sale,
            'is_suspend' => $transaction->is_suspend,
            'exchange_rate' => $transaction->exchange_rate,
            'total_amount_recovered' => $transaction->total_amount_recovered,
            'transfer_parent_id' => $transaction->transfer_parent_id,
            'return_parent_id' => $transaction->return_parent_id,
            'opening_stock_product_id' => $transaction->opening_stock_product_id,
            'created_by' => $userId,
            'prefer_payment_method' => $transaction->prefer_payment_method,
            'prefer_payment_account' => $transaction->prefer_payment_account,
            'sales_order_ids' => $transaction->sales_order_ids,
            'purchase_order_ids' => $transaction->purchase_order_ids,
            'custom_field_1' => $transaction->custom_field_1,
            'custom_field_2' => $transaction->custom_field_2,
            'custom_field_3' => $transaction->custom_field_3,
            'custom_field_4' => $transaction->custom_field_4,
            'import_batch' => $transaction->import_batch,
            'import_time' => $transaction->import_time,
            'types_of_service_id' => $transaction->types_of_service_id,
            'packing_charge' => $transaction->packing_charge,
            'packing_charge_type' => $transaction->packing_charge_type,
            'service_custom_field_1' => $transaction->service_custom_field_1,
            'service_custom_field_2' => $transaction->service_custom_field_2,
            'service_custom_field_3' => $transaction->service_custom_field_3,
            'service_custom_field_4' => $transaction->service_custom_field_4,
            'service_custom_field_5' => $transaction->service_custom_field_5,
            'service_custom_field_6' => $transaction->service_custom_field_6,
            'is_created_from_api' => $transaction->is_created_from_api,
            'rp_earned' => $transaction->rp_earned,
            'order_addresses' => $transaction->order_addresses,
            'is_recurring' => $transaction->is_recurring,
            'recur_interval' => $transaction->recur_interval,
            'recur_interval_type' => $transaction->recur_interval_type,
            'recur_repetitions' => $transaction->recur_repetitions,
            'recur_stopped_on' => $transaction->recur_stopped_on,
            'recur_parent_id' => $transaction->recur_parent_id,
            'invoice_token' => $transaction->invoice_token,
            'pay_term_number' => $transaction->pay_term_number,
            'pay_term_type' => $transaction->pay_term_type,
            'selling_price_group_id' => $transaction->selling_price_group_id,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at

        ];
    }
    protected function prepareExpenseCategoryData($expenseCategory, $businessId)
    {
        return [
            'name' => $expenseCategory->name,
            'business_id' => $businessId,
            'code' => $expenseCategory->code,
            'deleted_at' => $expenseCategory->deleted_at,
            'created_at' => $expenseCategory->created_at,
            'updated_at' => $expenseCategory->updated_at
        ];
    }
    protected function prepareProductVariationData($productVariation, $productId)
    {
        return [
            'variation_template_id' => $productVariation->variation_template_id,
            'name' => $productVariation->name,
            'product_id' => $productId,
            'is_dummy' => $productVariation->is_dummy,
            'created_at' => $productVariation->created_at,
            'updated_at' => $productVariation->updated_at
        ];
    }
    protected function prepareVariationData($variation, $productId, $productVariationId)
    {
        return  [
            'name' => $variation->name,
            'product_id' => $productId,
            'sub_sku' => $variation->sub_sku,
            'product_variation_id' => $productVariationId,
            'variation_value_id' => $variation->variation_value_id,
            'default_purchase_price' => $variation->default_purchase_price,
            'dpp_inc_tax' => $variation->dpp_inc_tax,
            'profit_percent' => $variation->profit_percent,
            'default_sell_price' => $variation->default_sell_price,
            'sell_price_inc_tax' => $variation->sell_price_inc_tax,
            'created_at' => $variation->created_at,
            'updated_at' => $variation->updated_at,
            'deleted_at' => $variation->deleted_at,
            'combo_variations' => $variation->combo_variations
        ];
    }
    protected function prepareSellLineData($sellLine, $transactionId, $productId, $variationId)
    {
        return [
            'transaction_id' => $transactionId,
            'product_id' => $productId,
            'variation_id' => $variationId,
            'quantity' => $sellLine->quantity,
            'quantity_returned' => $sellLine->quantity_returned,
            'unit_price_before_discount' => $sellLine->unit_price_before_discount,
            'unit_price' => $sellLine->unit_price,
            'line_discount_type' => $sellLine->line_discount_type,
            'line_discount_amount' => $sellLine->line_discount_amount,
            'unit_price_inc_tax' => $sellLine->unit_price_inc_tax,
            'item_tax' => $sellLine->item_tax,
            'tax_id' => $sellLine->tax_id,
            'discount_id' => $sellLine->discount_id,
            'lot_no_line_id' => $sellLine->lot_no_line_id,
            'sell_line_note' => $sellLine->sell_line_note,
            'so_line_id' => $sellLine->so_line_id,
            'so_quantity_invoiced' => $sellLine->so_quantity_invoiced,
            'res_service_staff_id' => $sellLine->res_service_staff_id,
            'res_line_order_status' => $sellLine->res_line_order_status,
            'parent_sell_line_id' => $sellLine->parent_sell_line_id,
            'children_type' => $sellLine->children_type,
            'sub_unit_id' => $sellLine->sub_unit_id,
            'created_at' => $sellLine->created_at,
            'updated_at' => $sellLine->updated_at
        ];
    }
    protected function preparePaymentData($payment, $transactionId, $businessId, $userId, $contactId, $parentId)
    {
        return [
            'transaction_id' => $transactionId,
            'business_id' => $businessId,
            'is_return' => $payment->is_return,
            'amount' => $payment->amount,
            'method' => $payment->method,
            'transaction_no' => $payment->transaction_no,
            'card_transaction_number' => $payment->card_transaction_number,
            'card_number' => $payment->card_number,
            'card_type' => $payment->card_type,
            'card_holder_name' => $payment->card_holder_name,
            'card_month' => $payment->card_month,
            'card_year' => $payment->card_year,
            'card_security' => $payment->card_security,
            'cheque_number' => $payment->cheque_number,
            'bank_account_number' => $payment->bank_account_number,
            'paid_on' => $payment->paid_on,
            'created_by' => $userId,
            'is_advance' => $payment->is_advance,
            'payment_for' => $contactId,
            'parent_id' => $parentId,
            'note' => $payment->note,
            'document' => $payment->document,
            'payment_ref_no' => $payment->payment_ref_no,
            'account_id' => $payment->account_id,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at
        ];
    }
}
