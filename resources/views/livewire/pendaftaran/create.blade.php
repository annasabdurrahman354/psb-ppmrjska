<div class="bg-gradient-to-br from-lime-50 to-green-100 p-16 bg-zinc-100 font-inter w-full">
    <div class="min-h-screen flex flex-col items-center justify-center">

        <div class="relative mb-6 w-24 h-24 text-center">
            <span class="absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-10 animate-ping"></span>

            <img
                src="{{asset("logo.png")}}"
                alt="PPM Roudlotul Jannah Logo Placeholder"
                class="relative inline-flex h-24 w-24 z-10 object-cover drop-shadow"
            >
        </div>

        <h1 class="text-3xl md:text-4xl font-bold text-green-600 mb-2  text-center">
            PPM Roudlotul Jannah Surakarta
        </h1>

        <p class="mb-16 text-lg md:text-xl italic text-gray-700 text-center">
            Sarjana yang Mubaligh,
            <span class="text-green-400 mx-1 relative inline-block stroke-current">
                Mubaligh yang Sarjana
                <svg class="absolute -bottom-0.5 w-full max-h-1.5" viewBox="0 0 55 5" xmlns="http://www.w3.org/2000/svg"
                     preserveAspectRatio="none">
                    <path d="M0.652466 4.00002C15.8925 2.66668 48.0351 0.400018 54.6853 2.00002" stroke-width="2"></path>
                </svg>
            </span>
        </p>

        @if ($pendaftaranDibuka)
            <form wire:submit.prevent="create">
                {{ $this->form }}
            </form>

            {{-- Required for form interactions --}}
            <x-filament-actions::modals />

        @else
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Informasi Pendaftaran</p>
                <p>Mohon maaf, saat ini tidak ada gelombang pendaftaran calon santri baru yang sedang dibuka untuk tahun {{ \Carbon\Carbon::now()->year }}. Silakan periksa kembali nanti.</p>
            </div>
        @endif

    </div>
</div>
