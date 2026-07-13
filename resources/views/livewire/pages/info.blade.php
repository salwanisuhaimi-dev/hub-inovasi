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
                    <img src="{{ asset('images/info-logo.png') }}"
                         alt="FAQ Logo"
                         class="w-14 h-14 object-contain filter drop-shadow-[0_4px_12px_rgba(59,130,246,0.5)]">
                </div>
            </div>

            <!--<span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-[0.25em] bg-blue-500/10 text-blue-400 border border-blue-500/20">
                Dokumentasi
            </span>-->

            <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white mt-4 leading-tight">
                Tentang <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 font-serif italic">Kami</span>
            </h2>

            <p class="text-base sm:text-lg text-slate-400 mt-4 max-w-xl mx-auto leading-relaxed font-medium">
                Segala informasi mengenai kami dan perkhidmatan yang disediakan. <span class="text-slate-200 font-semibold"></span>
            </p>
        </div>
    </header>


    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-24">

            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest">
                    Profil Korporat
                </div>

                <h1 class="text-4xl md:text-5xl font-black text-stone-900 tracking-tight leading-tight">
                    Memacu Kecemerlangan, <br>
                    <span class="text-blue-600 italic font-serif">Membina Masa Depan</span>
                </h1>

                <p class="text-stone-600 text-base leading-relaxed">
                    Kami komited dalam menyampaikan perkhidmatan yang berintegriti tinggi serta memupuk inovasi yang mampan demi kesejahteraan organisasi dan masyarakat secara menyeluruh. Dengan tadbir urus yang kukuh, setiap langkah kami berlandaskan matlamat yang jelas.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                    <div class="bg-stone-50 border-l-4 border-blue-600 p-6 rounded-r-2xl shadow-sm">
                        <span class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Visi Kami</span>
                        <p class="text-stone-900 font-extrabold text-lg leading-snug">
                            Menjadi peneraju organisasi global yang inklusif, berintegriti, dan berteknologi tinggi menjelang 2030.
                        </p>
                    </div>

                    <div class="bg-stone-50 border-l-4 border-stone-800 p-6 rounded-r-2xl shadow-sm">
                        <span class="block text-xs font-bold text-stone-600 uppercase tracking-wider mb-1">Misi Kami</span>
                        <p class="text-stone-700 text-sm leading-relaxed">
                            Melaksanakan tadbir urus terbaik melalui solusi inovatif, memperkasakan modal insan, dan menyampaikan impak positif yang berterusan.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-visible p-6 justify-self-center lg:justify-self-end w-full max-w-lg">

                <div class="relative z-10 rounded-[2.5rem] overflow-hidden shadow-2xl border-8 border-white">
                    <img src="{{ asset('images/jpa.jpg') }}"
                         class="w-full h-[450px] object-cover object-top transition duration-700 hover:scale-105"
                         alt="Gambar Korporat">
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 pt-12 border-t border-stone-200">

            <div class="lg:col-span-1 space-y-4">
                <span class="text-xs font-black text-blue-600 uppercase tracking-widest block">Matlamat Strategik</span>
                <h2 class="text-3xl font-black text-stone-900 tracking-tight">Objektif <br>Organisasi</h2>
                <p class="text-stone-500 text-sm leading-relaxed">
                    Rangka kerja teras yang digubal khusus untuk memastikan setiap operasi mencapai piawaian kualiti tertinggi yang telah ditetapkan.
                </p>
            </div>

            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                <div class="bg-white border border-stone-100 p-8 rounded-3xl shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 font-bold text-lg">
                        01
                    </div>
                    <h4 class="text-lg font-bold text-stone-900 mb-2">Pengurusan Strategik</h4>
                    <p class="text-stone-500 text-sm leading-relaxed">
                        Merancang, melaksana, dan memantau dasar-dasar utama organisasi bagi memastikan keselarasan dengan hala tuju negara.
                    </p>
                </div>

                <div class="bg-white border border-stone-100 p-8 rounded-3xl shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-stone-50 text-stone-800 rounded-2xl flex items-center justify-center mb-6 font-bold text-lg">
                        02
                    </div>
                    <h4 class="text-lg font-bold text-stone-900 mb-2">Pembangunan Digital</h4>
                    <p class="text-stone-500 text-sm leading-relaxed">
                        Memacu transformasi digital perkhidmatan korporat melalui integrasi sistem automasi moden yang selamat dan utuh.
                    </p>
                </div>

                <div class="bg-white border border-stone-100 p-8 rounded-3xl shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-stone-50 text-stone-800 rounded-2xl flex items-center justify-center mb-6 font-bold text-lg">
                        03
                    </div>
                    <h4 class="text-lg font-bold text-stone-900 mb-2">Pengukuhan Integriti</h4>
                    <p class="text-stone-500 text-sm leading-relaxed">
                        Menegakkan kawal selia dan audit dalaman secara telus bagi mengekalkan tahap kepercayaan pemegang taruh (stakeholders).
                    </p>
                </div>

                <div class="bg-white border border-stone-100 p-8 rounded-3xl shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 font-bold text-lg">
                        04
                    </div>
                    <h4 class="text-lg font-bold text-stone-900 mb-2">Optimasi Fungsi</h4>
                    <p class="text-stone-500 text-sm leading-relaxed">
                        Menilai prestasi berkala setiap sektor demi memperkemas proses rantaian kerja harian agar kekal responsif dan cekap.
                    </p>
                </div>

            </div>
        </div>
    </main>
    <x-footer />
</div>
