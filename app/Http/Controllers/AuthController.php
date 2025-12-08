<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $isFirstUser = User::count() === 0;

            Log::info('Is first user', ['value' => $isFirstUser]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'referral_id' => $isFirstUser
                    ? 'nullable'
                    : 'required|uuid|exists:users,id',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'referral_id' => $validated['referral_id'] ?? null,
            ]);

            Auth::login($user);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        }
        catch (\Throwable $e) {

            Log::error('Register failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Registration failed'
            ], 500);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::whereEmail($request->email)->first();

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }

    public function logout(Request $request){
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $session = $request->session();
        Log::debug(json_encode($session));

        return response()->json(['message' => 'Logged out successfully'])
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }
}
