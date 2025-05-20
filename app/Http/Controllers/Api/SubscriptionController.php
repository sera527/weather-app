<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionRequest;
use App\Services\Weather\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function subscribe(SubscriptionRequest $request): JsonResponse
    {
        $result = $this->subscriptionService->subscribe(
            $request->input('email'),
            $request->input('city'),
            $request->input('frequency'),
        );

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    public function confirm(string $token): JsonResponse
    {
        $result = $this->subscriptionService->confirmSubscription($token);

        return response()->json($result);
    }

    public function unsubscribe(string $token): JsonResponse
    {
        $result = $this->subscriptionService->unsubscribe($token);

        if ($result) {
            return response()->json([
                'message' => 'You have been successfully unsubscribed from weather updates.',
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired unsubscribe token',
        ], 400);
    }
}
