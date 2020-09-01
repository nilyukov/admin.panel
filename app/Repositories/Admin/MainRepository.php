<?php


namespace App\Repositories\Admin;


use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Model;

class MainRepository extends CoreRepository
{
    protected function getModelClass()
    {
        return Model::class;
    }

    public static function getCountOrders()
    {
        $countOrders = \DB::table('orders')
            ->where('status', '0')
            ->get()
            ->count();
        return $countOrders;
    }

    public static function getCountUsers()
    {
        $countUsers = \DB::table('users')
            ->get()
            ->count();
        return $countUsers;
    }

    public static function getCountProducts()
    {
        $countProducts = \DB::table('products')
            ->get()
            ->count();
        return $countProducts;
    }

    public static function getCountCategories()
    {
        $countCategories = \DB::table('categories')
            ->get()
            ->count();
        return $countCategories;
    }
}
