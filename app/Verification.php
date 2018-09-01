<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    //
    protected $table = 'verification';
    protected $fillable = ['verification_account','verification_type','verification_code','send_at'];

    public $timestamps = false;

    static function makeVerifyCode($phone)
    {
        $data['verification_code'] = rand(100000,999999);
        $data['verification_account'] = $phone;
        $data['send_at'] = time();
        $obj = self::where('verification_account',$phone)->first();
        if ($obj){
            $obj->update($data);
        }else {
            self::create($data);
        }
        return $data['verification_code'];
    }
}
