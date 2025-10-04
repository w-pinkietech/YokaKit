<div class="col-md-12">
    <x-adminlte-card>
        <x-adminlte-datatable id="{{ Str::random(16) }}" :heads="$heads" :config="$config">
            {{ $slot }}
        </x-adminlte-datatable>
        @can('admin')
            @if($attributes->has('href'))
                <x-slot name="footerSlot">
                    <a role="button" href="{{ $attributes->get('href') }}"
                        class="{{ $attributes->merge(['class' => 'btn btn-primary'])->get('class') }}">
                        <i class="fa-solid fa-lg fa-add"></i>
                        {{ $add }}
                    </a>
                </x-slot>
            @endif
        @endcan
    </x-adminlte-card>
</div>
