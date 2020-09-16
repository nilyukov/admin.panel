<?php


namespace App\Repositories\Admin;


use App\Models\Admin\AttributeGroup;
use App\Repositories\CoreRepository;

class FilterGroupRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return AttributeGroup::class;
    }

    public function getInfoProduct($id)
    {
        $product = $this->startConditions()
            ->find($id);
        return $product;
    }

    public function getAllGroupsFilter()
    {
        $attrsGroup = \DB::table('attribute_groups')->get()->all();
        return $attrsGroup;
    }

    public function deleteGroupFilter($id)
    {
        $delete = $this->startConditions()
            ->where('id', $id)
            ->forceDelete();
        return $delete;
    }

    public function getCountGroupFilter()
    {
        $count = \DB::table('attribute_values')->count();
        return $count;
    }
}
