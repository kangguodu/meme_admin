<?php

namespace App\Jobs;

use App\Api\Merchant\Repositories\RebateOrderRepository;
use App\RebateOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @deprecated since 0.1. unused
 * Class RebateOrderJob
 * @package App\Jobs
 */

class RebateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }


    public function handle()
    {
        //\Log::debug("rebate job is runing");
        (new RebateOrderRepository())->rebateOrderFromJob($this->id);
    }
}
