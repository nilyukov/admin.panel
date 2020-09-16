<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCurrencyAddRequest;
use App\Models\Admin\Currency;
use App\Repositories\Admin\CurrencyRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class CurrencyController extends AdminBaseController
{
    private $currencyRepository;

    public function __construct()
    {
        $this->currencyRepository = app(CurrencyRepository::class);
    }

    public function index(){
        $currency = $this->currencyRepository->getAllCurrency();
        MetaTag::setTags(['title' => 'Валюты']);
        return view('blog.admin.currency.index', compact('currency'));
    }

    public function currencyAdd(AdminCurrencyAddRequest $request){
        if($request->isMethod('post')){
            $data = $request->input();
            $currency = (new Currency())->create($data);
            if ($request->base === 'on'){
                $this->currencyRepository->switchBaseCurr();
            }
            $currency->base = $request->base ? '1' : '0';
            $currency->save();

            if ($currency) {
                return redirect('/admin/currency/add')
                    ->with(['success' => 'Валюта добавлена']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка добавления валюты"])
                    ->withInput();
            }

        } else {
            MetaTag::setTags(['title' => 'Добавить валюту']);
            return view('blog.admin.currency.add');
        }
    }

    public function currencyEdit(AdminCurrencyAddRequest $request, $id)
    {
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [{$id}] не найдена!"]);
        }
        if ($request->isMethod('post')){

            $currency = Currency::find($id);
            $currency->title = $request->title;
            $currency->code = $request->code;
            $currency->symbol_left = $request->symbol_left;
            $currency->symbol_right = $request->symbol_right;
            $currency->value = $request->value;
            $currency->base = $request->base ? '1' : '0';
            $currency->save();

            if ($currency) {
                return redirect(url('/admin/currency/edit',$id))
                    ->with(['success' => 'Сохранено']);
            } else {
                return back()
                    ->withErrors(['msg' => "Ошибка"])
                    ->withInput();
            }

        } else if ($request->isMethod('get')){
            $currency = $this->currencyRepository->getInfoProduct($id);
            MetaTag::setTags(['title' => 'Добавление валюты']);
            return view('blog.admin.currency.edit',compact('currency'));
        }
    }

    public function currencyDelete($id){
        if (empty($id)) {
            return back()->withErrors(['msg' => "Запись [{$id}] не найдена!"]);
        }
        $delete = $this->currencyRepository->deleteCurrency($id);

        if ($delete) {
            return back()->with(['success' => "Удалено"]);
        } else {
            return back()->withErrors(['msg' => "Ошибка удаления"]);
        }
    }
}
