<div class="position-relative float-right pr-2" style="top:-2.75rem; height: 0;">
    @can('admin')
        @if ($process->isStopped())
            <x-button-add class="pt-0 pb-0" href="{{ route('alarm.create', ['process' => $process]) }}" />
        @endif
    @endcan
</div>
<table class="mt-2 table">
    <thead>
        <tr>
            <th class="border-top-0 border-bottom-0">{{ __('yokakit.alarm_text') }}</th>
            <th class="border-top-0 border-bottom-0">{{ __('yokakit.identification_number') }}</th>
            <th class="border-top-0 border-bottom-0">{{ __('yokakit.sensor_type') }}</th>
            <th class="border-top-0 border-bottom-0">{{ __('yokakit.raspberry_pi') }}</th>
            <th class="border-top-0 border-bottom-0">{{ __('yokakit.trigger') }}</th>
            @can('admin')
                @if ($process->isStopped())
                    <th class="border-top-0 border-bottom-0 w-1"></th>
                @endif
            @endcan
        </tr>
    </thead>
    <tbody>
        @foreach ($process->sensors as $sensor)
            <tr class="text-muted">
                <td class="align-middle">{{ $sensor->alarm_text }}</td>
                <td class="align-middle">{{ $sensor->identification_number }}</td>
                <td class="align-middle">{{ $sensor->sensor_type->description }}</td>
                <td class="align-middle">{{ $sensor->raspberryPi->raspberry_pi_name }}</td>
                <td class="align-middle">{{ $sensor->trigger ? 'HIGH' : 'LOW' }}</td>
                @can('admin')
                    @if ($process->isStopped())
                        <td class="text-nowrap text-right align-middle">
                            {{-- アラーム編集ボタン --}}
                            <x-button-edit href="{{ route('alarm.edit', ['process' => $process, 'sensor' => $sensor]) }}" />
                            {{-- アラーム削除ボタン --}}
                            <x-button-delete target="alarm_{{ $sensor->sensor_id }}" />
                        </td>
                        {{-- アラーム削除ダイアログ --}}
                        <x-modal-delete id="alarm_{{ $sensor->sensor_id }}"
                            action="{{ route('alarm.destroy', ['process' => $process, 'sensor' => $sensor]) }}">
                            <strong>{{ __('yokakit.confirm_delete', ['target' => __('yokakit.alarm')]) }}</strong>
                            <x-adminlte-card class="mt-4">
                                <strong>{{ __('yokakit.target_name', ['target' => __('yokakit.alarm')]) }}</strong>
                                <p class="mt-1 ml-2">{{ $sensor->device_name }}</p>
                                <hr>
                                <strong>{{ __('yokakit.target_name', ['target' => __('yokakit.device')]) }} -
                                    {{ __('yokakit.alias') }}</strong>
                                <p class="mt-1 ml-2">{{ $sensor->alias }}</p>
                                <hr>
                                <strong>{{ __('yokakit.sensor_type') }}</strong>
                                <p class="mt-1 ml-2">{{ $sensor->sensor_type->description }}</p>
                                <hr>
                                <strong>{{ __('yokakit.raspberry_pi') }}</strong>
                                <p class="mt-1 ml-2">{{ $sensor->raspberryPi->raspberry_pi_name }}</p>
                                <hr>
                                <strong>{{ __('yokakit.trigger') }}</strong>
                                <p class="mt-1 ml-2 mb-0">{{ $sensor->trigger ? 'HIGH' : 'LOW' }}</p>
                            </x-adminlte-card>
                        </x-modal-delete>
                    @endif
                @endcan
            </tr>
        @endforeach
    </tbody>
</table>
