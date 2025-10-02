<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ユーザー情報を取得するAPIコントローラークラス
 */
class UserInfoController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return User|null
     */
    public function __invoke(Request $request): ?User
    {
        $this->authorizeAdmin();
        return Auth::user();
    }
}
