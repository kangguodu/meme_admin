<?php
namespace App\Http\Controllers\Test;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
class RechargeController extends Controller
{

    public function index(Request $request){
        \Log::debug("Recharge return start: ".json_encode($request->all()));
        try{
            $encoded_data =file_get_contents('php://input');
            \Log::debug("file_get_contents: {$encoded_data}");
        }catch (\Exception $e){
            \log::debug("Recharge return fail: no data");
        }
        \Log::debug("Recharge return end");
        return response('1|OK',200);
    }
}