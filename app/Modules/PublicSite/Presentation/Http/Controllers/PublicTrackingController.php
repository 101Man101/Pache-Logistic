<?php

declare(strict_types=1);

namespace App\Modules\PublicSite\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

final class PublicTrackingController extends Controller
{
    public function welcome(): Response
    {
        $stats = [
            'branches' => Schema::hasTable('branches') ? Branch::query()->where('is_active', true)->count() : 6,
            'routes' => Schema::hasTable('tariffs') ? Tariff::query()->where('is_active', true)->count() : 30,
            'shipments' => Schema::hasTable('shipments') ? Shipment::query()->count() : 10,
        ];

        $sampleAwbs = Schema::hasTable('shipments')
            ? Shipment::query()->orderBy('id')->limit(3)->pluck('awb_number')->all()
            : ['PCH-JKT-261121-00001', 'PCH-JKT-261121-00003', 'PCH-JKT-261121-00004'];

        return Inertia::render('Welcome', [
            'stats' => $stats,
            'sampleAwbs' => $sampleAwbs,
        ]);
    }

    public function track(Request $request): Response
    {
        $rawAwbs = (string) $request->string('awb', '');
        $phoneLast4 = preg_replace('/\D/', '', (string) $request->string('phone_last4', '')) ?? '';

        $awbList = collect(preg_split('/[\s,;]+/', trim($rawAwbs)) ?: [])
            ->map(static fn (string $code): string => strtoupper(trim($code)))
            ->filter()
            ->unique()
            ->take(10)
            ->values();

        $results = [];
        $notFound = [];

        if ($awbList->isNotEmpty() && Schema::hasTable('shipments')) {
            $shipments = Shipment::query()
                ->with(['originBranch:id,code,name,city', 'destinationBranch:id,code,name,city', 'trackingLogs'])
                ->whereIn('awb_number', $awbList->all())
                ->get()
                ->keyBy('awb_number');

            foreach ($awbList as $code) {
                /** @var Shipment|null $s */
                $s = $shipments->get($code);
                if ($s === null) {
                    $notFound[] = $code;

                    continue;
                }

                $digits = preg_replace('/\D/', '', $s->consignee_phone) ?? '';
                $unlocked = strlen($phoneLast4) === 4 && str_ends_with($digits, $phoneLast4);

                $results[] = [
                    'awb_number' => $s->awb_number,
                    'reference_no' => $s->reference_no,
                    'service_type' => $s->service_type,
                    'status' => $s->status->value,
                    'status_label' => $s->status->label(),
                    'payment_method' => $s->payment_method->value,
                    'payment_method_label' => $s->payment_method->shortLabel(),
                    'payment_status' => $s->payment_status->value,
                    'payment_status_label' => $s->payment_status->label(),
                    'chargeable_weight_kg' => (float) $s->chargeable_weight_kg,
                    'origin' => $s->originBranch ? "{$s->originBranch->city} ({$s->originBranch->code})" : '-',
                    'destination' => $s->destinationBranch ? "{$s->destinationBranch->city} ({$s->destinationBranch->code})" : '-',
                    'shipper_name' => $s->shipper_name,
                    'consignee_name' => $unlocked ? $s->consignee_name : $this->maskName($s->consignee_name),
                    'consignee_phone' => $unlocked ? $s->consignee_phone : $this->maskPhone($s->consignee_phone),
                    'consignee_address' => $unlocked ? $s->consignee_address : $this->maskAddress($s->consignee_address),
                    'pii_unlocked' => $unlocked,
                    'epod_receiver_name' => $s->epod_receiver_name,
                    'epod_receiver_relation' => $s->epod_receiver_relation,
                    'delivered_at' => $s->delivered_at?->format('d M Y, H:i'),
                    'logs' => $s->trackingLogs->map(static fn ($log): array => [
                        'id' => $log->id,
                        'status' => $log->status,
                        'location' => $log->location,
                        'description' => $log->description,
                        'created_at' => $log->created_at->format('d M Y, H:i'),
                    ])->all(),
                ];
            }
        }

        $sampleAwbs = Schema::hasTable('shipments')
            ? Shipment::query()->orderBy('id')->limit(4)->pluck('awb_number')->all()
            : [];

        return Inertia::render('PublicTracking', [
            'queryAwb' => $rawAwbs,
            'phoneLast4' => $phoneLast4,
            'results' => $results,
            'notFound' => $notFound,
            'sampleAwbs' => $sampleAwbs,
        ]);
    }

    private function maskName(string $name): string
    {
        $parts = explode(' ', trim($name));

        return implode(' ', array_map(static function (string $word): string {
            if (strlen($word) <= 2) {
                return $word;
            }

            return substr($word, 0, 2).str_repeat('*', min(5, strlen($word) - 2));
        }, $parts));
    }

    private function maskPhone(string $phone): string
    {
        $clean = trim($phone);
        if (strlen($clean) <= 4) {
            return '****';
        }

        return substr($clean, 0, 4).'-****-'.substr($clean, -2);
    }

    private function maskAddress(string $address): string
    {
        $parts = explode(',', $address);
        $tail = trim(end($parts) ?: 'Indonesia');

        return '*** (Alamat disamarkan demi privasi — masukkan 4 digit akhir HP penerima), '.$tail;
    }
}
