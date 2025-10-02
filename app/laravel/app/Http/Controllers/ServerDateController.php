<?php

namespace App\Http\Controllers;

use App\Services\Utility;
use Illuminate\Http\Request;

/**
 * サーバー時刻コントローラー
 */
class ServerDateController extends BaseController
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return string|null
     */
    public function __invoke(Request $request): ?string
    {
        return Utility::format(Utility::now());
    }
}
