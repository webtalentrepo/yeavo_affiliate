<?php
/**
 * Created by PhpStorm.
 * User: MY
 * Date: 4/20/2018
 * Time: 8:45 AM
 */

namespace App\Http\Repositories;

use App\Notifications\WelcomeToOurSoftware;
use App\Notifications\WelcomeToTewlKitPurchase;
use App\User;
use Illuminate\Support\Facades\DB;

class UsersRepository extends Repository
{
    public function model()
    {
        return app(User::class);
    }

    /**
     * Create users by Administrator
     *
     * @param $user_id
     * @param $request
     */
    public function create($user_id, $request)
    {
        $user = $this->model()->find($user_id);

        // User role register
        $user->roles()->attach($request['role']);

        $plan = DB::table('plans')->find($request['plan']);

        $user_plans = [
            'payment_status'    => 'success',
            'status'            => 'Active',
            'activated_on'      => DB::raw('NOW()'),
            'payment_method'    => 'Stripe',
            'free_flag'         => $plan->free_plan,
            'duration'          => $plan->duration,
            'duration_schedule' => $plan->duration_schedule,
            'amount'            => $plan->amount,
        ];

        if (!$plan->free_plan) {
            $current_date = date('Y-m-d H:i:s');
            $user_plans['expiry_on'] = date('Y-m-d H:i:s', strtotime("+" . $plan->duration . " " . $plan->duration_schedule . "s", strtotime($current_date)));
            $user_plans['payment_method'] = 'JVZoo';
            $user_plans['free_pack'] = '1';
        }

        // User free plan register
        $user->plans()->attach($request['plan'], $user_plans);

        // User profile register
        $userPlan = $user->user_plans()->orderBy('id', 'desc')->first();

        $ins_data = [
            'activated'    => 1,
            'current_plan' => $userPlan->id,
            'company'      => is_null($request['company']) ? '' : $request['company'],
            'address'      => is_null($request['address']) ? '' : $request['address'],
            'city'         => is_null($request['city']) ? '' : $request['city'],
            'postal_code'  => is_null($request['postal_code']) ? '' : $request['postal_code'],
            'country'      => is_null($request['country']) ? '' : $request['country'],
            'state_code'   => is_null($request['state_code']) ? '' : $request['state_code'],
            'phone'        => is_null($request['phone']) ? '' : $request['phone'],
        ];

        $user->user_profile()->create($ins_data);

        $user->notify(new WelcomeToOurSoftware($user, $request['password']));
    }

    public function createFreeUserDetails($user, $password)
    {
        $user->roles()->attach(1);

        $plan = $this->getPlan();

        $user_plans = [
            'payment_status'    => 'success',
            'status'            => 'Active',
            'activated_on'      => DB::raw('NOW()'),
            'payment_method'    => 'Stripe',
            'free_flag'         => $plan->free_plan,
            'duration'          => $plan->duration,
            'duration_schedule' => $plan->duration_schedule,
            'amount'            => $plan->amount,
        ];

        // User free plan register
        $user->plans()->attach($plan->id, $user_plans);

        // User profile register
        $userPlan = $user->user_plans()->orderBy('id', 'desc')->first();

        $ins_data = [
            'activated'    => 1,
            'current_plan' => $userPlan->id,
            'company'      => '',
            'address'      => '',
            'city'         => '',
            'postal_code'  => '',
            'country'      => '',
            'state_code'   => '',
            'phone'        => '',
        ];

        $user->user_profile()->create($ins_data);

        $user->notify(new WelcomeToOurSoftware($user, $password));
    }

    /**
     * Get plan data.
     *
     * @param string $product_id
     * @return mixed
     */
    public function getPlan($product_id = '')
    {
        if (!isset($product_id) || is_null($product_id) || !$product_id || $product_id == '') {
            return DB::table('plans')->where('free_plan', '=', '1')->where('status', '=', 'Active')->first();
        } else {
            return DB::table('plans')->where('product_id', '=', $product_id)->first();
        }
    }

    public function creatByKajabi($user)
    {
        $user->roles()->attach(1);

        $plan = $this->getPlan();

        $user_plans = [
            'payment_status'    => 'success',
            'status'            => 'Active',
            'activated_on'      => DB::raw('NOW()'),
            'payment_method'    => 'Stripe',
            'free_flag'         => $plan->free_plan,
            'duration'          => $plan->duration,
            'duration_schedule' => $plan->duration_schedule,
            'amount'            => $plan->amount,
        ];

        // User free plan register
        $user->plans()->attach($plan->id, $user_plans);

        // User profile register
        $userPlan = $user->user_plans()->orderBy('id', 'desc')->first();

        $activation_code = sha1(encrypt($user->email . '_' . $user->id . '_' . $user->name, false));

        $ins_data = [
            'activated'       => 0,
            'activation_code' => $activation_code,
            'current_plan'    => $userPlan->id,
            'company'         => '',
            'address'         => '',
            'city'            => '',
            'postal_code'     => '',
            'country'         => '',
            'state_code'      => '',
            'phone'           => '',
        ];

        $user->user_profile()->create($ins_data);

        $user->notify(new WelcomeToTewlKitPurchase($user, $activation_code));
    }

    /**
     * get users list by search params
     * @param $request
     * @return mixed
     */
    public function getMembers($request)
    {
        $members = $this->model()
            ->with([
                'roles',
                'user_profile',
                'user_plans' => function ($query) {
                    $query->orderBy('id', 'desc');
                }
            ])
            ->whereExists(function ($query) {
                // User plan check
                $query->select(DB::raw(1))
                    ->from('user_plans')
                    ->whereRaw('user_plans.user_id = users.id');
            });

        // Search by name
        if (isset($request['name']) && !empty($request['name'])) {
            $members->where('users.name', 'like', '%' . $request['name'] . '%');
        }

        // search by email
        if (isset($request['email']) && !empty($request['email'])) {
            $members->where('users.email', 'like', '%' . $request['email'] . '%');
        }

        // search by user role
        if (isset($request['role']) && !empty($request['role'])) {
            $members->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('role_user')
                    ->whereRaw('role_user.user_id = users.id')
                    ->whereRaw('role_user.role_id = "' . $request['role'] . '"');
            });
        }

        // search by banned status
        if (isset($request['banned']) && !empty($request['banned'])) {
            if ($request['banned'] == '2') {
                $request['banned'] = 0;
            }
            $members->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('user_profiles')
                    ->whereRaw('user_profiles.user_id = users.id')
                    ->whereRaw('user_profiles.banned = "' . $request['banned'] . '"');
            });
        }

        // search by subscription status
        if (isset($request['subscription']) && !empty($request['subscription'])) {
            $members->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('user_plans')
                    ->whereRaw('user_plans.user_id = users.id')
                    ->whereRaw('user_plans.status = "' . $request['subscription'] . '"');
            });
        }

        // search by plan
        if (isset($request['plan']) && !empty($request['plan'])) {
            $members->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('user_plans')
                    ->whereRaw('user_plans.user_id = users.id')
                    ->whereRaw('user_plans.plan_id = "' . $request['plan'] . '"');
            });
        }

        $members = $members->orderBy('users.id', 'desc')->get();

        return $members;
    }
}
