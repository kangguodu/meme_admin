<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 10:57
 */

namespace App\Common\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqttNotificationService
{

    public static function sendStoreProcessMessage($store_id,$order_id,$operation = 'new'){
        try{
            $exchange = 'amq.topic';
            $queue = 'meme_store_order';
            $connection = new AMQPStreamConnection('192.168.1.80', 5672, 'memecoins', 'memecoins','/');
            $channel = $connection->channel();
            $channel->queue_declare($queue, false, true, false, true);

            $channel->exchange_declare($exchange, 'topic', false, true, false);
            $channel->queue_bind($queue, $exchange,$queue);
            $message = json_encode(array(
                'store_id' => $store_id,
                'order_id' => $order_id,
                'operation' => $operation
            ));
            \Log::debug("store mqtt message: {$message}");
            $msg = new AMQPMessage($message);
            $channel->basic_publish($msg, $exchange, $queue);
            $channel->close();
            $connection->close();
        }catch (\Exception $e){
            \Log::error("send store mqtt message fail:".$e->getMessage().$e->getFile().$e->getLine());
        }
    }

    public static function sendMemberMessage($member_id,$order_id,$operation = 'complete',$rebate = 0){
        try{
            $exchange = 'amq.topic';
            $queue = 'meme_user_order';
            $connection = new AMQPStreamConnection('192.168.1.80', 5672, 'memecoins', 'memecoins','/');
            $channel = $connection->channel();
            $channel->queue_declare($queue, false, true, false, true);

            $channel->exchange_declare($exchange, 'topic', false, true, false);
            $channel->queue_bind($queue, $exchange,$queue);
            $message = json_encode(array(
                'member_id' => $member_id,
                'order_id' => $order_id,
                'operation' => $operation,
                'order_rebate' => $rebate
            ));
            \Log::debug("member mqtt message: {$message}");
            $msg = new AMQPMessage($message);
            $channel->basic_publish($msg, $exchange, $queue);
            $channel->close();
            $connection->close();
        }catch (\Exception $e){
            \Log::error("send member mqtt message fail:".$e->getMessage().$e->getFile().$e->getLine());
        }

    }
}