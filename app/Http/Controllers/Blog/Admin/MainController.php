<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class MainController extends AdminBaseController
{
    public function index(){
        MetaTag::setTags([
            'title' => 'Admin Panel'
        ]);

        return view('blog.admin.main.index');
    }
}
