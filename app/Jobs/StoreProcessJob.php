<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
/**
 * 處理訂單處理
 * Class StoreProcessJob
 * @package App\Jobs
 */
class StoreProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $order_id;
    protected $user_id;

    protected $host = '192.168.1.80';
    protected $port = 5672;

    /**
     * Create a new job instance.
     * @param integer $store_id
     * @param integer $order_id
     * @return void
     */
    public function __construct($store_id,$order_id,$user_id)
    {
        $this->store_id = $store_id;
        $this->order_id = $order_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exchange = 'amq.topic';
        $queue = 'meme_store_order';
        $connection = new AMQPStreamConnection($this->host, $this->port, 'memecoins', 'memecoins','/');
        $channel = $connection->channel();

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, true);
        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($exchange, 'topic', false, true, false);
        $channel->queue_bind($queue, $exchange,$queue);
        $data = array(
            'store_id' => $this->store_id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id
        );
        $msg = new AMQPMessage(json_encode($data));
        $channel->basic_publish($msg, $exchange, $queue);
        $channel->close();
        $connection->close();
    }
}
