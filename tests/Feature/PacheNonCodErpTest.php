<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Manifest;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StorageBin;
use App\Models\User;
use App\Models\Vehicle;
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
}
