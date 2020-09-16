<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductsCreateRequest;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use App\Repositories\Admin\MainRepository;
use App\Repositories\Admin\ProductRepository;
use App\SBlog\Core\BlogApp;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class ProductController extends AdminBaseController
{
    private $productRepository;

    public function __construct()
    {
        $this->productRepository = app(ProductRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perpage = 10;

        $products      = $this->productRepository->getAllProducts($perpage);
        $countProducts = MainRepository::getCountProducts();
        MetaTag::setTags(['titile' => 'Products list']);
        return view('blog.admin.product.index', compact('products', 'countProducts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $item = new Category();

        MetaTag::setTags(['title' => 'Product create']);
        return view('blog.admin.product.create', [
            'categories' => Category::with('children')
                ->where('parent_id', '0')
                ->get(),
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
    public function store(AdminProductsCreateRequest $request)
    {
        $data = $request->input();

        $product              = (new Product())->create($data);
        $id                   = $product->id;
        $product->status      = $request->status ? '1' : '0';
        $product->hit         = $request->hit ? '1' : '0';
        $product->category_id = $request->parent_id ?? '0';
        $this->productRepository->getImg($product);
        $save = $product->save();
        if ($save) {
            $this->productRepository->editFilter($id, $data);
            $this->productRepository->editRelatedProduct($id, $data);
            $this->productRepository->saveGallery($id);
            return redirect()
                ->route('blog.admin.products.create', [$product->id])
                ->with(['success' => 'Успешно сохранено']);
        } else {
            return back()
                ->withErrors(['msg' => 'Ошибка сохранения'])
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
        $product = $this->productRepository->getInfoProduct($id);
        $id      = $product->id;

        BlogApp::get_instance()->setProperty('parent_id', $product->category_id);
        $filter           = $this->productRepository->getFiltersProduct($id);
        $relatedProducts = $this->productRepository->getRelatedProducts($id);
        $images           = $this->productRepository->getGallery($id);

        MetaTag::setTags(['title' => "Edit product № {$id}"]);
        return view('blog.admin.product.edit', compact('product', 'images', 'filter', 'relatedProducts', 'id'), [
            'categories' => Category::with('children')->where('parent_id', '0')
                ->get(),
            'delimiter' => '-',
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(AdminProductsCreateRequest $request, $id)
    {
        $product = $this->productRepository->getId($id);
        if(empty($product)){
            return back()
                ->withErrors(['msg' => "Запись = [{$id}] не найдена"])
                ->withInput();
        }
        $data = $request->all();
        $result = $product->update($data);
        $product->status = $request->status ? '1' : '0';
        $product->hit = $request->hit ? '1' : '0';
        $product->category_id = $request->parent_id ?? $product->category_id;
        $this->productRepository->getImg($product);
        $save = $product->save();

        if($result && $save){
            $this->productRepository->editFilter($id, $data);
            $this->productRepository->editRelatedProduct($id, $data);
            $this->productRepository->saveGallery($id);
            return redirect()
                ->route('blog.admin.products.edit', [$product->id])
                ->with(['success' => 'Успешно сохранено']);
        } else {
            return back()
                ->withErrors(['msg' => 'Ошибка сохранения'])
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

    public function related(Request $request)
    {
        $q             = isset($request->q) ? htmlspecialchars(trim($request->q)) : null;
        $data['items'] = [];
        $products      = $this->productRepository->getProductsByName($q);

        if ($products) {
            $i = 0;
            foreach ($products as $id => $title) {
                $data['items'][$id]['id']   = $title->id;
                $data['items'][$id]['text'] = $title->title;
                $i++;
            }
        }

        return json_encode($data);
    }

    public function ajaxImage(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('blog.admin.product.include.image_single_edit');
        } else {
            $validator = \Validator::make($request->all(),
                [
                    'file' => 'image|max:5000'
                ],
                [
                    'file.image' => 'File must be a picture (jpeg,png,gif,svg)',
                    'file.max' => 'Error. Max size is 5 mb'
                ]);
            if ($validator->fails()) {
                return ['fail' => true, 'errors' => $validator->errors()];
            }

            $extension = $request->file('file')->getClientOriginalExtension();
            $dit = 'uploads/single/';
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $request->file('file')->move($dit, $filename);
            $wmax = BlogApp::get_instance()->getProperty('img_width');
            $hmax = BlogApp::get_instance()->getProperty('img_height');
            $this->productRepository->uploadImg($filename, $wmax, $hmax);
            return $filename;
        }
    }

    public function deleteImage($filemane){
        \File::delete('uploads/single/' . $filemane);
    }

    /**
     * Add Photo for Gallery Ajax from my.js
     * @param Request $request
     * @return array
     */
    public function gallery(Request $request)
    {
        $validator = \Validator::make($request->all(),
            [
                'file' => 'image|max:5000',
            ],
            [
                'file.image' => 'Файл должен быть картинкой (jpeg, png, bmp, gif, or svg)',
                'file.max' => 'Ошибка! Максимальный вес файла - 5 Мб!',
            ]);
        if ($validator->fails()) {
            return array(
                'fail' => true,
                'errors' => $validator->errors()
            );
        }
        if (isset($_GET['upload'])) {
            $wmax = BlogApp::get_instance()->getProperty('gallery_width');
            $hmax = BlogApp::get_instance()->getProperty('gallery_height');
            $name = $_POST['name'];
            $this->productRepository->uploadGallery($name, $wmax, $hmax);
        }
    }

    public function deleteGallery(){
        $id = $_POST['id'] ?? null;
        $src = $_POST['src'] ?? null;
        if(!$id || !$src){
            return;
        }

        if(\DB::delete("DELETE FROM galleries WHERE product_id = ? AND img = ?", [$id, $src])){
            unlink("uploads/gallery/$src");
            exit('1');
        }
        return;
    }

    public function returnStatus($id){
        if($id){
            $st = $this->productRepository->returnStatusOne($id);

            if($st){
                return redirect()
                    ->route('blog.admin.products.index')
                    ->with(['success' => 'Успешно сохранено']);
            } else {
                return back()
                    ->withErrors(['msg' => 'Ошибка сохранения'])
                    ->withInput();
            }
        }
    }

    public function deleteStatus($id){
        if($id){
            $st = $this->productRepository->deleteStatusOne($id);

            if($st){
                return redirect()
                    ->route('blog.admin.products.index')
                    ->with(['success' => 'Успешно сохранено']);
            } else {
                return back()
                    ->withErrors(['msg' => 'Ошибка сохранения'])
                    ->withInput();
            }
        }
    }

    public function deleteProduct($id){
        if($id){
            $gal = $this->productRepository->deleteImgGalleryFromPath($id);
            $db = $this->productRepository->deleteFromDB($id);

            if($db){
                return redirect()
                    ->route('blog.admin.products.index')
                    ->with(['success' => 'Успешно удалено']);
            } else {
                return back()
                    ->withErrors(['msg' => 'Ошибка удаления'])
                    ->withInput();
            }
        }
    }
}
