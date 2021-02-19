<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\UsersRepository;
use App\Http\Resources\User as UserResource;
use App\Notifications\ResetPasswordLinkSent;
use App\User;
use App\UserProfile;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Lcobucci\JWT\Parser;
use Stripe\StripeClient;

class AuthController extends Controller
{
    protected $hasher;

    public function __construct(HasherContract $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * Login
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        if (!$user || is_null($user)) {
            return response()->json([
                'message' => "We couldn't find your " . config('app.name') . " Account"
            ], 401);
        }

        if ($user->user_profile) {
            if ($user->user_profile->banned) {
                return response()->json([
                    'message' => "Account has been suspended!"
                ], 419);
            }
        }

        $credentials = [
            'email'    => $email,
            'password' => $request->input('password')
        ];

        $remember_me = false;
        if ($request->has('remember')) {
            if ($request->input('remember') || $request->input('remember') == 'true') {
                $remember_me = true;
            }
        }

        if (auth()->attempt($credentials, $remember_me)) {
            $user = auth()->user();

            $tokenObject = $this->createTokenForUser($user);

            if ($remember_me) {
                $tokenObject->token->expires_at = now()->addDays(config('services.passport.expires_remember_me'));
                $tokenObject->token->update([
                    'expires_at' => now()->addDays(config('services.passport.expires_remember_me'))
                ]);
            }

            return response()->json([
                'accessToken' => $tokenObject->accessToken,
                'expiresIn'   => $tokenObject->token->expires_at->diffInSeconds(now()),
                'isAdmin'     => isSupperAdmin()
            ], 200);
        } else {
            return response()->json([
                'message' => 'Wrong password. Try again or click forgot password to reset it.'
            ], 401);
        }
    }

    /**
     * Create Token for user
     *
     * @param $user
     * @return mixed
     */
    private function createTokenForUser($user)
    {
        $tokenObject = $user->createToken('DBForWeb');

        $tokenObject->token->expires_at = now()->addHours(config('services.passport.expires_hours'));
        $tokenObject->token->update([
            'expires_at' => now()->addHours(config('services.passport.expires_hours'))
        ]);

        return $tokenObject;
    }

    /**
     * Authentication Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $value = $request->bearerToken();

        if ((new Parser())->parse($value)->hasHeader('jti')) {
            $id = (new Parser())->parse($value)->getHeader('jti');
        } elseif ((new Parser())->parse($value)->hasClaim('jti')) {
            $id = (new Parser())->parse($value)->getClaim('jti');
        } else {
            return response()->json([
                'result' => 'Invalid JWT token, Unable to find JTI header'
            ], 400);
        }

        $token = $request->user()->tokens->find($id);
        if ($token) {
            $token->delete();
        }

        cookie()->queue(cookie()->forget('VMAccessToken'));

        return response()->json([
            'result' => 'success'
        ], 200);
    }

    /**
     * Send reset password link notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetPasswordLink(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if (!$user || is_null($user)) {
            return response()->json([
                'message' => "We couldn't find your " . config('app.name') . " Account"
            ], 401);
        }

        if ($user->user_profile) {
            if ($user->user_profile->banned) {
                return response()->json([
                    'message' => "Account has been suspended!"
                ], 419);
            }
        }

        $token = Password::getRepository()->create($user);

        $user->notify(new ResetPasswordLinkSent($token));

        return response()->json([
            'result' => 'success'
        ], 200);
    }

    /**
     * Reset Password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setNewPasswordByToken(Request $request)
    {
        if ($request->has('token')) {
            $token = $request->input('token');
            if (!is_null($token)) {
                $email = '';

                $reset_list = DB::table('password_resets')->get();
                if ($reset_list) {
                    foreach ($reset_list as $row) {
                        if ($this->hasher->check($token, $row->token)) {
                            $email = $row->email;
                            break;
                        }
                    }
                }

                if ($email != '') {
                    $user = User::where('email', $email)->first();
                    if ($user) {
                        $user->password = $this->hasher->make($request->input('password'));
                        $user->save();

                        $tokenObject = $this->createTokenForUser($user);

                        DB::table('password_resets')->where('email', $user->email)->delete();

                        $user = new UserResource($user);

                        return response()->json([
                            'result'      => 'success',
                            'accessToken' => $tokenObject->accessToken,
                            'expiresIn'   => $tokenObject->token->expires_at->diffInSeconds(now()),
                            'isAdmin'     => checkSupperAdmin($user->email),
                            'userInfo'    => $user,
                        ], 200);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Invalid token. Please re-send email and get link again.'
        ], 422);
    }

    /**
     * get authenticated user info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUserInfo()
    {
        $user = auth()->user();

        if ($user) {
            return response()->json([
                'result'   => 'success',
                'email'    => $user->email,
                'userInfo' => new UserResource($user)
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string|max:191',
            'email'    => 'required|string|email|max:191',
            'password' => 'required|string|min:6',
        ]);

        // check user in the stripe
        $stripe = new StripeClient(config('services.stripe.secret'));
        $customer = $stripe->customers->all(['limit' => 1, 'email' => strtolower($request->input('email'))]);
        if ($customer) {
            $customer = json_decode($customer, true);

            if ($customer && isset($customer['data']) && isset($customer['data']['email']) && !is_null($customer['data']['email'])) {
                $user = User::where('email', $request->input('email'))->first();
                $exist = true;
                if (!$user) {
                    $user = new User();
                    $exist = false;
                }
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->password = $this->hasher->make($request->input('password'));
                $user->save();

                if (!$exist) {
                    $userRepo = new UsersRepository();
                    $userRepo->createFreeUserDetails($user, $request->input('password'));
                }

                $tokenObject = $this->createTokenForUser($user);

                $user = new UserResource($user);

                return response()->json([
                    'accessToken' => $tokenObject->accessToken,
                    'expiresIn'   => $tokenObject->token->expires_at->diffInSeconds(now()),
                    'email'       => $user->email,
                    'name'        => $user->name,
                    'id'          => $user->id,
                    'userInfo'    => $user,
                    'isAdmin'     => checkSupperAdmin($user->email),
                    'result'      => 'success'
                ], 200);
            }
        }

        return response()->json([
            'result' => 'error',
            'message' => 'Not exist customer!'
        ]);
    }

    public function getUserByActivateToken(Request $request)
    {
        $activation_code = $request->input('activation_code');

        $userProfile = UserProfile::where('activation_code', $activation_code)->first();

        $email = '';
        $name = '';

        if ($userProfile) {
            $email = $userProfile->user->email;
            $name = $userProfile->user->name;
        }

        return response()->json([
            'email' => $email,
            'name'  => $name
        ]);
    }

    public function oauth(Request $request)
    {
        try {
            if ($request->has('code')) {
                $code = $request->input('code');
//                $client = new \Google_Client();
//                $client->authenticate($code);
//                $access_token = $client->getAccessToken();
//                var_dump($access_token);

                var_dump($code);

//                $client = new Client();
//
//                $response = $client->request('POST', 'https://www.googleapis.com/oauth2/v4/token', [
//                    'body' => json_encode([
//                        'code'          => $code,
//                        'client_id'     => env('ADWORDS_CLIENT_ID'),
//                        'client_secret' => env('ADWORDS_CLIENT_SECRET'),
//                        'grant_type'    => 'authorization_code',
//                        'redirect_uri'  => env('BACKEND_URL') . 'api/oauth'
//                    ])
//                ]);
//
//                $access = json_decode($response->getBody()->getContents(), true);
//
//                var_dump($access);

                exit;
            }
        } catch (GuzzleException $e) {
            var_dump($e->getMessage());
        }
    }
}
