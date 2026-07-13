<?php

use App\Models\Archive;
use App\Models\Quiz;
use App\Models\Program;
use function Livewire\Volt\{layout, with, state, mount};

layout('layouts.landing');

with([
  'projects' => Archive::latest()->get(),
  'programs' => fn() => Program::where(function ($query) {
          $query->whereDate('start_date', '>=', now())
                ->orWhereNull('start_date')
                ->orWhere('start_date', '0000-00-00');
      })
      ->latest()
      ->get(),
]);

state([
    'showWelcome' => true,
    'userAnswer' => null,
    'isCorrect' => null,
    'activeQuestion' => null,
    'extras' => null,
]);

mount(function () {
    $data = Quiz::inRandomOrder()->first();

    if($data) {
        $this->activeQuestion = [
            'id'       => $data->id,
            'text'     => $data->question,
            'options'  => [
                'A' => $data->option_a,
                'B' => $data->option_b,
                'C' => $data->option_c,
                'D' => $data->option_d,
            ],
            'answer'   => $data->correct_answer,
            'extras'   => $data->extras,
        ];
    }
});

$checkAnswer = function ($key) {
    if ($this->userAnswer === null) {
        $this->userAnswer = $key;

        $lowercaseKey = strtolower($key);

        $this->isCorrect = ($lowercaseKey === $this->activeQuestion['answer']);
    }
};

?>

