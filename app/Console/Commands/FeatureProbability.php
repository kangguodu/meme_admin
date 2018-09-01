<?php

namespace App\Console\Commands;

use App\StoreAccount;
use Illuminate\Console\Command;

class FeatureProbability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FeatureProbability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '使設定的撥數生效';

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
     * @return mixed
     */
    public function handle()
    {
       \Log::debug("执行拨数生效时间".date('Y-m-d H:i:s'));
       $time = time();
       $result = (new StoreAccount())->where('feature_probability','>',0)
            ->where('feature_probability_time','<=',$time)
           ->where('feature_probability_time','>',0)
            ->select([
                'id',
                'store_id',
                'probability',
                'feature_probability',
                'feature_probability_time',
            ])->get();
       if($result->isNotEmpty()){
           foreach ($result as $key=>$value){
               $value->probability = $value->feature_probability;
               $value->feature_probability = 0;
               $value->feature_probability_time = 0;
               $value->save();
           }
       }
    }
}
