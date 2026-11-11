<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // GET /api/orders/{uuid}
    // public function show(Request $request, string $uuid)
    // {
    //     $order = Order::with(['course:id,slug,title,image,image_alt'])
    //         ->where('uuid', $uuid)
    //         ->where('user_id', $request->user()->id)
    //         ->first();

    //     if (! $order) {
    //         return response()->json(['message' => 'Commande introuvable.'], 404);
    //     }

    //     return response()->json([
    //         'data' => $this->format($order),
    //     ]);
    // }


        // GET /api/orders/{uuid}
    public function show(Request $request, string $uuid)
    {
        $order = Order::with(['course:id,slug,title,image,image_alt'])
            ->where('uuid', $uuid)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Commande introuvable.'], 404);
        }

        // Optionnel : vérifier que la commande appartient bien à l'utilisateur
        // Si l'utilisateur est connecté et que ce n'est pas la sienne, on bloque
        if ($request->user() && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return response()->json([
            'data' => $this->format($order),
        ]);
    }

    // GET /api/me/orders
    public function myOrders(Request $request)
    {
        $orders = Order::with(['course:id,slug,title,image,image_alt'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $orders->map(fn ($o) => $this->format($o)),
        ]);
    }

    private function format(Order $order): array
    {
        return [
            'id'                    => $order->uuid,
            'reference'             => $order->reference,
            'invoiceNumber'         => $order->invoice_number,
            'type'                  => $order->type,
            'status'                => $order->status,
            'courseId'              => $order->course_id,
            'courseSlug'            => $order->course?->slug,
            'courseTitle'           => $order->course?->title,
            'courseImage'           => $order->course?->image,
            'courseImageAlt'        => $order->course?->image_alt,
            'courseProvider'        => 'KMAA Academy',
            'subtotal'              => $order->subtotal,
            'discount'              => $order->discount,
            'tva'                   => $order->tva,
            'total'                 => $order->total,
            'currency'              => $order->currency,
            'promoCode'             => $order->promo_code,
            'paymentMethod'         => $order->payment_method,
            'paymentMethodLabel'    => $order->payment_method_label,
            'transactionId'         => $order->transaction_id,
            'customerName'          => $order->customer_name,
            'customerEmail'         => $order->customer_email,
            'createdAt'             => $order->created_at?->toIso8601String(),
            'paidAt'                => $order->paid_at?->toIso8601String(),
        ];
    }
}