<?php

namespace App\Http\Controllers;

use App\Exceptions\ShippingProviderException;
use App\Services\CheckoutService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutShippingController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected ShippingService $shippingService,
    ) {}

    public function destinations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $request->user()->id),
            ],
        ]);
        $address = $request->user()->addresses()->findOrFail($validated['address_id']);

        try {
            return response()->json([
                'destinations' => $this->shippingService->destinationsForAddress($address),
            ]);
        } catch (ShippingProviderException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function quotes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $request->user()->id),
            ],
            'destination_id' => ['required', 'integer', 'min:1'],
            'courier' => ['required', 'string', Rule::in(ShippingService::availableCouriers())],
        ]);
        $address = $request->user()->addresses()->findOrFail($validated['address_id']);

        try {
            $weightGram = $this->checkoutService->currentShippingWeight($request->user());
            $quotes = $this->shippingService->quote(
                $address,
                (int) $validated['destination_id'],
                $weightGram,
                $validated['courier'],
                $request->user(),
                $this->checkoutService->currentShippingFingerprint($request->user()),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (ShippingProviderException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }

        return response()->json([
            'weight_gram' => $weightGram,
            'quotes' => $quotes,
        ]);
    }
}
