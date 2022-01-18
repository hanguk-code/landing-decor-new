<?php

namespace App\Repositories;

use App\Helpers\HashGen;
use App\Mail\RegisterEmail;
use App\Models\Order\Order;
use App\Models\Product\OcProductDescription;
use App\Models\Product\OcProduct;
use App\Models\User\User;
use App\Models\User\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    protected $user;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(
        User $user,
        Users $users
    ) {
        $this->user = $user;
    }

    /**
     * {@inheritDoc}
     */
    public function all($request)
    {
        if ($request->input('client')) {
            return $this->user->select('id', 'name', 'email', 'status', 'created_at', 'updated_at',
                'deleted_at')->get();
        }

        $columns = ['id', 'name', 'email', 'status', 'updated_at'];

        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');

        $query = $this->user->select('id', 'name', 'email', 'status', 'created_at', 'updated_at', 'deleted_at')
            ->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('status', 'like', '%' . $searchValue . '%')
                    ->orWhere('created_at', 'like', '%' . $searchValue . '%')
                    ->orWhere('updated_at', 'like', '%' . $searchValue . '%');
            });
        }

        $users = $query->paginate($length);

        $columns = array(
            array('width' => '33%', 'label' => 'Id', 'name' => 'id'),
            array('width' => '33%', 'label' => 'Имя', 'name' => 'name'),
            array('width' => '33%', 'label' => 'Имейл', 'name' => 'email'),
            array('width' => '33%', 'label' => 'Статус', 'name' => 'status'),
            array('width' => '33%', 'label' => 'Даты', 'name' => 'dates')
        );

        $statusClass = array(
            array('status' => 'active', 'badge' => 'kt-badge--success'),
            array('status' => 'blocked', 'badge' => 'kt-badge--danger')
        );


        $sortKey = 'dates';

        return [
            'data' => $users,
            'columns' => $columns,
            'statusClass' => $statusClass,
            'sortKey' => $sortKey,
            'draw' => $request->input('draw')
        ];
    }

    public function find(int $userId)
    {
        $user = $this->user->findOrFail($userId);

        return $user;
    }


    public function update(array $request, int $userId)
    {

        DB::transaction(function () use ($request, $userId) {
            $this->user->find($userId)->update($request);

            // if($request['photo']) {
            //     if(!isset($request['photo_url'])) {
            //        $this->savePhoto($request['photo'], $userId, $userProfile['id']);
            //    } else {
            //     if($request['photo'] !== $request['photo_url']) {
            //         $this->savePhoto($request['photo'], $userId, $userProfile['id']);
            //     }
            //    }

            // }
        });

    }


    public function destroy(int $userId)
    {
        $user = $this->user->find($userId);
        $user->delete();
    }

    public function savePhoto($logoDataImage, $userId, $userProfileId)
    {
        $filename = time() . '.' . explode('/',
                explode(':', substr($logoDataImage, 0, strpos($logoDataImage, ';')))[1])[1];
        $path = 'img/user/' . $userId . '/photo/';

        if (isset($userProfile['photo_url'])) {
            $explodedLogo = explode('/', $userProfile['photo_url']);
            $logoName = end($explodedLogo);
            $imageToRemove = public_path($path . $logoName); // get previous image from folder
            if (\File::exists($imageToRemove)) { // unlink or remove previous image from folder
                unlink($imageToRemove);
            }
        }

        \File::makeDirectory(public_path('img/user/' . $userId . '/photo/'), 0755, true, true);
        \Image::make($logoDataImage)->save(public_path('img/user/' . $userId . '/photo/') . $filename);

        $logo = config('app.url') . '/' . $path . $filename;
        $this->user::find($userId)->profile()->update(['photo_url' => $logo]);
    }

    public function changePassword($request)
    {
        $userId = $request->get('user_id');
        $password = $request->get('password');
        $newPassword = $request->get('new_password');

        $user = $this->user->find($userId);

        if (Hash::check($password, $user->password)) {
            $user->password = Hash::make($newPassword);
            $user->save();
            $status = ['status' => 'success'];
        } else {
            $status = ['status' => 'error'];
        }

        return $status;
    }


    /**
     * @param $request
     * @return array
     */
    public function users($request)
    {
        if ($request->input('client')) {
            return Users::get();
        }

        $columns = ['name', 'phone', 'number_of_purchases', 'total_sum'];

        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $searchByStatus = $request->input('search_by_status');

        $query = Users::orderBy('created_at', 'DESC')->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('created_at', 'like', '%' . $searchValue . '%')
                    ->orWhere('updated_at', 'like', '%' . $searchValue . '%');
            });
        }


        if(isset($searchByStatus) || !empty($searchByStatus)) {
            $query = $query->where('status', $searchByStatus);
        }

        $users = $query->paginate($length);

        $columns = array(
            array('width' => '33%', 'label' => 'Имя', 'name' => 'name'),
            array('width' => '33%', 'label' => 'Номер телефона', 'name' => 'phone'),
            array('width' => '33%', 'label' => 'Количество покупок', 'name' => 'number_of_purchases'),
            array('width' => '33%', 'label' => 'Сумма', 'name' => 'total_sum'),
        );


        /*$statusClass = array (
            array('status' => 'active','badge' => 'kt-badge--success'),
            array('status' => 'blocked', 'badge' => 'kt-badge--danger')
        );*/


        $sortKey = 'id';

        return [
            'data' => $users,
            'columns' => $columns,
            /*'statusClass' => $statusClass,*/
            'sortKey' => $sortKey,
            'draw' => $request->input('draw'),
            'stats' => [
                'total' => Users::count(),
                'permanent' => Users::where('status', 'permanent')->count(),
                'blacklist' => Users::where('status', 'blacklist')->count()
            ]
        ];
    }


    public function deleteChecked($request)
    {
        $checkedItems = $request->get('checkedItems');

        foreach ($checkedItems as $item) {
            Users::where('id', $item)->delete();
        }
    }


    public function getUserData($id)
    {
        $user = Users::where('id', $id)->first();

        return $user;
    }


    public function getUserOrders($request, $user_id)
    {
        $user = Users::where('id', $user_id)->first();
        $data = Order::where('phone', $user->phone)->get();
        foreach ($data as $key => $value) {
            $pids = unserialize($value['product_id']);
            $data[$key]['product_id'] = OcProduct::whereIn('product_id', $pids)->get();
        }
        return $data;
    }


    public function UserDataUpdate($request, $user_id)
    {
        $request = (object) $request;
        Users::where('id', $user_id)->update([
            'phone' => $request->user['phone'],
            'email' => $request->user['email'],
            'address' => $request->user['address'],
            'tags' => $request->user['tags'],
            'status' => $request->user['status'],
            'comments' => $request->user['comments']
        ]);
    }

}
