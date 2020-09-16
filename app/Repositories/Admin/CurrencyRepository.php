<?php


namespace App\Repositories\Admin;


use App\Models\Admin\Currency;
use App\Repositories\CoreRepository;

class CurrencyRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Currency::class;
    }

    public function getAllCurrency()
    {
        $curr = $this->startConditions()::all();
        return $curr;
    }

    public function getInfoProduct($id){
        $product = $this->startConditions()
            ->find($id);
        return $product;
    }

    public function switchBaseCurr()
    {
        $id = Currency::where('base',2)->pluck('id')->toArray();
        $id = $id[0];
        $new = Currency::find($id);
        $new->base = '0';
        $new->save();
    }

    public function deleteCurrency($id)
    {
        $delete = $this->startConditions()
            ->where('id', $id)
            ->forceDelete();
        return $delete;
    }
}
