<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCurrencyRequest;
use App\Http\Requests\Api\UpdateCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
	public function index(): JsonResponse
	{
		$currencies = Currency::orderBy('code')->get();

		return response()->json($currencies);
	}

	public function store(StoreCurrencyRequest $request): JsonResponse
	{
		$currency = Currency::create($request->validated());

		return response()->json($currency, 201);
	}

	public function show(Currency $currency): JsonResponse
	{
		return response()->json($currency);
	}

	public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
	{
		$currency->update($request->validated());

		return response()->json($currency);
	}

	public function destroy(Currency $currency): JsonResponse
	{
		if ($currency->tournaments()->count() > 0) {
			return response()->json(['message' => 'Cannot delete currency with associated tournaments'], 422);
		}

		$currency->delete();

		return response()->json(['message' => 'Currency deleted successfully']);
	}
}
