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

class AdminLang
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
        App::setLocale('zh_TW');

        return $next($request);
    }
}