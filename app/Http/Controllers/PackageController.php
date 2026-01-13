<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPackagePurchase;
use App\Jobs\ResetVolumes;
use App\Models\Package;
use App\Models\UserPackage;
use App\Models\VolumeHistory;
use App\Services\MlmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(){
        return Package::query()->where('active',1)->get();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function buy(Request $request)
    {
        $user = auth()->user();

        $package = Package::query()->whereId($request->get("package_id"))->whereActive(1)->first();

         ProcessPackagePurchase::dispatch($user->id, $package->id )->onQueue('purchases');

        if (!$package) {
            return response()->json(["message" => "Package not found"], 404);
        }

        $purchase = UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $request->package_id,
            'price' => $package->price,
            'uuid' => Str::uuid(),
        ]);

        return response()->json([
            'message' => 'Pachet cumpărat cu succes!',
            'package' => $package,
            'purchase' => $purchase,
        ]);
    }
}
