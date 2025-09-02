<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * データテーブル用言語取得コントローラー
 */
class DataTablesLocaleController extends BaseController
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
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $config = __('datatables');
        $locale = config('app.locale');
        if (array_key_exists($locale, $config)) {
            return $config[$locale];
        } else {
            throw new NotFoundHttpException();
        }
    }
}
