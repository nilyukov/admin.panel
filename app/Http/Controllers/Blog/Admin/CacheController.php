<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;

use App\SBlog\Core\Cache;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    public function index()
    {
        MetaTag::setTags(['title' => 'Очистка кэша']);
        return view('blog.admin.cache.index');
    }


    public function delete($key)
    {
        $value = isset($key) ? $key : null;
        $cache = Cache::instance();

        switch ($value) {
            case 'category':
                $cache->delete('category');
                $cache->delete('blog_menu');
                break;

            case 'filter':
                $cache->delete('filter_group');
                $cache->delete('filter_attrs');
                break;
            default:
                return back()
                    ->withErrors(['msg' => 'Кэш не удален']);
        }
        return redirect()
            ->route('blog.admin.cache')
            ->with(['success' => "Кэш [{$value}] удален"]);
    }
}
