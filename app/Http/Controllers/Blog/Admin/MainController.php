<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainController extends AdminBaseController
{
    public function index(){
        return view('blog.admin.main.index');
    }
}
