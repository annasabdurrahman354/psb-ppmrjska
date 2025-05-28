<x-filament-panels::page.simple>
    @if (filament()->hasLogin())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/register.actions.login.before') }}

            {{ $this->loginAction }}
        </x-slot>
    @endif

    <x-filament-panels::form id="form" wire:submit="register">
        {{ $this->form }}
    </x-filament-panels::form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Find the main element and change max-w-lg to max-w-6xl
                const mainElement = document.querySelector('main');
                if (mainElement) {
                    mainElement.classList.remove('max-w-lg');
                    mainElement.classList.add('max-w-6xl');
                }
            });
        </script>
    @endpush
</x-filament-panels::page.simple>
