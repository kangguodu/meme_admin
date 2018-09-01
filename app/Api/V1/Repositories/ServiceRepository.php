<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 10:41
 */

namespace App\Api\V1\Repositories;

use App\ServiceAutoReply;
class ServiceRepository
{

    public function keyword(){
        return (new \App\ServiceKeyword())->get();
    }

    public function autoreply($root_id){
    	// $node = ServiceAutoReply::where('id', '=', '3')->first();
    	// $result = ServiceAutoReply::whereBetween('lft',[$node->lft,$node->rgt])->where('root','=',$node->id)->select('id','name','lft','rgt')->get();

    	$sql = 'SELECT id,name as title, (SELECT id 
       FROM service_auto_reply t2 
       WHERE t2.lft < t1.lft AND t2.rgt > t1.rgt AND root= ?  
       ORDER BY t2.rgt-t1.rgt ASC
       LIMIT 1) 
AS parent_id FROM service_auto_reply t1 where root = ?
ORDER BY (rgt-lft) DESC';
		$result = \DB::select($sql,[$root_id,$root_id]);
		if(count($result) > 0){
			unset($result[0]);
			// foreach($result as $val){
			// 	array_push($autoReply,$val);
			// }
			 $result = array_merge($result,array());
		}else{
			$result = array();
		}
		
		
    	return $result;
       // return (new \App\ServiceAutoReply())->get();
    }
}