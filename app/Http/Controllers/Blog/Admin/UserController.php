<?php

namespace App\Http\Controllers\Blog\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserEditRequest;
use App\Models\Admin\User;
use App\Models\UserRole;
use App\Repositories\Admin\MainRepository;
use App\Repositories\Admin\UserRepository;
use Fomvasss\LaravelMetaTags\Facade as MetaTag;
use Illuminate\Http\Request;

class UserController extends AdminBaseController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = app(UserRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $countUsers = MainRepository::getCountUsers();
        $paginator   = $this->userRepository->getAllUsers(10);
        MetaTag::setTags([
            'title' => 'List of orders'
        ]);

        return view('blog.admin.user.index', ['paginator' => $paginator, 'countUsers' => $countUsers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        MetaTag::setTags([
            'title' => 'Create user'
        ]);

        return view('blog.admin.user.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminUserEditRequest $request)
    {
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);

        if(!$user){
            return back()
                ->withErrors(['msg' => 'Creation error'])
                ->withInput();
        } else {
            $role = UserRole::create([
                'user_id' => $user->id,
                'role_id' => (int)$request['role']
            ]);

            if(!$role){
                return back()
                    ->withErrors(['msg' => 'Creation error'])
                    ->withInput();
            } else {
                return redirect()
                    ->route('blog.admin.users.edit', $user->id)
                    ->with(['success' => 'Success']);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $perpage = 10;
        $item = $this->userRepository->getEditId($id);
        if (empty($item)) {
            abort(404);
        }
        $orders = $this->userRepository->getUserOrders($id, $perpage);
        $role = $this->userRepository->getUserRole($id);
        $count = $this->userRepository->getCountOrdersPag($id);
        $countOrders = $this->userRepository->getCountOrders($id, $perpage);

        MetaTag::setTags(['title' => "Edit user № {$item->id}"]);

        return view('blog.admin.user.edit',
            compact('item', 'orders', 'role', 'countOrders', 'count'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AdminUserEditRequest $request
     * @param User $user
     * @param UserRole $role
     * @return \Illuminate\Http\Response
     */
    public function update(AdminUserEditRequest $request, User $user, UserRole $role)
    {
        $user->name = $request['name'];
        $user->email = $request['email'];
        $request['password'] == null ?: $user->password = bcrypt($request['password']);
        $save = $user->save();
        if (!$save) {
            return back()
                ->withErrors(['msg' => "Save error"])
                ->withInput();
        } else {
            $role->where('user_id', $user->id)->update(['role_id' => (int)$request['role']]);
            return redirect()
                ->route('blog.admin.users.edit', $user->id)
                ->with(['success' => 'Success']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $result = $user->forceDelete();
        if($result){
            return redirect()
                ->route('blog.admin.users.index')
                ->with(['success' => "Пользователь " . ucfirst($user->name) . " удален"]);
        } else {
            return back()->withErrors(['msg' => 'Ошибка удаления']);
        }
    }
}
