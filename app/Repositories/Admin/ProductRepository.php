<?php


namespace App\Repositories\Admin;


use App\Models\Admin\Product;
use App\Repositories\CoreRepository;

class ProductRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Product::class;
    }

    public function getLastProducts($perpage){
        $lastProducts = $this->startConditions()
            ->orderBy('id','desc')
            ->limit($perpage)
            ->paginate($perpage);

        return $lastProducts;
    }
}
