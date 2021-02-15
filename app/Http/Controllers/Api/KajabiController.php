<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\UsersRepository;
use App\User;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KajabiController extends Controller
{
    protected $hasher;

    public function __construct(HasherContract $hasher)
    {
        $this->hasher = $hasher;
    }

    public function index(Request $request)
    {
        try {
            $inputData = json_decode(file_get_contents('php://input'), true);
            Log::info(json_encode($inputData));

            if (!$inputData || !isset($inputData)) {
                $inputData = $request->all();
            }

            if (isset($inputData['event']) && isset($inputData['payload'])) {
                // && $inputData['event'] == 'purchase.created'
                $payload = $inputData['payload'];

                $email = $payload['member_email'];
                $name = $payload['member_name'];

                $user_check = User::where('email', $email)->count();

                if (!$user_check) {
                    $user = new User();
                    $user->name = $name;
                    $user->email = $email;
                    $user->password = $this->hasher->make('12345678');
                    $user->save();

                    $userRepo = new UsersRepository();
                    $userRepo->creatByKajabi($user);
                }

            }
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 400);
        }

        return response('ok');
    }
}
