<?php

namespace App\Console\Commands;

use App\Http\Repositories\CommissionJunction;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScoutsDataInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scoutsdata:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Affiliate Scouts Data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $links = ['clickbank.com', 'cj.com', 'jvzoo.com'];

        for ($i = 0; $i < count($links); $i++) {
            if ($links[$i] == 'cj.com') {
                $cj = new CommissionJunction();
                $re = $cj->getMerchants([]);

//                Log::info(json_encode($re));

                if ($re) {
                    $cj->saveDataToTable($re, $links[$i]);
                }
            }
        }

        return 0;
    }
}
