<?php

namespace App\Http\Requests;

use App\Enums\SensorType;
use App\Models\Process;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * センサー追加リクエスト
 *
 * @property integer $raspberry_pi_id ラズパイID
 */
class StoreSensorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::check('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'raspberry_pi_id' => 'required|integer|exists:raspberry_pis,raspberry_pi_id',
            'sensor_type' => ['required', 'integer', Rule::in(SensorType::getValues())],
            'identification_number' => "required|integer|between:1,65535|unique:sensors,identification_number,NULL,sensor_id,raspberry_pi_id,{$this->raspberry_pi_id}",
            'alarm_text' => 'required|max:128|string',
            'trigger' => 'required|boolean',
        ];
    }

    /**
     * バリデーションのためのデータの準備
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        /** @var Process */
        $process = $this->route('process');
        // パラメータをマージ
        $this->merge([
            'process_id' => $process->process_id,
            'trigger' => !is_null($this->trigger)
        ]);
    }
}
