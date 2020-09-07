<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Resources\UserResource;
use App\Mail\ConfirmationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
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
     * @var int The expiration time of confirmation signatures, in minutes.
     */
    public int $signatureExpires = 15;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'confirm', 'refresh', 'check']]);
        $this->middleware('throttle:5,1,confirm', ['only' => ['confirm']]);
        $this->middleware('throttle:5,1,login', ['only' => ['login']]);
        $this->middleware('throttle:3,1,refresh', ['only' => ['refresh']]);
        $this->middleware('throttle:60,1,default', ['only' => ['logout', 'me']]);
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required|email',
        ])->validate();

        $code = mt_rand(100000, 999999);

        $url = URL::temporarySignedRoute(
            'v1.auth.confirm', now()->addMinutes($this->signatureExpires), [
                'email' => $data['email'],
                'code' => $code,
            ]
        );

        $query = $this->getQueryFromUrl($url);

        Mail::to($query->get('email'))->send(new ConfirmationEmail($url, $code));

        return response()->json([
            'signature' => $query->get('signature'),
            'expires' => $query->get('expires'),
            'email' => $query->get('email'),
        ]);
    }

    public function confirm(Request $request)
    {
        if ( ! $request->hasValidSignature() || $this->isSignatureExpired($request)) {
            error('Signature invalid or expired.', 'KI-AUTH-0001');
        }

        $user = User::query()
                    ->where('email', $request->query('email'))
                    ->firstOrCreate(['email' => $request->query('email')]);

        $token = auth()->login($user);

        return $this->queueTokenCookie($token)
                    ->expireSignature($request)
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

    public function check(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'username' => "required|string|alpha_dash|max:32|unique:users,username,{$user->id}",
        ]);

        return response('');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $this->validate($request, [
            'name' => 'required|string|max:100',
            'username' => "required|string|alpha_dash|max:32|unique:users,username,{$user->id}",
        ]);

        $user->fill($data);
        $user->save();

        return new UserResource($user);
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
     */
    public function refresh()
    {
        try {
            $token = auth()->refresh();
        } catch (TokenExpiredException $exception) {
            error('Token missing or expired.', 'KI-AUTH-0002');
        } catch (TokenInvalidException $exception) {
            error('Invalid token.', 'KI-AUTH-0003');
        } catch (JWTException $exception) {
            error('Token missing or expired.', 'KI-AUTH-0002');
        }

        $decoded = JWTAuth::decode(new Token($token));

        $iat = $decoded->get('iat');

        $ttl = config('jwt.refresh_ttl') * 60 - (now()->unix() - $iat);

        if ($user = auth()->user()) {
            $this->touchLoginTimestamp($user);
        }

        return $this->queueTokenCookie($token, $ttl / 60)
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

    protected function getQueryFromUrl(string $url)
    {
        $collection = collect();
        $query = explode('&', parse_url($url, PHP_URL_QUERY));

        foreach ($query as $item) {
            $data = explode('=', $item);

            $collection->put($data[0], urldecode($data[1]));
        }

        return $collection;
    }

    protected function isSignatureExpired(Request $request)
    {
        return Cache::has("signature-{$request->query('signature')}");
    }

    protected function expireSignature(Request $request)
    {
        Cache::put("signature-{$request->query('signature')}", true, $this->signatureExpires * 60);

        return $this;
    }
}
