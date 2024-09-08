<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAllUsers(Request $request)
    {
        return UserService::getAllUsers($request);
    }

    public function getUserById(int $id)
    {
        return UserService::getUserById($id);
    }

    public function updateAuthInformation(int $id, Request $request)
    {
        return UserService::updateAuthInformation($id, $request);
    }

    public function deleteAccount(int $id, Request $request)
    {
        return UserService::deleteAccount($id, $request);
    }

    public function getUsersStats(Request $request)
    {
        return UserService::getUsersStats($request);
    }

    public function suspendUser(int $id, Request $request)
    {
        return UserService::suspendUser($id, $request);
    }

    public function getUserByIds(Request $request)
    {
        return UserService::getUserByIds($request);
    }
}
