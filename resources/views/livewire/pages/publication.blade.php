<?php

use App\Models\Publication;
use function Livewire\Volt\{layout, state, computed};

layout('layouts.landing');

state([
    'search' => '',
    'selectedType' => '',
    'selectedYear' => '',
]);

$publications = computed(function () {
    return Publication::query()
        ->where('is_active', true)
        ->when($this->search, function ($query) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->when($this->selectedType, function ($query) {
            $query->where('type', $this->selectedType);
        })
        ->when($this->selectedYear, function ($query) {
            $query->where('year', $this->selectedYear);
        })
        ->latest()
        ->get();
});

$availableYears = computed(function () {
    return Publication::where('is_active', true)->pluck('year')->unique()->sortDesc();
});

?>

<div "min-h-screen bg-[#faf7f2] text-[#4a3728] font-sans pb-20 overflow-x-hidden">
    <x-top-nav />
    <header class="relative overflow-hidden py-24 bg-slate-900 text-center">

        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/Corporate-Life.jpg') }}"
                 alt=""
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/40 via-slate-900 to-slate-900"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-blue-500/10 via-transparent to-transparent"></div>
        </div>

        <div class="relative max-w-3xl mx-auto px-6 z-10 flex flex-col items-center">

            <div class="relative mb-6 animate-[bounce_3s_infinite] ease-in-out">
                <div class="absolute inset-0 bg-blue-500/30 rounded-full blur-xl scale-75 animate-pulse"></div>

                <div class="relative bg-slate-800/80 p-4 rounded-3xl border border-slate-700 shadow-xl backdrop-blur-sm">
                    <img src="{{ asset('images/book-logo.png') }}"
                         alt="FAQ Logo"
                         class="w-14 h-14 object-contain filter drop-shadow-[0_4px_12px_rgba(59,130,246,0.5)]">
                </div>
            </div>

            <!--<span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-[0.25em] bg-blue-500/10 text-blue-400 border border-blue-500/20">
                Dokumentasi
            </span>-->

            <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white mt-4 leading-tight">
                Penerbitan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 font-serif italic"></span>
            </h2>

            <p class="text-base sm:text-lg text-slate-400 mt-4 max-w-xl mx-auto leading-relaxed font-medium">
                Cari dan muat turun dokumen rasmi, TOR Coff-B, atau garis panduan pertandingan terkini. <span class="text-slate-200 font-semibold"></span>
            </p>
        </div>
    </header>


    <div class="max-w-7xl mx-auto px-6 py-10">

        {{-- Search & Filter Bar (Gaya Joomag) --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row gap-3 mb-8">
            {{-- Input Carian --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                      🔍
                </span>
                <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama dokumen atau program..."
                   class="w-full bg-slate-50 border-none rounded-xl pl-11 pr-4 py-3 text-sm font-bold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 transition-all">
            </div>

            {{-- Penapis Kategori --}}
            <div class="w-full sm:w-48">
                <select wire:model.live="selectedType" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Semua Kategori</option>
                    <option value="TOR">Terms of Reference (TOR)</option>
                    <option value="Garis Panduan">Garis Panduan</option>
                    <option value="Lain-lain">Lain-lain</option>
                </select>
            </div>

            <div class="w-full sm:w-36">
                <select wire:model.live="selectedYear" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Semua Tahun</option>
                        @foreach($this->availableYears as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                </select>
            </div>
        </div>

        {{-- Publications List Container --}}
        <div class="space-y-4">
        @forelse($this->publications as $pub)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 p-5 flex flex-col sm:flex-row items-start sm:items-center gap-6 group relative">
                @php
                    $coverGradient = match($pub->type) {
                        'TOR' => 'from-blue-600 to-indigo-700 shadow-blue-100',
                        'Garis Panduan' => 'from-emerald-500 to-teal-700 shadow-emerald-100',
                        'Template' => 'from-purple-500 to-indigo-600 shadow-purple-100',
                        default => 'from-slate-600 to-slate-800 shadow-slate-100'
                    };
                @endphp
                <div class="w-24 h-32 flex-shrink-0 bg-gradient-to-br {{ $coverGradient }} rounded-xl shadow-lg p-3 flex flex-col justify-between text-white relative overflow-hidden group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-y-0 left-0 w-1 bg-black/10"></div>
                    <div class="text-[10px] font-black tracking-widest opacity-80 uppercase">{{ $pub->type }}</div>
                    <div class="text-2xl font-black opacity-40 select-none tracking-tighter">DOC</div>
                    <div class="text-xs font-black tracking-tight leading-none bg-black/20 p-1.5 rounded text-center">
                        {{ $pub->year }}
                    </div>
                </div>

                {{-- Center: Metadata & Description --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-[9px] font-black tracking-wider uppercase px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                            {{ $pub->year }}
                        </span>
                        <span class="text-[9px] font-black tracking-wider uppercase px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $pub->type }}
                        </span>
                    </div>

                    <h3 class="text-lg font-black text-slate-900 tracking-tight leading-snug group-hover:text-blue-600 transition-colors">
                        {{ $pub->title }}
                    </h3>

                    <p class="text-xs text-slate-400 font-medium mt-1.5 leading-relaxed line-clamp-2" title="{{ $pub->description }}">
                        {{ $pub->description }}
                    </p>
                </div>

                {{-- Right: Download/View --}}
                <div class="w-full sm:w-auto flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 border-t sm:border-t-0 pt-4 sm:pt-0 border-slate-100">
                    @php $files = $pub->pdf_paths ?? []; @endphp

                    @if(count($files) > 0)
                        <div class="flex flex-wrap sm:flex-col gap-2 w-full">
                            @foreach($files as $index => $path)
                                <a href="{{ asset('storage/' . $path) }}"
                                   target="_blank"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-blue-600 text-white rounded-xl text-xs font-black tracking-wider uppercase transition-all shadow-sm active:scale-95 group/btn">

                                    <svg class="w-3.5 h-3.5 text-red-400 group-hover/btn:text-white transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>

                                    <span>Buka Dokumen {{ count($files) > 1 ? ($index + 1) : '' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($pub->url))
                    <a href="{{ Str::startsWith($pub->url, ['http://', 'https://']) ? $pub->url : 'https://' . $pub->url }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100/70 px-2.5 py-1.5 rounded-lg border border-red-100 transition-all max-w-[180px] truncate">

                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>

                        <span class="inline-block max-w-[200px] truncate" title="{{ $pub->url }}">
                            {{ $pub->url }}
                        </span>
                    </a>
                @endif


                <!--<div class="absolute top-4 right-4 opacity-10 group-hover:opacity-30 transition-opacity hidden sm:block">
                    <svg class="w-8 h-8 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h3a2 2 0 002-2V6l-4-4H9z"></path>
                        <path d="M12 2.5V5a1 1 0 001 1h2.5L12 2.5z"></path>
                    </svg>
                </div>-->

            </div>
        @empty
            <div class="bg-white rounded-3xl border border-slate-100 p-20 text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    📂
                </div>
                <h4 class="font-black text-slate-800 text-sm uppercase tracking-wider">Tiada Dokumen Ditemui</h4>
                <p class="text-xs text-slate-400 font-medium mt-1">Sila tukar kata kunci carian atau tetapan penapis anda.</p>
            </div>
        @endforelse
    </div>

  </div>

</div>

<x-footer />
