<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOnOffRequest;
use App\Http\Requests\UpdateOnOffRequest;
use App\Models\OnOff;
use App\Models\Process;
use App\Services\OnOffService;
use App\Services\Utility;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * ON-OFFメッセージコントローラー
 */
class OnOffController extends AbstractController
{
    /**
     * コンストラクタ
     *
     * @param OnOffService $service ON-OFFサービス
     */
    public function __construct(private readonly OnOffService $service)
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
        return  __('yokakit.notification');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Process $process
     * @return View
     */
    public function index(Process $process): View
    {
        return view('process.on-off.index', ['process' => $process]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Process $process 工程
     * @return View
     */
    public function create(Process $process): View
    {
        $this->authorizeAdmin();
        $this->throwExceptionIfRunning($process);
        $raspberryPiOptions = $this->service->raspberryPiOptions();
        $pinOptions = Utility::pinNumberOptions();
        return view('process.on-off.create', [
            'process' => $process,
            'raspberryPiOptions' => $raspberryPiOptions,
            'pinOptions' => $pinOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOnOffRequest $request リクエスト
     * @param Process $process 工程
     * @return RedirectResponse
     */
    public function store(StoreOnOffRequest $request, Process $process): RedirectResponse
    {
        $this->throwExceptionIfRunning($process);
        $result = $this->service->store($request);
        return $this->redirectWithStore($result, 'process.show', ['process' => $process, 'tab' => 'on-off']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Process $process 工程
     * @param OnOff $onOff
     * @return View
     */
    public function edit(Process $process, OnOff $onOff): View
    {
        $this->authorizeAdmin();
        $this->throwExceptionIfRunning($process);
        $raspberryPiOptions = $this->service->raspberryPiOptions();
        $pinOptions = Utility::pinNumberOptions();
        return view('process.on-off.edit', [
            'process' => $process,
            'onOff' => $onOff,
            'raspberryPiOptions' => $raspberryPiOptions,
            'pinOptions' => $pinOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateOnOffRequest $request リクエスト
     * @param Process $process 工程
     * @param OnOff $onOff
     * @return RedirectResponse
     */
    public function update(UpdateOnOffRequest $request, Process $process, OnOff $onOff): RedirectResponse
    {
        $this->throwExceptionIfRunning($process);
        $result = $this->service->update($request, $onOff);
        return $this->redirectWithUpdate($result, 'process.show', ['process' => $process, 'tab' => 'on-off']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Process $process 工程
     * @param OnOff $onOff
     * @return RedirectResponse
     */
    public function destroy(Process $process, OnOff $onOff): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->throwExceptionIfRunning($process);
        $result = $this->service->destroy($onOff);
        return $this->redirectWithDestroy($result, 'process.show', ['process' => $process, 'tab' => 'on-off']);
    }
}
