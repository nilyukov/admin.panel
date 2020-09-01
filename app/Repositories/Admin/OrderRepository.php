<?php


namespace App\Repositories\Admin;


use App\Models\Admin\Order;
use App\Repositories\CoreRepository;

class OrderRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Order::class;
    }

    public function getAllOrders($perpage){
        $orders = $this->startConditions()
            ->withTrashed()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('order_products', 'order_products.order_id', '=', 'orders.id')
            ->select('orders.id', 'orders.user_id', 'orders.status', 'orders.created_at', 'orders.updated_at',
                'orders.currency', 'users.name',
                \DB::raw('ROUND(SUM(order_products.price), 2) AS sum'))
            ->groupBy('orders.id')
            ->orderBy('orders.status')
            ->orderBy('orders.id')
            ->toBase()
            ->paginate($perpage);

        return $orders;
    }
}
