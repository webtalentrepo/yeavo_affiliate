<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Repositories\PlansRepository;
use App\Http\Repositories\UsersRepository;
use App\Http\Resources\User as UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    protected $usersRepo;
    protected $planRepo;

    public function __construct(UsersRepository $usersRepository, PlansRepository $plansRepository)
    {
        $this->usersRepo = $usersRepository;
        $this->planRepo = $plansRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $members = $this->usersRepo->getMembers($request);

        return UserResource::collection($members);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string|max:200',
            'email'    => 'required|string|email|max:200|unique:users',
            'password' => 'required|string|min:' . config('auth.password_min') . '|confirmed',
            'role'     => 'required',
            'plan'     => 'required',
        ], [
            'name.required' => 'Name is required.'
        ]);

        $user = new User();
        $userId = $user->insertGetId([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        $this->usersRepo->create($userId, $request->all());

        return response()->json(['result' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\User $user
     * @return UserResource
     */
    public function edit(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, User $user)
    {
        $flag = $request->input('flag');

        if ($flag == 'banned') {
            $user->user_profile()->update([
                'banned' => 1
            ]);
        } elseif ($flag == 'no-ban') {
            $user->user_profile()->update([
                'banned' => 0
            ]);
        } elseif ($flag == 'update') {
            $rules = [
                'name'  => 'required|string|max:191',
                'email' => 'required|string|email|max:191|unique:users,email,' . $user->id
            ];

            if ($request->input('password') != '') {
                $rules['password'] = 'string|min:' . config('auth.password_min') . '|confirmed';
            }

            $this->validate($request, $rules, [
                'name.required' => 'First name is required.'
            ]);

            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if ($request->input('password') != '') {
                $user->password = bcrypt($request->input('password'));
            }

            // User role update
            $user->roles()->sync($request->input('role'));

            // User plan update
            $plan_id = $request->input('plan');
            $plans = $this->planRepo->model()->find($plan_id);
            $current_date = date('Y-m-d H:i:s');
            if ($plans->free_plan == '1') {
                $save_data = [
                    'payment_status' => 'success',
                    'status'         => 'Active',
                    'activated_on'   => $current_date,
                    'payment_method' => 'Stripe',
                    'free_flag'      => 1
                ];
            } else {
                $save_data = [
                    'free_pack'         => '1',
                    'duration'          => $plans->duration,
                    'duration_schedule' => $plans->duration_schedule,
                    'amount'            => $plans->amount,
                    'payment_status'    => 'success',
                    'status'            => 'Active',
                    'activated_on'      => $current_date,
                    'expiry_on'         => date('Y-m-d H:i:s', strtotime("+" . $plans->duration . " " . $plans->duration_schedule . "s", strtotime($current_date))),
                    'payment_method'    => 'JVZoo',
                    'free_flag'         => 0
                ];
            }

            $user->plans()->sync([$plan_id => $save_data]);
            $user->save();

            // User profile update
            $current_plan = $user->user_plans()->orderBy('id', 'desc')->first();
            $user->user_profile()->update([
                'company'      => is_null($request->input('company')) ? '' : $request->input('company'),
                'address'      => is_null($request->input('address')) ? '' : $request->input('address'),
                'city'         => is_null($request->input('city')) ? '' : $request->input('city'),
                'postal_code'  => is_null($request->input('postal_code')) ? '' : $request->input('postal_code'),
                'country'      => is_null($request->input('country')) ? '' : $request->input('country'),
                'state_code'   => is_null($request->input('state_code')) ? '' : $request->input('state_code'),
                'phone'        => is_null($request->input('phone')) ? '' : $request->input('phone'),
                'current_plan' => $current_plan ? $current_plan->id : 0
            ]);
        }

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        DB::table('role_user')->where('user_id', $user->id)->delete();
        DB::table('user_plans')->where('user_id', $user->id)->delete();
        DB::table('user_profiles')->where('user_id', $user->id)->delete();
        DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();

        $user->delete();

        return response()->json([
            'result' => 'success'
        ]);
    }
}
