<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Store extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'store';
    protected $primaryKey = 'id';
    protected $fillable = ['store_id','super_uid','name','branchname','city','district','address','phone','email','company_name','company_tax_no',
        'code','type','type_name','image','service_status','level','remark','avg_cost_status','avg_cost_low','avg_cost_high','facebook','instagram',
        'google_keyword','coordinate','lat','lng','search_keyword','created_at','email_valid','routine_holiday','special_holiday','special_business_day',
        'number','is_return'];

    public $timestamps = false;

    public function account()
    {
    	return $this->hasOne(StoreAccount::class,'store_id','id');
    }
}
