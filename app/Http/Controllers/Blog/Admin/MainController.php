<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\MainRepository;
use App\Repositories\Admin\OrderRepository;
use App\Repositories\Admin\ProductRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class MainController extends AdminBaseController
{
    private $orderRepository;
    private $productRepository;

    public function __construct()
    {
        $this->orderRepository = app(OrderRepository::class);
        $this->productRepository = app(ProductRepository::class);
    }

    public function index(){
        $countOrders = MainRepository::getCountOrders();
        $countUsers = MainRepository::getCountUsers();
        $countProducts = MainRepository::getCountProducts();
        $countCategories = MainRepository::getCountCategories();

        $perpage = 4;

        $lastOrders = $this->orderRepository->getAllOrders($perpage);
        $lastProducts = $this->productRepository->getLastProducts($perpage);

        MetaTag::setTags([
            'title' => 'Admin Panel'
        ]);

        return view('blog.admin.main.index', compact('countOrders', 'countUsers', 'countProducts', 'countCategories', 'lastOrders', 'lastProducts'));
    }
}
