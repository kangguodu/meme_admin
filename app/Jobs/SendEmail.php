<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        if($data['flag'] == 1){
            $email = 'kevin.lee@oneonegp.com';
           $flag = Mail::send('email.storeapply',['data'=>$this->data],function($message) use ($email){
                $to = $email;
                $message ->to($to)->subject('MeMecoins 商家入駐申請');
            });
            if($flag){
                \Log::info('邮件发送成功');
            }else{
                \Log::info('邮件发送失败');
            }
        }else if($data['flag'] == 2){
            $email = 'kevin.lee@oneonegp.com';
            $flag = Mail::send('email.cooperation',['data'=>$data],function($message) use ($email){
                $to = $email;
                $message->to($to)->subject('MeMecoins 我要合作申請');
            });
            if($flag){
                \Log::info('邮件发送成功');
            }else{
                \Log::info('邮件发送失败');
            }
        }

    }
}
