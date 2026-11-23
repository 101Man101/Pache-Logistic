<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentCharge;
use App\Models\StorageBin;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\Warehouse;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacheNonCodErpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_landing_and_multi_awb_tracking_with_pii_masking(): void
    {
        $this->get('/')->assertOk();

        // Default tracking masks recipient phone & address
        $this->get('/tracking?awb=PCH-JKT-261121-00001')
            ->assertOk()
            ->assertSee('PCH-JKT-261121-00001');

        // Providing correct last 4 digits unlocks full recipient info
        $this->get('/tracking?awb=PCH-JKT-261121-00001&phone_last4=7890')
            ->assertOk()
            ->assertSee('RS Graha Husada Surabaya');
    }

    public function test_strictly_non_cod_policy_rejects_cod_payment_method(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        $cust = Customer::query()->where('code', 'CUST-RTL-001')->firstOrFail();

        $response = $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Penerima Uji',
            'consignee_phone' => '081200001111',
            'consignee_address' => 'Jl. Tunjungan No. 1, Surabaya',
            'service_type' => 'REG',
            'payment_method' => 'cod', // Forbidden!
            'items' => [
                ['description' => 'Paket Uji', 'qty' => 1, 'actual_weight_kg' => 2, 'length_cm' => 30, 'width_cm' => 20, 'height_cm' => 20],
            ],
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_br01_chargeable_weight_and_br08_non_cod_dispatch_gate(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        $cust = Customer::query()->where('code', 'CUST-RTL-001')->firstOrFail();

        // Volumetric = (60*50*40)/6000 = 20 kg > actual 5 kg => chargeable = 20 kg
        $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Toko Online Surabaya',
            'consignee_phone' => '081299998888',
            'consignee_address' => 'Jl. Pemuda No. 10, Surabaya',
            'service_type' => 'REG',
            'payment_method' => PaymentMethod::ONLINE->value,
            'items' => [
                ['description' => 'Karton Ringan Besar', 'qty' => 1, 'actual_weight_kg' => 5, 'length_cm' => 60, 'width_cm' => 50, 'height_cm' => 40],
            ],
        ])->assertRedirect();

        /** @var Shipment $shipment */
        $shipment = Shipment::query()->latest('id')->firstOrFail();
        $this->assertEquals(20.0, (float) $shipment->chargeable_weight_kg);
        $this->assertEquals(PaymentStatus::PENDING, $shipment->payment_status);

        // BR-08: Unpaid ONLINE shipment MUST be blocked from Scan Bagging into Manifest
        /** @var Manifest $openManifest */
        $openManifest = Manifest::query()->where('status', 'open')->firstOrFail();
        $this->actingAs($admin)
            ->post("/tms/manifests/{$openManifest->id}/scan", ['awb_number' => $shipment->awb_number])
            ->assertSessionHas('error');

        // After confirming online webhook payment => PAID, Scan Bagging succeeds!
        $this->actingAs($admin)
            ->post("/oms/shipments/{$shipment->awb_number}/confirm-payment")
            ->assertSessionHas('success');

        $this->assertEquals(PaymentStatus::PAID, $shipment->refresh()->payment_status);

        $this->actingAs($admin)
            ->post("/tms/manifests/{$openManifest->id}/scan", ['awb_number' => $shipment->awb_number])
            ->assertSessionHas('success');
    }

    public function test_br02_credit_limit_blocks_top_order_when_exceeded(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        /** @var Customer $cust */
        $cust = Customer::query()->where('code', 'CUST-B2B-003')->firstOrFail();
        $cust->update(['credit_limit' => 10000]); // Tiny limit to trigger BR-02

        $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Gudang Surabaya',
            'consignee_phone' => '081233334444',
            'consignee_address' => 'Surabaya',
            'service_type' => 'EXP',
            'payment_method' => PaymentMethod::TOP->value,
            'items' => [
                ['description' => 'Server Rack', 'qty' => 1, 'actual_weight_kg' => 10, 'length_cm' => 40, 'width_cm' => 40, 'height_cm' => 40],
            ],
        ])->assertSessionHas('error');
    }

    public function test_br04_wms_ledger_blocks_negative_stock(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $wh = Warehouse::query()->where('code', 'WH-JKT-01')->firstOrFail();
        $bin = StorageBin::query()->where('code', 'DRY-A1-R1-B01')->firstOrFail();
        $prod = Product::query()->where('sku', 'SKU-MED-101')->firstOrFail();

        // Available balance is 200; requesting 500 outbound MUST be rejected
        $this->actingAs($admin)->post('/wms/inventory/mutate', [
            'warehouse_id' => $wh->id,
            'storage_bin_id' => $bin->id,
            'product_id' => $prod->id,
            'movement_type' => 'outbound',
            'quantity' => 500,
            'reference_code' => 'PICK-ERR-001',
        ])->assertSessionHas('error');
    }

    public function test_br06_blocks_manifest_with_expired_kir_vehicle_and_top_auto_invoicing_works(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        $expiredVehicle = Vehicle::query()->where('plate_number', 'L 8821 PXS')->firstOrFail();
        $driver = Driver::query()->where('sim_number', 'SIM-B2-001')->firstOrFail();

        $this->actingAs($admin)->post('/tms/manifests', [
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'vehicle_id' => $expiredVehicle->id,
            'driver_id' => $driver->id,
        ])->assertSessionHas('error');

        // Auto-Invoicing B2B TOP for PT Sinar Baja Industri (has delivered unbilled TOP shipment 00005)
        $b2b = Customer::query()->where('code', 'CUST-B2B-002')->firstOrFail();
        $this->actingAs($admin)->post('/finance/invoices/generate', [
            'customer_id' => $b2b->id,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $b2b->id,
            'status' => 'unpaid',
        ]);

        // Ensure dashboard loads cleanly
        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->assertEquals(ShipmentStatus::DELIVERED, Shipment::query()->where('awb_number', 'PCH-SBY-261121-00005')->firstOrFail()->status);
    }

    public function test_rate_settings_surcharge_vendor_and_add_charge_workflow(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        $cust = Customer::query()->where('code', 'CUST-RTL-001')->firstOrFail();

        // 1. Configure route rate + route surcharge settings in MDM
        $this->actingAs($admin)->post('/master/tariffs', [
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'service_type' => 'REG',
            'volumetric_divisor' => 6000,
            'min_weight_kg' => 1,
            'rate_per_kg' => 20000,
            'insurance_rate_pct' => 0.25,
            'fuel_surcharge_pct' => 10,
            'handling_fee' => 15000,
            'heavy_weight_threshold_kg' => 15,
            'heavy_weight_surcharge_per_kg' => 2000,
            'sla_days' => 2,
            'is_active' => true,
        ])->assertSessionHas('success');

        // 2. Create Vendor & Surcharge in MDM
        $this->actingAs($admin)->post('/master/vendors', [
            'code' => 'VND-TST-01',
            'name' => 'PT Vendor Penguji Nusantara',
            'type' => 'linehaul',
            'phone' => '021-9988-7766',
            'payment_terms_days' => 14,
            'base_cost_per_kg' => 7000,
        ])->assertSessionHas('success');

        $this->actingAs($admin)->post('/master/surcharges', [
            'code' => 'SUR-TST',
            'name' => 'Packing Khusus Test',
            'calc_mode' => 'fixed',
            'amount' => 30000,
        ])->assertSessionHas('success');

        $vendor = Vendor::query()->where('code', 'VND-TST-01')->firstOrFail();
        $surcharge = Surcharge::query()->where('code', 'SUR-TST')->firstOrFail();

        // 3. Book shipment with 20 kg (> 15 kg heavy threshold), vendor, preset surcharge & add charge
        // Base shipping = 20 * 20,000 = 400,000
        // Route FSC (10%) = 40,000; Handling = 15,000; Heavy (5 kg * 2,000) = 10,000; Preset SUR-TST = 30,000 => Surcharge = 95,000
        // Add Charge = 25,000
        // Total = 400,000 + 95,000 + 25,000 = 520,000
        $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'vendor_id' => $vendor->id,
            'vendor_awb_no' => 'VAWB-9910',
            'vendor_cost' => 140000,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Penerima Surcharge Test',
            'consignee_phone' => '081277776666',
            'consignee_address' => 'Surabaya',
            'service_type' => 'REG',
            'payment_method' => PaymentMethod::ONLINE->value,
            'surcharge_ids' => [$surcharge->id],
            'additional_charges' => [
                ['description' => 'Biaya Parkir Muat', 'amount' => 25000],
            ],
            'items' => [
                ['description' => 'Mesin Kemasan', 'qty' => 1, 'actual_weight_kg' => 20, 'length_cm' => 30, 'width_cm' => 30, 'height_cm' => 30],
            ],
        ])->assertRedirect();

        /** @var Shipment $shipment */
        $shipment = Shipment::query()->latest('id')->firstOrFail();
        $this->assertEquals($vendor->id, $shipment->vendor_id);
        $this->assertEquals('VAWB-9910', $shipment->vendor_awb_no);
        $this->assertEquals(140000.0, (float) $shipment->vendor_cost);
        $this->assertEquals(400000.0, (float) $shipment->shipping_cost);
        $this->assertEquals(95000.0, (float) $shipment->surcharge_cost);
        $this->assertEquals(25000.0, (float) $shipment->additional_charge_cost);
        $this->assertEquals(520000.0, (float) $shipment->total_amount);

        // 4. Post-booking Add Charge + Remove Charge
        $this->actingAs($admin)->post("/oms/shipments/{$shipment->awb_number}/charges", [
            'charge_type' => 'add_charge',
            'description' => 'Biaya Tunggu Bongkar',
            'amount' => 50000,
        ])->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals(75000.0, (float) $shipment->additional_charge_cost);
        $this->assertEquals(570000.0, (float) $shipment->total_amount);

        /** @var ShipmentCharge $added */
        $added = $shipment->charges()->where('description', 'Biaya Tunggu Bongkar')->firstOrFail();
        $this->actingAs($admin)->delete("/oms/shipments/{$shipment->awb_number}/charges/{$added->id}")
            ->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals(520000.0, (float) $shipment->total_amount);
        $this->assertTrue(Tariff::query()->where('origin_branch_id', $jkt->id)->where('destination_branch_id', $sby->id)->where('service_type', 'REG')->exists());
    }

    public function test_rbac_blocks_unauthorized_roles_and_allows_authorized_modules(): void
    {
        $superAdmin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();
        $oms = User::query()->where('email', 'oms@pachelogistic.id')->firstOrFail();
        $wms = User::query()->where('email', 'wms@pachelogistic.id')->firstOrFail();
        $tms = User::query()->where('email', 'tms@pachelogistic.id')->firstOrFail();
        $kasir = User::query()->where('email', 'kasir@pachelogistic.id')->firstOrFail();

        $this->actingAs($superAdmin)->get('/oms/shipments/create')->assertOk();
        $this->actingAs($superAdmin)->get('/wms/inventory')->assertOk();
        $this->actingAs($superAdmin)->get('/tms/manifests')->assertOk();
        $this->actingAs($superAdmin)->get('/finance')->assertOk();
        $this->actingAs($superAdmin)->get('/master')->assertOk();

        $this->actingAs($oms)->get('/oms/shipments/create')->assertOk();
        $this->actingAs($oms)->get('/master')->assertOk();
        $this->actingAs($oms)->get('/wms/inventory')->assertForbidden();
        $this->actingAs($oms)->get('/tms/manifests')->assertForbidden();
        $this->actingAs($oms)->get('/finance')->assertForbidden();

        $this->actingAs($wms)->get('/dashboard')->assertOk();
        $this->actingAs($wms)->get('/wms/inventory')->assertOk();
        $this->actingAs($wms)->get('/oms/shipments/create')->assertForbidden();
        $this->actingAs($wms)->get('/tms/manifests')->assertForbidden();
        $this->actingAs($wms)->get('/finance')->assertForbidden();
        $this->actingAs($wms)->get('/master')->assertForbidden();

        $this->actingAs($tms)->get('/tms/manifests')->assertOk();
        $this->actingAs($tms)->get('/oms/shipments')->assertOk();
        $this->actingAs($tms)->get('/oms/shipments/create')->assertForbidden();
        $this->actingAs($tms)->get('/wms/inventory')->assertForbidden();
        $this->actingAs($tms)->get('/finance')->assertForbidden();
        $this->actingAs($tms)->get('/master')->assertForbidden();

        $this->actingAs($kasir)->get('/finance')->assertOk();
        $this->actingAs($kasir)->get('/master')->assertOk();
        $this->actingAs($kasir)->get('/oms/shipments')->assertOk();
        $this->actingAs($kasir)->get('/oms/shipments/create')->assertForbidden();
        $this->actingAs($kasir)->get('/wms/inventory')->assertForbidden();
        $this->actingAs($kasir)->get('/tms/manifests')->assertForbidden();
    }

    public function test_shipment_status_transition_handles_missing_location_and_manual_input(): void
    {
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();

        // Test clicking quick button without location/description keys on PAID shipment (00001 is booked/paid or let's use 00002/new booked paid shipment)
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();
        $cust = Customer::query()->where('code', 'CUST-RTL-001')->firstOrFail();

        $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Penerima Manual Test',
            'consignee_phone' => '081277778888',
            'consignee_address' => 'Surabaya',
            'service_type' => 'REG',
            'payment_method' => PaymentMethod::CASH->value,
            'cash_received' => true,
            'items' => [
                ['description' => 'Barang Uji', 'qty' => 1, 'actual_weight_kg' => 3, 'length_cm' => 20, 'width_cm' => 20, 'height_cm' => 20],
            ],
        ])->assertRedirect();

        /** @var Shipment $shipment */
        $shipment = Shipment::query()->latest('id')->firstOrFail();

        // 1. Transition WITHOUT location & description keys (regression check for Undefined array key "location")
        $this->actingAs($admin)
            ->post("/oms/shipments/{$shipment->awb_number}/transition", ['status' => 'picked_up'])
            ->assertSessionHas('success');

        $this->assertEquals(ShipmentStatus::PICKED_UP, $shipment->refresh()->status);

        // 2. Manual transition jump with custom location & description
        $this->actingAs($admin)
            ->post("/oms/shipments/{$shipment->awb_number}/transition", [
                'status' => 'in_transit',
                'location' => 'Checkpoint Tol Palimanan',
                'description' => 'Linehaul melaju ke hub Surabaya',
                'manual' => true,
            ])
            ->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals(ShipmentStatus::IN_TRANSIT, $shipment->status);
        $this->assertDatabaseHas('tracking_logs', [
            'shipment_id' => $shipment->id,
            'status' => 'in_transit',
            'location' => 'Checkpoint Tol Palimanan',
            'description' => 'Linehaul melaju ke hub Surabaya',
        ]);
    }

    public function test_manual_invoice_detail_print_and_vendor_bill_ap_workflow(): void
    {
        /** @var User $kasir */
        $kasir = User::query()->where('email', 'kasir@pachelogistic.id')->firstOrFail();
        /** @var User $oms */
        $oms = User::query()->where('email', 'oms@pachelogistic.id')->firstOrFail();
        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@pachelogistic.id')->firstOrFail();

        $cust = Customer::query()->where('code', 'CUST-RTL-001')->firstOrFail();
        $vendor = Vendor::query()->where('code', 'VND-LHL-001')->firstOrFail();
        $jkt = Branch::query()->where('code', 'JKT')->firstOrFail();
        $sby = Branch::query()->where('code', 'SBY')->firstOrFail();

        // 1. Seeded Invoice detail & print pages load cleanly
        $seededInvoice = Invoice::query()->where('invoice_number', 'INV-PCH-202611-0001')->firstOrFail();
        $this->actingAs($kasir)->get("/finance/invoices/{$seededInvoice->id}")->assertOk();
        $this->actingAs($kasir)->get("/finance/invoices/{$seededInvoice->id}/print")->assertOk();

        // 2. Unauthorized OMS user blocked from finance manual invoice & vendor bill endpoints
        $this->actingAs($oms)->post('/finance/invoices/manual', [])->assertForbidden();
        $this->actingAs($oms)->post('/finance/vendor-bills', [])->assertForbidden();

        // 3. Create Manual AR Invoice with 2 lines + PPN 11% (without PPh 23)
        // Line 1: 2 * 500,000 = 1,000,000; Line 2: 1 * 250,000 = 250,000 => Subtotal = 1,250,000
        // PPN 11% = 137,500; PPh23 = 0 => Total = 1,387,500
        $this->actingAs($kasir)->post('/finance/invoices/manual', [
            'customer_id' => $cust->id,
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-23',
            'due_date' => '2026-12-07',
            'apply_ppn' => true,
            'apply_pph23' => false,
            'notes' => 'Tagihan jasa sewa staging WMS & charter',
            'lines' => [
                ['description' => 'Charter Blindvan JKT–BDG', 'quantity' => 2, 'unit' => 'trip', 'unit_price' => 500000],
                ['description' => 'Handling & Repacking Pallet', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 250000],
            ],
        ])->assertRedirect();

        /** @var Invoice $manualInvoice */
        $manualInvoice = Invoice::query()->where('invoice_type', 'manual')->latest('id')->firstOrFail();
        $this->assertEquals(1250000.0, (float) $manualInvoice->subtotal);
        $this->assertEquals(137500.0, (float) $manualInvoice->ppn_amount);
        $this->assertEquals(0.0, (float) $manualInvoice->pph23_amount);
        $this->assertEquals(1387500.0, (float) $manualInvoice->total_amount);
        $this->assertCount(2, $manualInvoice->lines);

        $this->actingAs($kasir)->get("/finance/invoices/{$manualInvoice->id}")->assertOk();
        $this->actingAs($kasir)->get("/finance/invoices/{$manualInvoice->id}/print")->assertOk();

        // Mark manual invoice paid -> records credit entry in financial_ledger
        $this->actingAs($kasir)->post("/finance/invoices/{$manualInvoice->id}/mark-paid", [
            'payment_reference' => 'TRF-BCA-MAN-001',
        ])->assertSessionHas('success');

        $manualInvoice->refresh();
        $this->assertEquals('paid', $manualInvoice->status);
        $this->assertEquals('TRF-BCA-MAN-001', $manualInvoice->payment_reference);
        $this->assertDatabaseHas('financial_ledger', [
            'channel' => 'manual_invoice',
            'direction' => 'credit',
            'reference_type' => 'invoice',
            'reference_id' => $manualInvoice->id,
            'amount' => 1387500.0,
        ]);

        // 4. Create Shipment with vendor_cost to test pulling AWB vendor cost into Vendor Bill (AP)
        $this->actingAs($admin)->post('/oms/shipments', [
            'customer_id' => $cust->id,
            'origin_branch_id' => $jkt->id,
            'destination_branch_id' => $sby->id,
            'vendor_id' => $vendor->id,
            'vendor_awb_no' => 'VND-LHL-8801',
            'vendor_cost' => 300000,
            'shipper_name' => $cust->name,
            'shipper_phone' => $cust->phone,
            'shipper_address' => $cust->address,
            'consignee_name' => 'Tujuan Vendor Bill',
            'consignee_phone' => '081266665555',
            'consignee_address' => 'Surabaya',
            'service_type' => 'REG',
            'payment_method' => PaymentMethod::CASH->value,
            'cash_received' => true,
            'items' => [
                ['description' => 'Karton Komponen', 'qty' => 1, 'actual_weight_kg' => 10, 'length_cm' => 30, 'width_cm' => 30, 'height_cm' => 30],
            ],
        ])->assertRedirect();

        /** @var Shipment $vendorShipment */
        $vendorShipment = Shipment::query()->latest('id')->firstOrFail();

        // Create AP Vendor Bill linked to shipment vendor_cost + extra fee
        // Subtotal = 300,000 + 100,000 = 400,000; PPN 11% = 44,000; PPh23 2% = 8,000 => Total = 436,000
        $this->actingAs($kasir)->post('/finance/vendor-bills', [
            'vendor_id' => $vendor->id,
            'branch_id' => $jkt->id,
            'vendor_invoice_no' => 'MTN-2026-9901',
            'bill_date' => '2026-11-23',
            'due_date' => '2026-12-23',
            'apply_ppn' => true,
            'apply_pph23' => true,
            'notes' => 'Tagihan linehaul mitra bulan November',
            'lines' => [
                ['shipment_id' => $vendorShipment->id, 'description' => "Biaya Vendor AWB {$vendorShipment->awb_number}", 'quantity' => 1, 'unit' => 'resi', 'unit_price' => 300000],
                ['description' => 'Biaya Bongkar Tambahan', 'quantity' => 1, 'unit' => 'jasa', 'unit_price' => 100000],
            ],
        ])->assertRedirect();

        /** @var VendorBill $bill */
        $bill = VendorBill::query()->where('vendor_invoice_no', 'MTN-2026-9901')->firstOrFail();
        $this->assertEquals(400000.0, (float) $bill->subtotal);
        $this->assertEquals(44000.0, (float) $bill->ppn_amount);
        $this->assertEquals(8000.0, (float) $bill->pph23_amount);
        $this->assertEquals(436000.0, (float) $bill->total_amount);

        // Prevent double AP billing for same shipment vendor cost
        $this->actingAs($kasir)->post('/finance/vendor-bills', [
            'vendor_id' => $vendor->id,
            'bill_date' => '2026-11-23',
            'due_date' => '2026-12-23',
            'apply_ppn' => false,
            'apply_pph23' => false,
            'lines' => [
                ['shipment_id' => $vendorShipment->id, 'description' => 'Duplikat Tagihan AWB', 'quantity' => 1, 'unit' => 'resi', 'unit_price' => 300000],
            ],
        ])->assertSessionHas('error');

        $this->actingAs($kasir)->get("/finance/vendor-bills/{$bill->id}")->assertOk();
        $this->actingAs($kasir)->get("/finance/vendor-bills/{$bill->id}/print")->assertOk();

        // Mark Vendor Bill Paid -> records DEBIT entry in financial_ledger
        $this->actingAs($kasir)->post("/finance/vendor-bills/{$bill->id}/mark-paid", [
            'payment_reference' => 'OUT-BCA-AP-771',
        ])->assertSessionHas('success');

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals('OUT-BCA-AP-771', $bill->payment_reference);
        $this->assertDatabaseHas('financial_ledger', [
            'channel' => 'vendor_ap',
            'direction' => 'debit',
            'reference_type' => 'vendor_bill',
            'reference_id' => $bill->id,
            'amount' => 436000.0,
        ]);
    }
}
