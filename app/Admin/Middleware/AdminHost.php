<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-9
 * Time: 上午11:07
 */

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class AdminHost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()||$request->pjax())
            return $next($request);

        $adminHost = admin_url('');
        $appHost = url('/');
        echo "<meta name='admin_host' content='{$adminHost}'/>";
        echo "<meta name='app_host' content='{$appHost}'/>";
        return $next($request);
    }
}