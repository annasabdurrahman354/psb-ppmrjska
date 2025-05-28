<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{-- Ini akan merender Grid yang didefinisikan di setUp() ->schema() --}}
    {{ $getChildComponentContainer() }}
</x-dynamic-component>
