<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

use App\Http\Requests\UserUpdateRequest;
use App\Repositories\UserRepository;
use App\Http\Resources\UserResource;

use App\Http\Resources\JResource;

class UserController extends Controller
{
    
    protected $userRepository;

    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $this->userRepository->all($request);

        return (new UserResource($user));
    }

    public function me()
    {
        $user = $this->userRepository->find(\Auth::id());
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId)
    {
        $user = $this->userRepository->find($userId);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, $userId)
    {
        $this->userRepository->update($request->all(), $userId);

        return (new UserResource(['status' => 'success']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId)
    {
        $this->userRepository->delete($userId);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function changePassword(Request $request)
    {
       $user = $this->userRepository->changePassword($request);

        return new UserResource($user); 
    }


    public function deleteChecked(Request $request)
    {
        $this->userRepository->deleteChecked($request);

        return (new JResource(['status' => 'success']));
    }

    public function users(Request $request)
    {
        $users = $this->userRepository->users($request);

        return new JResource($users);
    }

    public function UserDataUpdate(Request $request, $id)
    {
        $params = $request->all();
        $phone = str_replace("+", "", $request->user['phone']);
        if(iconv_strlen($phone) === 11) {
            $phone = substr_replace($phone, 7, 0, 1);
        }

        $params['user']['phone'] = "+".$phone;

        $this->userRepository->UserDataUpdate($params, $id);
        return response()->json(['status' => 'success']);
    }

    public function getUserOrders(Request $request, $id)
    {
        $orders = $this->userRepository->getUserOrders($request, $id);

        return response()->json($orders);
    }

    public function getUserData($id)
    {
        $user = $this->userRepository->getUserData($id);

        return response()->json($user);
    }

}
