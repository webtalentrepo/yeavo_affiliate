<?php

namespace App\Console\Commands;

use App\Http\Repositories\CommissionJunction;
use App\Models\Product;
use Illuminate\Console\Command;

class ChildProductsInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'childproduct:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert CJ child products for advertisers';

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
        $cj_data = Product::where('network', 'cj.com')->get();

        if ($cj_data) {
            $cj = new CommissionJunction();
            foreach ($cj_data as $row) {
                $child_products = $cj->getProductDetails($row['site_id']);

                if ($child_products) {
                    $cj->setChildProducts($child_products, $row['id']);
                }
            }
        }

        return 0;
    }
}
