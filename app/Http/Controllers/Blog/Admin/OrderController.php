<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminOrderSaveRequest;
use App\Models\Admin\Order;
use App\Repositories\Admin\MainRepository;
use App\Repositories\Admin\OrderRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class OrderController extends AdminBaseController
{
    private $orderRepository;

    public function __construct()
    {
        $this->orderRepository = app(OrderRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perpage     = 5;
        $countOrders = MainRepository::getCountOrders();
        $paginator   = $this->orderRepository->getAllOrders(10);
        MetaTag::setTags([
            'title' => 'List of orders'
        ]);

        return view('blog.admin.order.index', compact('countOrders', 'paginator'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = $this->orderRepository->getId($id);

        if (empty($item)) {
            abort(404);
        }

        $order = $this->orderRepository->getOneOrder($item->id);

        if (!$order) {
            abort(404);
        }

        $orderProducts = $this->orderRepository->getAllOrderProductsId($item->id);

        MetaTag::setTags(['title' => "Order №{$item->id}"]);


        return view('blog.admin.order.edit', compact('item', 'order', 'orderProducts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $st = $this->orderRepository->changeStatusOnDelete($id);
        if ($st) {
            $result = Order::destroy($id);
            if ($result) {
                return redirect()
                    ->route('blog.admin.orders.index')
                    ->with(['success' => "Record id {$id} was deleted successfully"]);
            } else {
                return back()->withErrors(['msg' => 'Delete error!']);
            }
        } else {
            return back()->withErrors(['msg' => 'Status was not changed']);
        }
    }

    public function forcedestroy($id)
    {
        if (empty($id)){
            return back()->withErrors(['msg' => 'Record not found']);
        }

        $res = \DB::table('orders')
            ->delete($id);

        if ($res) {
            return redirect()
                ->route('blog.admin.orders.index')
                ->with(['success' => "Record id [$id] was deleted from DB successfully"]);
        } else {
            return back()->withErrors(['msg' => 'Delete error!']);
        }
    }

    public function change($id)
    {
        $result = $this->orderRepository->changeStatusOrder($id);
        if ($result) {
            return redirect()->route('blog.admin.orders.edit', $id)->with(['success' => 'Saved success']);
        } else {
            return back()->withErrors(['msg' => 'Save error!']);
        }
    }

    public function save(AdminOrderSaveRequest $request, $id)
    {
        $result = $this->orderRepository->saveOrderComment($id);
        $result = $this->orderRepository->changeStatusOrder($id);
        if ($result) {
            return redirect()->route('blog.admin.orders.edit', $id)->with(['success' => 'Saved success']);
        } else {
            return back()->withErrors(['msg' => 'Save error!']);
        }
    }
}
