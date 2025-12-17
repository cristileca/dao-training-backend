<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UserPackage;
use App\Services\MlmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(){
        return Package::query()->where('active',1)->get();
    }

    public function buy(Request $request)
    {
        $user = auth()->user();



        Log::error("intraaaaaaaaa", ["user" => $user->id]);

        $package = Package::query()->whereId($request->get("package_id"))->whereActive(1)->first();

        if (!$package) {
            return response()->json(["message" => "Package not found"], 404);
        }

        $purchase = UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $request->package_id,
            'price' => $package->price,
            'uuid' => Str::uuid(),
        ]);

        $mlmService =  new MlmService();
        $mlmService->distributeCommissions($user->id, $package->price);

        return response()->json([
            'message' => 'Pachet cumpărat cu succes!',
            'package' => $package,
            'purchase' => $purchase,
        ]);
    }
}
