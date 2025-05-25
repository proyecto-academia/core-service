<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Pack;

class PurchaseController extends ApiController
{
    /**
     * Registrar una compra simulada.
     */
    public function store(Request $request)
    {
        $userId = $request->get('auth_user')['data']['id'] ?? null;
        if (!$userId) {
            return $this->error('Unauthorized', 401);
        }

        $data = $request->validate([
            'type'           => 'required|in:course,pack',
            'id'             => 'required|integer',
            'payment_method' => 'required|string',
        ]);

        // Determinar el artículo a comprar
        $modelClass = $data['type'] === 'course' ? Course::class : Pack::class;
        $item = $modelClass::find($data['id']);
        if (!$item) {
            return $this->error(ucfirst($data['type']) . ' not found', 404);
        }

        // Calcular monto
        $amount = $item->is_free ? 0 : ($item->price ?? 0);

        // Simular pago siempre exitoso
        $status = $amount > 0 ? 'paid' : 'free';

        // Crear inscripción si no existe
        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id'         => $userId,
                'enrollable_type' => $modelClass,
                'enrollable_id'   => $item->id,
            ],
            ['enrolled_at' => now()]
        );

        // Registrar compra
        $purchase = Purchase::create([
            'user_id'        => $userId,
            'enrollment_id'  => $enrollment->id,
            'amount'         => $amount,
            'payment_method' => $data['payment_method'],
            'status'         => $status,
        ]);

        return $this->success([
            'purchase'   => $purchase,
            'enrollment' => $enrollment,
        ], 'Purchase completed');
    }

    /**
     * Mostrar una compra por ID.
     */
    public function show($id)
    {
        $userId = request()->get('auth_user')['data']['id'] ?? null;
        if (!$userId) {
            return $this->error('Unauthorized', 401);
        }

        $purchase = Purchase::with('enrollment.enrollable')
            ->where('user_id', $userId)
            ->findOrFail($id);

        return $this->success(['purchase' => $purchase]);
    }
}
