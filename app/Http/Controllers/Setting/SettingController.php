<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\ApiController;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    }

    /**
     * GET /api/settings/interest-rate
     */
    public function getInterestRate()
    {
        $rate = Setting::getValue('interest_rate', '0');
        return $this->successResponse([
            'interest_rate' => (float) $rate,
        ], 200);
    }

    /**
     * PUT /api/settings/interest-rate
     */
    public function updateInterestRate(Request $request)
    {
        $request->validate([
            'interest_rate' => 'required|numeric|min:0',
        ]);

        Setting::setValue('interest_rate', $request->interest_rate);

        return $this->successResponse([
            'interest_rate' => (float) $request->interest_rate,
        ], 200);
    }
}
