<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Resources\UserResource;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'confirm', 'refresh']]);
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required|email',
        ])->validate();

        if ( ! User::where('email', $data['email'])->exists()) {
            error('Account not found.', 'KI-AUTH-0001');
        }

        // TODO: send this link via email
        return URL::temporarySignedRoute('v1.auth.confirm', now()->addMinutes(15), ['email' => $data['email']]);
    }

    public function confirm(Request $request)
    {
        if ( ! $request->hasValidSignature()) {
            error('Signature invalid or expired.', 'KI-AUTH-0002');
        }

        try {
            $user = User::query()
                        ->where('email', $request->query('email'))
                        ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            error('User not found. The account may have been deleted.', 'KI-AUTH-0003');
        }

        $token = auth()->login($user);

        return $this->queueTokenCookie($token)
                    ->touchLoginTimestamp($user)
                    ->respondWithJson($token);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        return new UserResource(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        $this->queueTokenCookie(); // Expire cookie

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * Refresh a token.
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function refresh()
    {
        try {
            $token = auth()->refresh();
        } catch (TokenExpiredException $exception) {
            error('Token missing or expired.', 'KI-AUTH-0004');
        } catch (TokenInvalidException $exception) {
            error('Invalid token.', 'KI-AUTH-0005');
        } catch (JWTException $exception) {
            error('Token missing or expired.', 'KI-AUTH-0004');
        }

        $iat = JWTAuth::decode(new Token($token))->get('iat');

        $ttl = config('jwt.refresh_ttl') * 60 - (now()->unix() - $iat);

        return $this->queueTokenCookie($token, $ttl / 60)
                    ->touchLoginTimestamp(auth()->user())
                    ->respondWithJson($token, $ttl);
    }

    protected function queueTokenCookie($token = null, $ttl = null)
    {
        $cookie = cookie(
            'token', // Name
            $token, // Value
            // The Expires property is set to 1 if no token is given. This means
            // that the system wants to delete the cookie, logging the user out.
            // If a token is given, use the given TTL, or the default TTL.
            $token ? $ttl ?? config('jwt.refresh_ttl') : 1, // Expires
            null, // Path
            config('app.domain'), // Domain
            config('app.secure_cookie'), // Secure
            true, // HttpOnly, must be true so JavaScript can't access it
            false, // Raw
            'none' // SameSite
        );

        Cookie::queue($cookie);

        return $this;
    }

    protected function touchLoginTimestamp($user)
    {
        $user->last_login_at = now();
        $user->save();

        return $this;
    }

    protected function respondWithJson($token, $ttl = null)
    {
        $refreshTtl = $ttl ?? config('jwt.refresh_ttl') * 60;
        $accessTtl = min(config('jwt.ttl') * 60, $refreshTtl);

        return response()->json([
            'token' => $token,
            'type' => 'bearer',
            'access_expires_in' => $accessTtl,
            'refresh_expires_in' => $refreshTtl,
        ]);
    }
}
