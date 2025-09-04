<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartNumberRequest;
use App\Http\Requests\UpdatePartNumberRequest;
use App\Models\PartNumber;
use App\Services\PartNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 品番コントローラー
 */
class PartNumberController extends AbstractController
{
    /**
     * コンストラクタ
     *
     * @param PartNumberService $service 品番サービス
     */
    public function __construct(private readonly PartNumberService $service)
    {
        $this->middleware('auth');
    }

    /**
     * UI表示用名称を取得する
     *
     * @return string 名称
     */
    public function name(): string
    {
        return __('yokakit.part_number');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $partNumbers = $this->service->all();
        return view('part-number.index', ['partNumbers' => $partNumbers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $this->authorizeAdmin();
        return view('part-number.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePartNumberRequest $request リクエスト
     * @return RedirectResponse
     */
    public function store(StorePartNumberRequest $request): RedirectResponse
    {
        $result = $this->service->store($request);
        return $this->redirectWithStore($result, 'part-number.index');
    }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param PartNumber $partNumber
    //  * @return Response
    //  */
    // public function show(PartNumber $partNumber)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param PartNumber $partNumber
     * @return View
     */
    public function edit(PartNumber $partNumber): View
    {
        $this->authorizeAdmin();
        return view('part-number.edit', ['partNumber' => $partNumber]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePartNumberRequest $request リクエスト
     * @param PartNumber $partNumber
     * @return RedirectResponse
     */
    public function update(UpdatePartNumberRequest $request, PartNumber $partNumber): RedirectResponse
    {
        $result = $this->service->update($request, $partNumber);
        return $this->redirectWithUpdate($result, 'part-number.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PartNumber $partNumber
     * @return RedirectResponse
     */
    public function destroy(PartNumber $partNumber): RedirectResponse
    {
        $this->authorizeAdmin();
        $result = $this->service->destroy($partNumber);
        return $this->redirectWithDestroy($result, 'part-number.index');
    }
}
