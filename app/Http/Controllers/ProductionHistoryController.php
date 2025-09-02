<?php

namespace App\Http\Controllers;

use App\Exceptions\NoIndicatorException;
use App\Http\Requests\StoreProductionHistoryRequest;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Services\ProductionHistoryService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * 生産履歴コントローラー
 */
class ProductionHistoryController extends AbstractController
{
    /**
     * コンストラクタ
     *
     * @param ProductionHistoryService $service 生産履歴サービス
     */
    public function __construct(private readonly ProductionHistoryService $service)
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
        return __('yokakit.production');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Process $process): View
    {
        $histories = $this->service->histories($process->process_id);
        return view('process.production.index', ['process' => $process, 'histories' => $histories]);
    }

    /**
     * Display the specified resource.
     *
     * @param Process $process 工程
     * @param  \App\Models\ProductionHistory $history
     * @return View
     */
    public function show(Process $process, ProductionHistory $history): View
    {
        $lines = $this->service->productionLines($history->production_history_id);
        return view('process.production.show', ['process' => $process, 'history' => $history, 'lines' => $lines]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Process $process 工程
     * @return View
     */
    public function create(Process $process): View
    {
        $partNumbers = $this->service->partNumberOptions($process);
        return view('process.production.create', ['process' => $process, 'partNumbers' => $partNumbers]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductionHistoryRequest $request リクエスト
     * @param Process $process 工程
     * @return RedirectResponse
     */
    public function store(StoreProductionHistoryRequest $request, Process $process): RedirectResponse
    {
        $route = redirect()->route('process.show', ['process' => $process]);
        try {
            $result = $this->service->switchPartNumberFromForm($request, $process);
            if ($result) {
                $route->with('toast_success', __('yokakit.success_toast2', ['action' => __('yokakit.switch_part_number')]));
            } else {
                $route->with('toast_danger', __('yokakit.failed_toast2', ['action' => __('yokakit.switch_part_number')]));
            }
        } catch (NoIndicatorException $th) {
            Log::error($th->getMessage(), $th->getTrace());
            $route->with('toast_danger', __(
                'yokakit.not_exists_toast',
                [
                    'target' => __('yokakit.indicator'),
                ]
            ));
        }
        return $route;
    }

    /**
     * 生産を停止する
     *
     * @param Process $process
     * @return RedirectResponse
     */
    public function stop(Process $process): RedirectResponse
    {
        $route = redirect()->route('process.show', ['process' => $process]);
        $action = __('yokakit.stop');
        try {
            $this->service->stop($process, true);
            $route->with('toast_success', __('yokakit.success_toast', ['target' => $this->name(), 'action' => $action]));
        } catch (Exception $th) {
            Log::error($th->getMessage(), $th->getTrace());
            $route->with('toast_danger', __('yokakit.failed_toast', ['target' => $this->name(), 'action' => $action]));
        }
        return $route;
    }

    /**
     * 段取り替えを開始する
     *
     * @param Process $process
     * @return RedirectResponse
     */
    public function startChangeover(Process $process): RedirectResponse
    {
        $route = redirect()->route('process.show', ['process' => $process]);
        try {
            $result = $this->service->changeover($process);
            if ($result) {
                $route->with('toast_success', __('yokakit.success_toast2', ['action' => __('yokakit.changeover')]));
            } else {
                $route->with('toast_danger', __('yokakit.failed_toast2', ['action' => __('yokakit.changeover')]));
            }
        } catch (Exception $th) {
            Log::error($th->getMessage(), $th->getTrace());
            $route->with('toast_danger', __('yokakit.failed_toast2', ['action' => __('yokakit.changeover')]));
        }
        return $route;
    }

    /**
     * 段取り替えを終了して生産を開始する
     *
     * @param Process $process
     * @return RedirectResponse
     */
    public function stopChangeover(Process $process): RedirectResponse
    {
        $route = redirect()->route('process.show', ['process' => $process]);
        try {
            $result = $this->service->changeover($process);
            if ($result) {
                $route->with('toast_success', __('yokakit.success_toast2', ['action' => __('yokakit.start_production')]));
            } else {
                $route->with('toast_danger', __('yokakit.failed_toast2', ['action' => __('yokakit.start_production')]));
            }
        } catch (Exception $th) {
            Log::error($th->getMessage(), $th->getTrace());
            $route->with('toast_danger', __('yokakit.failed_toast2', ['action' => __('yokakit.start_production')]));
        }
        return $route;
    }
}
