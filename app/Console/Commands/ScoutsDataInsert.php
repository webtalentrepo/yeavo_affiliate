<?php

namespace App\Console\Commands;

use App\Http\Repositories\CommissionJunction;
use App\Models\Product;
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
                            $sale = '0';
                            $c_name = '';
                            if (isset($row['actions'])) {
                                if (isset($row['actions']['action'])) {
                                    if (isset($row['actions']['action']['commission'])) {
                                        if (isset($row['actions']['action']['name'])) {
                                            $c_name = $row['actions']['action']['name'];
                                        }

                                        if (isset($row['actions']['action']['commission']['default'])) {
                                            $sale = $row['actions']['action']['commission']['default'];
                                        }
                                    } else {
                                        if (isset($row['actions']['action'][0]) && isset($row['actions']['action'][0]['commission'])) {
                                            $sale = $row['actions']['action'][0]['commission']['default'];

                                            if (isset($row['actions']['action'][0]['name'])) {
                                                $c_name = $row['actions']['action'][0]['name'];
                                            }
                                        }
                                    }
                                }
                            }

                            $aid = '';
                            if (!is_array($row['advertiser-id'])) {
                                $aid = $row['advertiser-id'];
                            } else {
                                if (is_array($row['advertiser-id'])) {
                                    if (sizeof($row['advertiser-id']) > 0) {
                                        $aid = $row['advertiser-id'][0];
                                    }
                                } else {
                                    $aid = $row['advertiser-id'];
                                }
                            }

                            if (is_array($row['advertiser-name'])) {
                                if (sizeof($row['advertiser-name']) > 0) {
                                    $name = $row['advertiser-name'][0];
                                } else {
                                    $name = $c_name;
                                }
                            } else {
                                $name = $row['advertiser-name'];
                            }

                            $commission = (float)$sale;
                            $c_unit_ary = explode($commission, $sale);
                            $c_unit = '';

                            if (isset($c_unit_ary[1]) && trim($c_unit_ary[1]) == '%') {
                                $c_unit = '%';
                            } else {
                                $c_unit_ary = explode(' ', $sale);

                                if (isset($c_unit_ary[1]) && !is_nan($c_unit_ary[1] * 1) && is_numeric($c_unit_ary[1] * 1)) {
                                    $c_unit = $c_unit_ary[0];
                                }
                            }

                            $scout = Product::where('site_id', $aid)
                                ->where('network', $links[$i])
                                ->where('category', $row['primary-category']['parent'])
                                ->where('child_category', $row['primary-category']['child'])
                                ->first();
                            if ($scout) {
                                if ($scout->deleted_flag || $scout->edited_flag) {
                                    continue;
                                }
                            } else {
                                $scout = new Product();
                            }

                            $scout->network = $links[$i];
                            $scout->category = isset($row['primary-category']['parent']) ? $row['primary-category']['parent'] : '';
                            $scout->child_category = isset($row['primary-category']['child']) ? $row['primary-category']['child'] : '';
                            $scout->site_id = $aid;
                            $scout->popular_rank = $row['network-rank'];
                            $scout->p_title = $name;
                            $scout->p_description = $c_name;
                            $scout->p_commission = $commission;
                            $scout->p_commission_unit = $c_unit;
                            $scout->p_gravity = $row['network-rank'];
                            $scout->p_percent_sale = ($c_unit == '%') ? $commission : 0;
                            $scout->deleted_flag = 0;
                            $scout->edited_flag = 0;

                            $scout->save();
                        }
                    }
                }
            }
        }

        return 0;
    }
}
