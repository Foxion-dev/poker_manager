<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
	public function index(): JsonResponse
	{
		$currencies = Currency::orderBy('code')->get();

		return response()->json($currencies);
	}
}
