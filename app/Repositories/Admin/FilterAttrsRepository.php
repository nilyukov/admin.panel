<?php


namespace App\Repositories\Admin;


use App\Models\Admin\AttributeValue;
use App\Repositories\CoreRepository;

class FilterAttrsRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return AttributeValue::class;
    }

    public function getInfoProduct($id)
    {
        $product = $this->startConditions()
            ->find($id);
        return $product;
    }

    public function getCountFilterAttrsById($id)
    {
        $count = \DB::table('attribute_values')->where('attr_group_id', $id)->count();
        return $count;
    }

    public function getAllAttrsFilter()
    {
        $attrs = \DB::table('attribute_values')
            ->join('attribute_groups', 'attribute_groups.id', '=', 'attribute_values.attr_group_id')
            ->select('attribute_values.*', 'attribute_groups.title')
            ->paginate(10);
        return $attrs;
    }

    public function checkUnique($name)
    {
        $unique = $this->startConditions()
            ->where('value', $name)
            ->count();
        return $unique;
    }

    public function deleteAttrFilter($id)
    {
        $delete = $this->startConditions()
            ->where('id', $id)
            ->forceDelete();
        return $delete;
    }
}