<div class="min-h-screen bg-[#f8fafc] relative">
    <style>
    @keyframes slideInUp {
        0% {
            transform: translateY(70px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    </style>
    <!--<div class="fixed inset-0 pointer-events-none" style="z-index: 0; overflow: hidden;">
        <div style="position: absolute; inset: 0; opacity: 0.35; background-image: radial-gradient(#334155 2px, transparent 2px); background-size: 40px 40px;"></div>

        <div style="position: absolute; top: 0; right: 0; width: 800px; height: 800px; background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, transparent 70%); filter: blur(60px); transform: translate(20%, -20%);"></div>

        <div style="position: absolute; bottom: 0; left: 0; width: 600px; height: 600px; background: radial-gradient(circle, rgba(124, 58, 237, 0.25) 0%, transparent 70%); filter: blur(60px); transform: translate(-20%, 20%);"></div>

        <div style="position: absolute; inset: 0; background: radial-gradient(circle at center, transparent 0%, rgba(253, 253, 252, 0.4) 100%);"></div>
    </div>-->


    <x-top-nav />
    <div class="relative z-10">

        <header class="relative py-32 px-6 bg-gray-900 overflow-visible">
            <div class="absolute inset-0 z-0 overflow-hidden">
                <img src="{{ asset('images/corporate.jpg') }}"
                    class="w-full h-full object-cover opacity-40 scale-125 transform"
                    style="object-position: center;"
                    alt="Innovation Background">
                <div class="absolute inset-0 bg-black/50"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-gray-900/80 via-transparent to-gray-900"></div>
            </div>

            <div class="max-w-4xl mx-auto text-center relative z-10">
                 <h2 class="text-5xl md:text-7xl font-black text-white mb-8 tracking-tighter leading-tight">
                    Teroka Idea <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Masa Depan</span> Melalui Inovasi.
                </h2>

                <p class="text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed mb-10">
                    Himpunan idea kreatif dan projek digital warga jabatan yang memacu transformasi dan kecemerlangan perkhidmatan.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#explore" class="w-full sm:w-auto px-8 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl">
                        Lihat Arkib Projek
                    </a>
                    <a href="{{ route('pitch') }}" class="w-full sm:w-auto px-8 py-4 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-2xl font-bold hover:bg-white/20 transition">
                        Hantar Idea Baru
                    </a>
                </div>
            </div>
        </header>

        <section class="relative mx-auto max-w-7xl px-6" style="margin-top: -100px; z-index: 30;">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('archive') }}" wire:navigate class="flex group">
                    <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-50 hover:bg-blue-600 transition-all duration-500 transform hover:-translate-y-2 h-full">
                        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-500 transition">
                            <svg class="w-7 h-7 text-blue-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-white mb-2">Arkib Inovasi</h3>
                        <p class="text-gray-500 group-hover:text-blue-100 text-sm leading-relaxed">Himpunan projek kreatif yang telah berjaya dilaksanakan.</p>
                    </div>
                </a>
                <a href="{{ route('coff-b') }}" wire:navigate class="flex group">
                    <div class="group bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-50 hover:bg-orange-500 transition-all duration-500 transform hover:-translate-y-2">
                        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-orange-400 transition">
                            <svg class="w-7 h-7 text-orange-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"></path>
                                <line x1="6" y1="1" x2="6" y2="4" stroke-linecap="round" stroke-width="2"></line>
                                <line x1="10" y1="1" x2="10" y2="4" stroke-linecap="round" stroke-width="2"></line>
                                <line x1="14" y1="1" x2="14" y2="4" stroke-linecap="round" stroke-width="2"></line>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-white mb-2">Coffee Break (Coff-B)</h3>
                        <p class="text-gray-500 group-hover:text-orange-100 text-sm leading-relaxed">Ruang santai untuk berkongsi idea pantas secara spontan bagi warga JPA.</p>
                    </div>
                </a>

                <a href="{{ route('quiz') }}" wire:navigate class="flex group">
                    <div class="group bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-50 hover:bg-purple-500 transition-all duration-500 transform hover:-translate-y-2">
                         <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-purple-500 transition">
                             <svg class="w-7 h-7 text-purple-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.364-6.364l-.707-.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M12 21V3"></path></svg>
                         </div>
                         <h3 class="text-xl font-bold text-gray-900 group-hover:text-white mb-2">Kuiz Inovasi</h3>
                         <p class="text-gray-500 group-hover:text-purple-100 text-sm leading-relaxed">Uji tahap pengetahuan anda mengenai teknologi terkini.</p>
                    </div>
                </a>

                <a href="{{ route('entries')}}" wire:navigate class="flex group">
                    <div class="group bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-50 hover:bg-green-600 transition-all duration-500 transform hover:-translate-y-2">
                        <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-green-500 transition">
                            <svg class="w-7 h-7 text-green-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-white mb-2">Penyertaan Pertandingan</h3>
                        <p class="text-gray-500 group-hover:text-green-100 text-sm leading-relaxed">Sertai komuniti dengan menghantar inovasi anda sendiri.</p>
                    </div>
                </a>
            </div>
        </section>

        <main id="explore" class="relative z-10 max-w-7xl mx-auto px-6 pt-20 pb-32">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div class="relative">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center gap-1.5 bg-blue-600 text-white px-3 py-1 rounded-full shadow-lg shadow-blue-200">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-[0.15em]">Jangan Terlepas</span>
                        </div>
                    </div>

                    <h3 class="text-3xl md:text-3xl font-black text-stone-900 tracking-tighter leading-[0.85]">
                        Acara Akan
                        <span class="text-blue-600 italic font-serif">Datang</span>
                    </h3>
                </div>

                @if($programs->isNotEmpty())
                <div class="flex justify-end items-center gap-3 mb-6 px-2">
                    <button onclick="scrollSlider('left')" class="p-3 bg-white border border-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white hover:border-orange-500 rounded-2xl shadow-sm transition duration-300 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button onclick="scrollSlider('right')" class="p-3 bg-white border border-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white hover:border-orange-500 rounded-2xl shadow-sm transition duration-300 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
                @endif
            </div>

            <div id="programSlider" class="@if($programs->isNotEmpty()) flex space-x-8 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-8 pt-2 px-2 scrollbar-none @else grid grid-cols-1 w-full @endif">
                @forelse($programs as $program)
                <div x-data="{ shown: false }"
                      x-intersect.once="shown = true"
                      class="group bg-white rounded-[2.5rem] shadow-sm overflow-hidden border border-gray-100 hover:shadow-2xl w-[calc(100vw-3rem)] md:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.35rem)] shrink-0 snap-start transform transition-all duration-700 ease-out"
                      :class="shown ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'">
                    <div class="relative h-64 overflow-hidden">
                        <img src="{{ asset('storage/' . $program->image_path) }}"
                           class="w-full h-full object-cover object-top transition duration-700 group-hover:scale-110" />

                        <div class="absolute top-6 left-6 bg-white/95 backdrop-blur px-4 py-2 rounded-2xl shadow-lg text-center">
                              <span class="block text-xl font-black text-gray-900 leading-none">
                                  {{ ($program->start_date && $program->start_date !== '0000-00-00') ? \Carbon\Carbon::parse($program->start_date)->format('d') : '--' }}
                              </span>
                              <span class="block text-[10px] font-bold text-orange-600 uppercase tracking-widest">
                                  {{ ($program->start_date && $program->start_date !== '0000-00-00') ? \Carbon\Carbon::parse($program->start_date)->format('M') : 'TBD' }}
                              </span>
                        </div>
                    </div>

                    <div class="p-10">
                       <div class="flex items-center space-x-2 mb-4">
                            <span class="px-3 py-1 bg-orange-50 text-orange-600 text-[10px] font-black uppercase rounded-full">{{ $program->category->name ?? 'Tiada Kategori' }}</span>
                            <span class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">{{ $program->location ?? '' }}</span>
                        </div>

                        <h3 class="text-xl font-extrabold text-gray-900 mb-4 leading-tight group-hover:text-orange-600 transition line-clamp-1">
                            {{ $program->title }}
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed mb-8">{{ $program->description }}</p>

                        @php
                            $isClosed = $program->deadline ? \Carbon\Carbon::parse($program->deadline)->isPast() : false;
                        @endphp

                        @if($isClosed)
                              <button class="w-full py-4 bg-gray-200 text-gray-400 rounded-2xl font-bold text-sm cursor-not-allowed" disabled>
                                  Penyertaan Ditutup
                              </button>
                        @else
                        @php
                            $hasApplied = false;
                            if (auth()->check()) {
                                $hasApplied = auth()->user()->submissions()
                                    ->where('program_id', $program->id)
                                    ->exists();
                            }
                        @endphp
                              @if(!auth()->check())
                              <button class="w-full py-4 bg-gray-50 text-gray-900 rounded-2xl font-bold text-sm group-hover:bg-orange-500 group-hover:text-white transition duration-300">
                                  Sertai Kami
                              </button>
                              @elseif($hasApplied)
                              <button class="w-full py-4 bg-gray-200 text-gray-400 rounded-2xl font-bold text-sm cursor-not-allowed" disabled>
                                  Telah sertai
                              </button>
                              @endif

                        @endif
                   </div>
                </div>
                @empty
                <div class="w-full py-6">
                   <div class="w-full bg-orange-50 border-2 border-dashed border-orange-200 rounded-3xl p-12 flex flex-col items-center text-center shadow-sm">
                        <div class="p-4 bg-white text-orange-500 rounded-2xl mb-5 shadow-sm flex items-center justify-center w-20 h-20">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 animate-bounce">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008H14.25v-.008Zm0 2.25h.008v.008H14.25V15Zm0 2.25h.008v.008H14.25v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                              </svg>
                          </div>

                          <h3 class="text-2xl font-bold text-orange-950 mb-3">Tiada Program Tersedia Buat Masa Ini</h3>
                          <p class="text-orange-800/80 max-w-xl text-base leading-relaxed mx-auto">
                               Sila semak semula seketika lagi atau hubungi pihak kami untuk maklumat lanjut mengenai jadual program akan datang.
                          </p>
                      </div>
                </div>
                @endforelse
            </div>
        </main>
    </div>

    <script>
          function scrollSlider(direction) {
              const slider = document.getElementById('programSlider');
              const cardWidth = slider.querySelector('.group').offsetWidth + 32;

              if (direction === 'left') {
                  slider.scrollLeft -= cardWidth;
              } else {
                  slider.scrollLeft += cardWidth;
              }
           }
    </script>


    <section id="info" class="relative py-24 bg-gray-50 overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 bg-blue-100/50 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-72 h-72 bg-indigo-100/50 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-16">
              <div class="w-full lg:w-1/2 relative overflow-visible p-6">
                  <div class="relative z-10 rounded-[2.5rem] overflow-hidden shadow-2xl border-8 border-white">
                      <img src="{{ asset('images/jpa.jpg') }}"
                           class="w-full h-[450px] object-cover"
                           alt="Gambar">
                  </div>
                  <div class="absolute -left-2 md:-left-4 bottom-16 bg-white p-6 rounded-2xl shadow-xl z-20">
                      <div class="flex items-center space-x-4">
                          <div class="flex -space-x-2">
                              <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-white"></div>
                              <div class="w-8 h-8 rounded-full bg-indigo-500 border-2 border-white"></div>
                              <div class="w-8 h-8 rounded-full bg-cyan-500 border-2 border-white"></div>
                          </div>
                          <p class="text-[10px] font-bold text-gray-900 uppercase tracking-tighter italic leading-none">
                              100+ Warga <br><span class="text-blue-600">Terlibat</span>
                          </p>
                      </div>
                  </div>
              </div>
              <div class="w-full lg:w-1/2">
                    <span class="text-blue-600 font-bold text-xs uppercase tracking-[0.3em] mb-4 block">Mengenai Kami</span>
                    <h2 class="text-4xl font-bold text-gray-900 mb-6 tracking-tight">
                        Memacu Budaya <span class="text-blue-600 italic">Kreativiti</span> Dalam Organisasi.
                    </h2>
                    <p class="text-gray-600 text-lg leading-relaxed mb-8">
                        Hab Inovasi merupakan inisiatif digital yang direka khas untuk menjadi pusat rujukan idea dan solusi kreatif. Kami percaya bahawa setiap warga jabatan mempunyai potensi untuk membawa perubahan positif melalui teknologi dan inovasi.
                    </p>

                    <div class="space-y-4 mb-10">
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <p class="text-gray-700 font-medium">Melahirkan bakat inovasi yang kompetitif.</p>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </div>
                            <p class="text-gray-700 font-medium">Memudahkan akses kepada arkib idea digital.</p>
                        </div>
                    </div>

                    <a href="{{ route('info') }}" class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200 group">
                        Ketahui Lebih Lanjut
                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <x-footer />
</div>

    <div x-data="{ open: false }"
            x-init="const lastSeen = localStorage.getItem('promoLastSeen');
                    const now = Date.now();
                    const oneDay = 24 * 60 * 60 * 1000;

                    if (!lastSeen || (now - lastSeen) > oneDay) {
                        setTimeout(() => open = true, 800);
                        localStorage.setItem('promoLastSeen', now);
                    }"
            class="relative">
        <div x-show="open"
            class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div x-show="open"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-300"
                class="absolute inset-0 bg-slate-900/10 backdrop-blur-[2px]"></div>

                <div x-show="open"
                    x-transition:enter="transition cubic-bezier(0.175, 0.885, 0.32, 1.275) duration-600"
                    x-transition:enter-start="opacity-0 scale-50 rotate-[-5deg]"
                    x-transition:enter-end="opacity-100 scale-100 rotate-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    class="relative bg-white/95 backdrop-blur-2xl w-full max-w-2xl rounded-[3.5rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.2)] border border-white p-12 text-center">

                    <div class="flex items-center justify-center gap-6 mb-8">
                        <div class="w-16 h-16 bg-blue-600 rounded-[1.5rem] flex items-center justify-center shadow-lg shadow-blue-200 shrink-0">
                            <span class="text-3xl animate-bounce">⚡</span>
                        </div>

                        <h3 class="text-3xl font-black text-gray-900 tracking-wider text-left leading-tight">
                            KUIZ KILAT INOVASI
                        </h3>
                    </div >
                    <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    @if($activeQuestion)
                        <div class="bg-gray-50/80 rounded-[2rem] p-8 mb-6 border border-gray-100 shadow-inner min-h-[100px] flex items-center justify-center">
                            <p class="text-gray-800 font-bold text-lg leading-snug">
                                {{ $activeQuestion['text'] }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-8 text-left">
                            @foreach($activeQuestion['options'] as $key => $value)
                                <button wire:click="checkAnswer('{{ $key }}')"
                                    @disabled($userAnswer !== null)
                                    class="group w-full py-4 px-6 rounded-2xl font-bold text-sm transition-all duration-300 border-2 flex items-center
                                    {{ $userAnswer === $key
                                    ? ($isCorrect ? 'bg-green-600 border-green-600 text-white shadow-lg shadow-green-200' : 'bg-red-600 border-red-600 text-white shadow-lg shadow-red-200')
                                    : 'bg-white border-gray-100 text-gray-700 hover:border-blue-400 hover:bg-blue-50 active:scale-95' }}">

                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center mr-4 shrink-0
                                        {{ $userAnswer === $key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600 font-black' }}">
                                        {{ $key }}
                                    </span>

                                    <span class="leading-tight">{{ $value }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="min-h-[60px]">
                            @if($userAnswer !== null)
                                <div class="animate-bounce mb-6">
                                    <p class="text-sm font-black {{ $isCorrect ? 'text-green-600' : 'text-red-600' }} uppercase tracking-widest">
                                        {{ $isCorrect ? 'Tahniah! Jawapan Anda Tepat ✨' : 'Alamak! Cuba lagi nanti 💡' }}
                                    </p>
                                </div>

                                @if($isCorrect && !empty($activeQuestion['extras']))
                                    <div x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-y-4"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="bg-blue-50/50 rounded-3xl p-6 mb-6 border border-blue-100 text-left relative overflow-hidden">
                                        <div class="absolute -right-2 -top-2 text-blue-100/50 text-4xl select-none">💡</div>
                                        <div class="relative z-10">
                                            <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-2">Fakta Menarik</h4>
                                            <p class="text-gray-700 text-sm leading-relaxed font-medium">
                                                {{ $activeQuestion['extras'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                </div>
                                <button x-on:click="open = false"
                                    class="inline-flex items-center gap-3 px-6 py-2.5 bg-gray-900 text-white rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-200 transition-all duration-300 active:translate-y-0.5">
                                    <span>Langkau ke Laman Utama</span>
                                    <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center group-hover:rotate-45 transition-transform">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </div>
                                </button>
                            @else
                                <button x-on:click="open = false"
                                    class="inline-flex items-center gap-3 px-6 py-2.5 bg-gray-900 text-white rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-200 transition-all duration-300 active:translate-y-0.5">
                                    <span>Langkau ke Laman Utama</span>
                                    <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center group-hover:rotate-45 transition-transform">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </div>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-gray-400 italic text-sm">Sedang menyediakan soalan...</div>
                    @endif
                </div>
            </div>
        </div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 10px;
    }
</style>


<script>
    window.addEventListener('hashchange', function() {
        location.reload();
    });
</script>
