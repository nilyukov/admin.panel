<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCategoryUpdateRequest;
use App\Models\Admin\Category;
use App\Repositories\Admin\CategoryRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class CategoryController extends AdminBaseController
{
    private $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = app(CategoryRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $arrMenu = Category::all();
        $menu    = $this->categoryRepository->buildMenu($arrMenu);
        MetaTag::setTags(['title' => 'Category list']);

        return view('blog.admin.category.index', ['menu' => $menu]);
    }

    public function mydel()
    {
        $id = $this->categoryRepository->getRequestId();
        if (!$id) {
            return back()->withErrors(['msg' => 'Error id']);
        }

        $children = $this->categoryRepository->checkChildren($id);

        if ($children) {
            return back()->withErrors(['msg' => 'Deletion is not possible. There are nested categories']);
        }

        $parents = $this->categoryRepository->checkParentsProducts($id);

        if ($parents) {
            return back()->withErrors(['msg' => 'Deletion is not possible. There are products in the category']);
        }

        $delete = $this->categoryRepository->deleteCategory($id);

        if ($delete) {
            return redirect()->route('blog.admin.categories.index')->with(['success' => "Category {$id} was deleted successfully."]);
        } else {
            return back()->withErrors(['msg' => 'Deletion error']);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $item         = new Category();
        $categoryList = $this->categoryRepository->getComboBoxCategories();

        MetaTag::setTags(['title' => 'Category create']);
        return view('blog.admin.category.create', [
            'categories' => Category::with('children')->where('parent_id', '0')->get(),
            'delimiter' => '-',
            'item' => $item
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogCategoryUpdateRequest $request)
    {
        $name = $this->categoryRepository->checkUniqueName($request->title, $request->parent_id);

        if($name){
            return back()
                ->withErrors(['msg' => "The category with the same name is exists."])
                ->withInput();
        }

        $data = $request->input();
        $item = new Category();
        $item->fill($data)->save();

        if ($item) {
            return redirect()
                ->route('blog.admin.categories.create', [$item->id])
                ->with(['success' => 'Successfully save']);
        } else {
            return back()
                ->withErrors(['msg'=>'Save error'])
                ->withInput();
        }
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
        $item = $this->categoryRepository->getEditId($id);
        if (empty($item)){
            abort(404);
        }

        $categoryList = $this->categoryRepository->getComboBoxCategories();

        MetaTag::setTags(['title' => 'Category edit']);
        return view('blog.admin.category.edit',[
            'categories' => Category::with('children')->where('parent_id','0')->get(),
            'delimiter' => '-',
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(BlogCategoryUpdateRequest $request, $id)
    {
        $item = $this->categoryRepository->getEditId($id);
        if (empty($item)){
            return back()
                ->withErrors(['msg' => "Запись = [{$id}] не найдена"])
                ->withInput();
        }

        $data = $request->all();
        $result = $item->update($data);
        if ($result){
            return redirect()
                ->route('blog.admin.categories.edit', $item->id)
                ->with(['success' => "Success"]);
        } else {
            return back()
                ->withErrors(['msg' => 'Save error'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
