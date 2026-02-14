<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __invoke(Request $request, Order $order): StreamedResponse|Response
    {
        $store = $order->store;

        if (! $store) {
            abort(404);
        }

        $user = auth()->user();
        if (! $user) {
            abort(401);
        }
        if (! $user->isSuperAdmin() && (int) $user->store_id !== (int) $store->id) {
            abort(403);
        }

        $order->load(['items.product', 'store']);

        $branding = $store->branding_config ?? [];
        $currency = $branding['currency'] ?? 'IQD';

        $pdf = Pdf::loadView('invoices.order', [
            'order' => $order,
            'store' => $store,
            'currency' => $currency,
        ]);

        $filename = 'invoice-' . ($order->order_number ?? $order->id) . '.pdf';

        return $pdf->stream($filename);
    }
}
