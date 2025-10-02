<x-adminlte-modal id="{{ $id }}" title="{{ __('yokakit.confirm') }}" theme="danger" icon="fa-solid fa-fw fa-triangle-exclamation"
    v-centered>
    {{ $slot }}
    <x-slot name="footerSlot">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method('DELETE')
            <x-adminlte-button type="submit" theme="danger" label="{{ __('yokakit.delete') }}" icon="fa-solid fa-fw fa-trash" />
        </form>
    </x-slot>
</x-adminlte-modal>
