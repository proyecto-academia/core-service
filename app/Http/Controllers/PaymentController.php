<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Course;
use App\Models\Pack;
use App\Models\Purchase;
use App\Models\Enrollment;

class PaymentController extends ApiController
{
    public function create(Request $request)
    {
        $user = $request->get('auth_user')['data'] ?? null;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:course,pack',
        ]);

        $modelClass = $request->type === 'course' ? Course::class : Pack::class;
        $item = $modelClass::findOrFail($request->id);

        $amount = $item->price;

        // Handle free courses or packs
        if ($amount == 0) {
            $enrollment = Enrollment::create([
                'user_id' => $user['id'],
                'enrollable_type' => $modelClass,
                'enrollable_id' => $item->id,
                'enrolled_at' => now(),
            ]);

            return $this->success(['enrollment' => $enrollment], 'Enrolled successfully (free)');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $intent = PaymentIntent::create([
                'amount' => intval($amount * 100), // Convert to cents
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'enrollable_id' => $item->id,
                    'enrollable_type' => $modelClass,
                    'user_id' => $user['id'],
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->error($e->getMessage(), 500);
        }

        return $this->success(['clientSecret' => $intent->client_secret]);
    }


    public function confirm(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method' => 'nullable|string', // Optional
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent_id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->error($e->getMessage(), 500);
        }

        if ($intent->status === 'succeeded') {
            $enrollableId = $intent->metadata->enrollable_id;
            $enrollableType = $intent->metadata->enrollable_type;
            $userId = $intent->metadata->user_id;

            // Create the enrollment after successful payment
            $enrollment = Enrollment::firstOrCreate([
                'user_id' => $userId,
                'enrollable_type' => $enrollableType,
                'enrollable_id' => $enrollableId,
            ], [
                'enrolled_at' => now(),
            ]);

            // Avoid duplicate purchases
            if (!$enrollment->purchase) {
                Purchase::create([
                    'user_id' => $userId,
                    'enrollment_id' => $enrollment->id,
                    'amount' => $intent->amount / 100, // Convert back to original amount
                    'payment_method' => $request->payment_method ?? $intent->payment_method_types[0] ?? 'unknown',
                    'status' => 'paid',
                ]);
            }

            return $this->success(['success' => true]);
        }

        return $this->error('Payment intent failed', 400);
    }
}
