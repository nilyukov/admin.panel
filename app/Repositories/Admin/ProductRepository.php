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

    public function getLastProducts($perpage)
    {
        $lastProducts = $this->startConditions()
            ->orderBy('id', 'desc')
            ->limit($perpage)
            ->paginate($perpage);

        return $lastProducts;
    }

    public function getAllProducts($perpage)
    {
        $products = $this->startConditions()
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.title AS cat')
            ->orderBy(\DB::raw('LENGTH(products.title)', 'products.title'))
            ->limit($perpage)
            ->paginate($perpage);
        return $products;
    }

    public function getProductsByName($q)
    {
        $products = \DB::table('products')
            ->select('id', 'title')
            ->where('title', 'LIKE', ["%{$q}%"])
            ->limit(8)
            ->get();
        return $products;
    }

    public function uploadImg($name, $wmax, $hmax)
    {
        $uploaddir  = 'uploads/single/';
        $ext        = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $name));
        $uploadfile = $uploaddir . $name;
        \Session::put('single', $name);
        self::resize($uploadfile, $uploadfile, $wmax, $hmax, $ext);
    }

    /**  Resize Images for My needs
     * @param $target
     * @param $dest
     * @param $wmax
     * @param $hmax
     * @param $ext
     */
    public static function resize($target, $dest, $wmax, $hmax, $ext)
    {
        list($w_orig, $h_orig) = getimagesize($target);
        $ratio = $w_orig / $h_orig;

        if (($wmax / $hmax) > $ratio) {
            $wmax = $hmax * $ratio;
        } else {
            $hmax = $wmax / $ratio;
        }

        $img = "";
        // imagecreatefromjpeg | imagecreatefromgif | imagecreatefrompng
        switch ($ext) {
            case("gif"):
                $img = imagecreatefromgif($target);
                break;
            case("png"):
                $img = imagecreatefrompng($target);
                break;
            default:
                $img = imagecreatefromjpeg($target);
        }
        $newImg = imagecreatetruecolor($wmax, $hmax);
        if ($ext == "png") {
            imagesavealpha($newImg, true);
            $transPng = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
            imagefill($newImg, 0, 0, $transPng);
        }
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $wmax, $hmax, $w_orig,
            $h_orig); // копируем и ресайзим изображение
        switch ($ext) {
            case("gif"):
                imagegif($newImg, $dest);
                break;
            case("png"):
                imagepng($newImg, $dest);
                break;
            default:
                imagejpeg($newImg, $dest);
        }
        imagedestroy($newImg);
    }

    public function uploadGallery($name, $wmax, $hmax)
    {
        $uploaddir  = 'uploads/gallery/';
        $ext        = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $_FILES[$name]['name']));
        $newName   = md5(time()) . ".$ext";
        $uploadfile = $uploaddir . $newName;
        \Session::push('gallery', $newName);
        if (@move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile)) {
            self::resize($uploadfile, $uploadfile, $wmax, $hmax, $ext);
            $res = ["file" => $newName];

            echo json_encode($res);
        }
    }

    public function getImg(Product $product){
        clearstatcache();
        if(!empty(\Session::get('single'))){
            $name = \Session::get('single');
            $product->img = $name;
            \Session::forget('single');
        }
        if(empty(\Session::get('single')) && !is_file(WWW . '/uploads/single/'  . $product->img)){
            $product->img = null;
        }
    }

    public function editFilter($id, $data){
        $filter = \DB::table('attribute_products')
            ->where('product_id', $id)
            ->pluck('attr_id')
            ->toArray();

        if(empty($data['attrs']) && !empty($filter)){
            \DB::table('attribute_products')
                ->where('product_id', $id)
                ->delete();
        }

        if(empty($filter) && !empty($data['attrs'])){
            $sqlPart = '';
            foreach ($data['attrs'] as $attr) {
                $sqlPart .= "($attr, $id),";
            }

            $sqlPart = rtrim($sqlPart, ',');
            \DB::insert("INSERT INTO attribute_products (attr_id, product_id) VALUES ($sqlPart)");
        }

        if(!empty($data['attrs'])){
            $result = array_diff($filter, $data['attrs']);
            if($result){
                \DB::table('attribute_products')
                    ->where('product_id', $id)
                    ->delete();
                $sqlPart = '';
                foreach ($data['attrs'] as $attr) {
                    $sqlPart .= "($attr, $id),";
                }
                $sqlPart = rtrim($sqlPart, ',');
                \DB::insert("INSERT INTO attribute_products (attr_id, product_id) VALUES ($sqlPart)");
            }
        }
    }

    public function editRelatedProduct($id, $data){
        $relatedProduct = \DB::table('related_products')
            ->where('product_id', $id)
            ->pluck('related_id')
            ->toArray();

        if(empty($data['related']) && !empty($relatedProduct)){
            \DB::table('related_products')
                ->where('product_id', $id)
                ->delete();
        }

        if(empty($relatedProduct) && !empty($data['related'])){
            $sqlPart = '';

            foreach ($data['related'] as $v) {
                $v = (int)$v;
                $sqlPart .= "($id, $v),";
            }

            $sqlPart = rtrim($sqlPart, ',');
            \DB::insert("INSERT INTO related_products (product_id, related_id) VALUES ($sqlPart)");
        }

        if(!empty($data['related'])){
            $result = array_diff($relatedProduct, $data['related']);
            if (!(empty($result)) || count($relatedProduct) != count($data['related'])) {
                \DB::table('related_products')
                    ->where('product_id', $id)
                    ->delete();
                $sql_part = '';
                foreach ($data['related'] as $v) {
                    $sql_part .= "($id, $v),";
                }
                $sql_part = rtrim($sql_part, ',');
                \DB::insert("insert into related_products (product_id, related_id) VALUES $sql_part");
            }
        }
    }

    public function saveGallery($id){
        if (!empty(\Session::get('gallery'))) {
            $sqlPart = '';
            foreach (\Session::get('gallery') as $v) {
                $sqlPart .= "('$v', $id),";
            }
            $sqlPart = rtrim($sqlPart, ',');
            \DB::insert("insert into galleries (img, product_id) VALUES $sqlPart");
            \Session::forget('gallery');
        }
    }

    public function getInfoProduct($id){
        $product = $this->startConditions()->find($id);
        return $product;
    }

    public function getFiltersProduct($id){
        $filter = \DB::table('attribute_products')
            ->select('attr_id')
            ->where('product_id', $id)
            ->pluck('attr_id')
            ->all();
        return $filter;
    }

    public function getRelatedProducts($id){
        $relatedProducts = $this->startConditions()
            ->join('related_products', 'products.id', '=', 'related_products.related_id')
            ->select('products.title', 'related_products.related_id')
            ->where('related_products.product_id', $id)
            ->get();
        return $relatedProducts;
    }

    public function getGallery($id){
        $gallery = \DB::table('galleries')
            ->where('product_id', $id)
            ->pluck('img')
            ->all();
        return $gallery;
    }

    public function returnStatusOne($id){
        if(isset($id)){
            $st = \DB::update("UPDATE products SET status = '1' WHERE id = ?", [$id]);
            return $st ? true : false;
        }
    }

    public function deleteStatusOne($id){
        if(isset($id)){
            $st = \DB::update("UPDATE products SET status = '0' WHERE id = ?", [$id]);
            return $st ?  true : false;
        }
    }

    public function deleteImgGalleryFromPath($id){
        $galleryImg = \DB::table('galleries')
            ->select('img')
            ->where('product_id', $id)
            ->pluck('img')
            ->all();
        $singleImg = \DB::table('products')
            ->select('img')
            ->where('id', $id)
            ->pluck('img')
            ->all();

        if(!empty($galleryImg)){
            foreach ($galleryImg as $img) {
                unlink("uploads/gallery/$img");
            }
        }

        if(!empty($singleImg)){
            unlink("uploads/single/$singleImg[0]");
        }
    }

    public function deleteFromDB($id){
        if(isset($id)){
            $relatedProduc = \DB::delete("DELETE FROM related_products WHERE product_id =?", [$id]);
            $attrProd = \DB::delete("DELETE FROM attribute_products WHERE product_id = ?", [$id]);
            $product = \DB::delete("DELETE FROM products WHERE id = ?", [$id]);

            if($product){
                return true;
            }

        }
    }
}
