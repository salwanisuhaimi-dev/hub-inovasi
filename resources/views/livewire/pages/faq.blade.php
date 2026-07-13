<?php

use function Livewire\Volt\{layout, state};

layout('layouts.landing');

state(['openIndex' => null]);

$toggle = function ($index) {
    $this->openIndex = $this->openIndex === $index ? null : $index;
};

?>

<div class="min-h-screen bg-gray-50">
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
                    <img src="{{ asset('images/questions-logo.png') }}"
                         alt="FAQ Logo"
                         class="w-14 h-14 object-contain filter drop-shadow-[0_4px_12px_rgba(59,130,246,0.5)]">
                </div>
            </div>

            <!--<span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-[0.25em] bg-blue-500/10 text-blue-400 border border-blue-500/20">
                Bantuan
            </span>-->

            <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white mt-4 leading-tight">
                Soalan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 font-serif italic">Lazim</span>
            </h2>

            <p class="text-base sm:text-lg text-slate-400 mt-4 max-w-xl mx-auto leading-relaxed font-medium">
                Segala jawapan kepada persoalan anda mengenai platform <span class="text-slate-200 font-semibold">Hab Inovasi Jabatan</span>.
            </p>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-6 py-20">
        <div class="space-y-4">
            @php
                $faqs = [
                    ['q' => 'Apa itu Hab Inovasi?', 'a' => 'Hab Inovasi adalah platform pusat untuk warga jabatan berkongsi, mendokumentasikan, dan meneroka projek-projek inovasi digital.'],
                    ['q' => 'Siapa yang boleh menghantar idea?', 'a' => 'Semua warga jabatan yang berdaftar boleh menghantar idea atau projek inovasi mereka melalui modul Hantar Idea.'],
                    ['q' => 'Adakah projek saya akan disemak?', 'a' => 'Ya, setiap projek yang dihantar akan melalui proses semakan oleh Admin sebelum dipaparkan di Arkib Inovasi.'],
                    ['q' => 'Bagaimana cara untuk menyertai Kuiz?', 'a' => 'Anda boleh terus ke modul Kuiz di Homepage dan pilih kuiz yang sedang aktif untuk menguji pengetahuan anda.'],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <button
                        wire:click="toggle({{ $index }})"
                        class="w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition"
                    >
                        <span class="font-bold text-gray-900">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-blue-600 transform {{ $openIndex === $index ? 'rotate-180' : '' }} transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div class="{{ $openIndex === $index ? 'block' : 'hidden' }} px-6 pb-6 text-gray-600 leading-relaxed border-t border-gray-50 pt-4">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <x-footer />
</div>
