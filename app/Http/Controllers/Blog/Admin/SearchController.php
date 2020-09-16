<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;

use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class SearchController extends AdminBaseController
{
    public function index(Request $request){
        $query = trim($request->search) ?? null;

        $products = \DB::table('products')
            ->where('title', 'LIKE', '%' . $query . '%')
            ->get()
            ->toArray();

        $currency = \DB::table('currencies')
            ->where('base', '1')
            ->first();

        MetaTag::setTags(['title' => 'Результаты поиска']);
        return view('blog.admin.search.result', compact('query', 'currency', 'products'));
    }

    public function search(Request $request){
        $search = $request->get('term');
        $result = \DB::table('products')
            ->where('title', 'LIKE', '%' . $search . '%')
            ->pluck('title');
        return response()->json($result);
    }
}
