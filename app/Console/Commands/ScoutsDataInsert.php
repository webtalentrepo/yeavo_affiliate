<?php

namespace App\Console\Commands;

use App\Http\Repositories\CommissionJunction;
use App\Models\Scout;
use Illuminate\Console\Command;

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

                if ($re && isset($re['advertisers'])) {
                    if (isset($re['advertisers']['advertiser'])) {
                        foreach ($re['advertisers']['advertiser'] as $key => $row) {
                            if (!is_array($row['advertiser-id'])) {
                                $aid = $row['advertiser-id'];
                            } else {
                                if (is_array($row['advertiser-id'])) {
                                    if (sizeof($row['advertiser-id']) > 0) {
                                        $aid = $row['advertiser-id'][0];
                                    } else {
                                        $aid = '';
                                    }
                                } else {
                                    $aid = $row['advertiser-id'];
                                }
                            }

                            $scout = Scout::where('advertiser_id', $aid)->first();
                            if ($scout) {
                                if ($scout->deleted_flag || $scout->edited_flag) {
                                    continue;
                                }
                            } else {

                            }
                        }
                    }
                }
            }
        }

        return 0;
    }
}
