<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogAttrsFilterAddRequest;
use App\Http\Requests\BlogGroupFilterAddRequest;
use App\Models\Admin\AttributeGroup;
use App\Models\Admin\AttributeValue;
use App\Repositories\Admin\FilterAttrsRepository;
use App\Repositories\Admin\FilterGroupRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class FilterController extends AdminBaseController
{
    private $filterGroupRepository;
    private $filterAttrsRepository;

    public function __construct()
    {
        $this->filterAttrsRepository = app(FilterAttrsRepository::class);
        $this->filterGroupRepository = app(FilterGroupRepository::class);
    }

    public function attributeGroup()
    {
        $attrsGroup = $this->filterGroupRepository->getAllGroupsFilter();
        MetaTag::setTags(['title' => 'Группы фильтров']);
        return view('blog.admin.filter.attribute-group', compact('attrsGroup'));
    }

    public function groupAdd(BlogGroupFilterAddRequest $request)
    {
        if ($request->isMethod('post')) {
            $data  = $request->input();
            $group = (new AttributeGroup())->create($data);
            $group->save();

            if ($group) {
                return redirect('/admin/filter/group-add-group')
                    ->with(['success' => 'Добавлена новая группа']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка создания новой группы"])
                    ->withInput();
            }
        } else {
            MetaTag::setTags(['title' => 'Новая группа фильтров']);
            return view('blog.admin.filter.group-add-group');
        }
    }

    public function groupEdit(BlogGroupFilterAddRequest $request, $id)
    {
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [$id] не найдена!"]);
        }
        if ($request->isMethod('post')) {
            $group        = AttributeGroup::find($id);
            $group->title = $request->title;
            $group->save();

            if ($group) {
                return redirect('/admin/filter/group-filter')
                    ->with(['success' => 'Успешно удалено']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка изменения"])
                    ->withInput();
            }
        } else {
            $group = $this->filterGroupRepository->getInfoProduct($id);
            MetaTag::setTags(['title' => 'Редактирование группы']);
            return view('blog.admin.filter.group-edit', compact('group'));
        }
    }

    public function groupDelete($id)
    {
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [{$id}] не найдена!"]);
        }
        $count = $this->filterAttrsRepository->getCountFilterAttrsById($id);
        if ($count) {
            return back()->withErrors(['msg' => "Удаление невозможно, в группе есть атрибуты"]);
        }
        $delete = $this->filterGroupRepository->deleteGroupFilter($id);
        if ($delete) {
            return back()->with(['success' => "Удалено"]);
        } else {
            return back()->withErrors(['msg' => "Ошибка удаления"]);
        }

    }

    public function attributeFilter()
    {
        $attrs = $this->filterAttrsRepository->getAllAttrsFilter();
        $count = $this->filterGroupRepository->getCountGroupFilter();

        MetaTag::setTags(['title' => 'Фильтры']);
        return view('blog.admin.filter.attribute', compact('attrs', 'count'));
    }

    public function attributeAdd(BlogAttrsFilterAddRequest $request)
    {
        if ($request->isMethod('post')) {
            $uniqueName = $this->filterAttrsRepository->checkUnique($request->value);
            if ($uniqueName) {
                return redirect('/admin/filter/attrs-add')
                    ->withErrors(['msg' => 'Такой название фильтра уже есть.'])
                    ->withInput();
            }

            $data = $request->input();
            $atrr = (new AttributeValue())->create($data);
            $atrr->save();
            if ($atrr) {
                return redirect('/admin/filter/attrs-add')
                    ->with(['success' => 'Добавлен новый фильтр']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка создания фильтра"])
                    ->withInput();
            }

        } else {
            if ($request->isMethod('get')) {
                $group = $this->filterGroupRepository->getAllGroupsFilter();
                MetaTag::setTags(['title' => 'Новый атрибут для фильтра']);
                return view('blog.admin.filter.attrs-add', compact('group'));
            }
        }
    }

    public function attrEdit(BlogAttrsFilterAddRequest $request, $id)
    {
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [{$id}] не найдена!"]);
        }
        if ($request->isMethod('post')) {

            $attr = AttributeValue::find($id);
            $attr->value = $request->value;
            $attr->attr_group_id = $request->attr_group_id;
            $attr->save();

            if ($attr) {
                return redirect('/admin/filter/attributes-filter')
                    ->with(['success' => 'Успешно изменено']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка изменения"])
                    ->withInput();
            }
        } else {
            if ($request->isMethod('get')) {

                $atrr = $this->filterAttrsRepository->getInfoProduct($id);
                $group = $this->filterGroupRepository->getAllGroupsFilter();
                MetaTag::setTags(['title' => 'Редактирование фильтра']);
                return view('blog.admin.filter.attrs-edit', compact('group','atrr'));
            }
        }
    }

    public function attrDelete($id)
    {
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [{$id}] не найдена!"]);
        }

        $delete = $this->filterAttrsRepository->deleteAttrFilter($id);

        if ($delete) {
            return back()->with(['success' => "Удалено"]);
        } else {
            return back()->withErrors(['msg' => "Ошибка удаления"]);
        }
    }
}
