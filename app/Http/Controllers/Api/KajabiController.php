<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class KajabiController extends Controller
{
    public function index()
    {
        try {
            $inputData = json_decode(file_get_contents('php://input'), true);

            Log::info(json_encode($inputData));
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 400);
        }
    }
}
