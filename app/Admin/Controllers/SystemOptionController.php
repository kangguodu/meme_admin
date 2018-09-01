<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Options;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemOptionController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('system');

        $options = Options::all();

        return Admin::content(function (Content $content) use ($options){
            $content->header('系统设置');
            $content->description(trans('lang.list'));

            $form = new Form();
            foreach ($options as $option) {
                $form->text($option->option_name, trans("lang.{$option->option_name}"))
                    ->default($option->option_value);
            }
            $content->body($form);
        });
    }

    public function store(Request $request)
    {
        Permission::check('system');

        $rules = array();
        $data = $request->except(['_token', '_method']);

        $options = Options::get(['option_name']);

        foreach ($options as $option) {
            $rules[$option->option_name] = 'required';
        }
        $validator = Validator::make($data, $rules);
        if ($validator->fails()){
            admin_toastr($validator->errors()->first(), 'error');
            return back();
        }

        foreach ($data as $name => $value)
        {
            if ($option = Options::where('option_name', $name)->first())
                $option->update(['option_value' => $value]);
        }
        admin_toastr('更新成功', 'success');
        return redirect(admin_url('system_option'));
    }
}
