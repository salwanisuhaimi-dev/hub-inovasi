<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hab Inovasi') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex">
            @auth
            <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col sticky top-0 h-screen">
                <div class="p-6">
                    <h1 class="text-xl font-bold text-gray-800">Hab Inovasi</h1>
                </div>

                <nav class="flex-1 px-4 space-y-1">
                @php
                    $dashboardRoute = auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin'  ? 'admin.dashboard' : 'user.dashboard';
                @endphp

                    <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)" class="block w-full border-l-4" style="padding-left: 15px">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="block w-full border-l-4" style="padding-left: 15px">
                        {{ __('Laman Utama') }}
                    </x-nav-link>

                    {{-- Menu Admin --}}
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                        <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            Admin Menu
                        </div>
                        <x-nav-link :href="route('admin.archives')" :active="request()->routeIs('admin.archives')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Arkib') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.coffb')" :active="request()->routeIs('admin.coffb')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Coff-B') }}
                        </x-nav-link>

                        <!--<div x-data="{ open: false }" class="w-full">
                            <button @click="open = !open"
                                class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium transition duration-150 ease-in-out border-l-4 border-transparent hover:bg-gray-100 focus:outline-none">
                                <div class="flex items-center" style="color: gray">
                                    <span>{{ __('Arkib') }}</span>
                                </div>
                                <svg :class="{'rotate-180': open, 'rotate-0': !open}" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="pl-4 bg-gray-50">

                            @foreach(\App\Models\Competition::all() as $competition)
                                <x-nav-link
                                    class="block w-full py-2 text-xs">
                                    {{ $competition->name }}
                                </x-nav-link>
                            @endforeach

                            @if(\App\Models\Competition::count() == 0)
                                <span class="block px-4 py-2 text-xs text-gray-400 italic">Tiada pertandingan aktif</span>
                            @endif

                            </div>
                        </div>-->

                        <x-nav-link :href="route('admin.quizzes')" :active="request()->routeIs('admin.quizzes')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Kuiz') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Pengguna') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.publication')" :active="request()->routeIs('admin.publication')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Penerbitan') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.programs')" :active="request()->routeIs('admin.programs')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Program') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.pitch')" :active="request()->routeIs('admin.pitch')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Idea Inovasi') }}
                        </x-nav-link>

                        <div x-data="{ open: false }" class="w-full">
                            <button @click="open = !open"
                                class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium transition duration-150 ease-in-out border-l-4 border-transparent hover:bg-gray-100 focus:outline-none">
                                <div class="flex items-center" style="color: gray">
                                    <span>{{ __('Penyertaan') }}</span>
                                </div>
                                <svg :class="{'rotate-180': open, 'rotate-0': !open}" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                            class="pl-4 bg-gray-50">

                            @foreach(\App\Models\Program::all() as $program)
                                <x-nav-link :href="$program->category_id == 3 ? route('admin.program.quiz-submissions', $program->id) : route('admin.program.submissions', $program->id)"
                                    :active="request()->fullUrlIs(route('admin.program.submissions', $program->id))"
                                    class="block w-full py-2 text-xs">
                                    {{ $program->title }}
                                </x-nav-link>
                            @endforeach

                            @if(\App\Models\Program::count() == 0)
                                <span class="block px-4 py-2 text-xs text-gray-400 italic">Tiada program aktif</span>
                            @endif
                        </div>
                    @else
                        <x-nav-link :href="route('user.submissions')" :active="request()->routeIs('user.submissions')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Penyertaan Saya') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.coffb')" :active="request()->routeIs('user.coffb')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Sesi Coff-B') }}
                        </x-nav-link>

                        <x-nav-link :href="route('user.pitches')" :active="request()->routeIs('user.pitches')" class="block w-full border-l-4" style="padding-left: 15px">
                            {{ __('Idea Inovasi') }}
                        </x-nav-link>

                    @endif
                </nav>

                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' )
                    <div x-data="{ openSettings: false }" class="w-full">
                        <button type="button" @click="openSettings = !openSettings"
                                class="flex items-center justify-between w-full px-4 py-3 text-sm font-bold text-gray-500 hover:text-blue-600 focus:outline-none">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ __('Tetapan') }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200"
                                :class="openSettings ? 'rotate-180' : 'rotate-0'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openSettings" x-cloak class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('admin.settings.manage-departments') }}" class="block text-xs text-gray-500 hover:text-blue-600">
                                {{ __('Urus Bahagian') }}
                            </a>
                        </div>
                        <div x-show="openSettings" x-cloak class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('admin.settings.manage-competitions') }}" class="block text-xs text-gray-500 hover:text-blue-600">
                                {{ __('Urus Pertandingan') }}
                            </a>
                        </div>
                        <div x-show="openSettings" x-cloak class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('admin.settings.manage-programs') }}" class="block text-xs text-gray-500 hover:text-blue-600">
                                {{ __('Urus Kategori Program') }}
                            </a>
                        </div>

                    </div>
                @endif
            </aside>
            @endauth

            <div class="flex-1">
                @auth
                    <livewire:layout.navigation />
                @endauth

                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
