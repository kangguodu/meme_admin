<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-9
 * Time: 上午11:33
 */

namespace App\Admin\Provider;

use Encore\Admin\Widgets\Navbar as Nav;

class NavBar
{
    private $navbar;

    public function __construct(Nav $navbar)
    {
        $this->navbar = $navbar;
    }

    /**
     * @return $this
     */
    public function back()
    {
        $back = "<a class='btn btn-md btn-default pull-right' href='javascript:window.history.go(-1)'><i class='fa fa-arrow-left'></i> 返回</a>";
        $this->navbar->add($back);
        return $this;
    }
    /**
     * @param $url 鏈接
     * @param $style 樣式，默認success
     * @return $this
     */
    public function add($url,$style='success')
    {
        $item = "<a class='btn btn-md btn-{$style} pull-right' href=''><i class='fa fa-plus'></i> 增加</a>";
        $this->navbar->add($item);
        return $this;
    }
    public function render()
    {
        return $this->navbar;
    }
}