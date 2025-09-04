<div class="col-md-12">
    <x-adminlte-card>
        <x-adminlte-datatable id="{{ Str::random(16) }}">
            {{ $slot }}
        </x-adminlte-datatable>
        @can('admin')
            <x-slot name="footerSlot">
                <a href="{{ $href }}" role="button" {{ $attributes->merge(['class' => 'btn btn-primary']) }}>
                    <i class="fa-solid fa-lg fa-add"></i>
                    {{ $add }}
                </a>
            </x-slot>
        @endcan
    </x-adminlte-card>
</div>
