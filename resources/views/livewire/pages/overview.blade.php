<?php

use App\Models\Competition;
use App\Models\Program;
use Carbon\Carbon;
use function Livewire\Volt\{layout, state, mount};

layout('layouts.landing');

state([
    'competition' => null,
    'isExpired' => false,
    'program' => null,
    'openIndex' => null,
    'hasSubmitted' => false
]);

mount(function (Competition $competition) {
    $this->competition = $competition;

    // 1. Dapatkan program berserta semakan jika user ID semasa wujud dalam table submissions bagi program_id ini
    $this->program = Program::withExists(['submissions as has_submitted' => function($query) {
        $query->where('user_id', auth()->id());
    }])->where('competition_id', $competition->id)->first();

    if ($this->program) {
        // Set nilai status penyertaan berdasarkan program_id
        $this->hasSubmitted = $this->program->has_submitted ?? false;

        // 2. Semak deadline program
        if ($this->program->deadline) {
            $this->isExpired = Carbon::now()->greaterThan(Carbon::parse($this->program->deadline));
        }
    }
});

$toggle = function ($index) {
    $this->openIndex = $this->openIndex === $index ? null : $index;
};

?>

<div class="min-h-screen bg-gray-50">
    <x-top-nav />

    <div class="bg-gray-50 min-h-screen pb-20">
      <style>
          @keyframes floatIklan {
              0%, 100% { transform: translateY(0px); }
              50% { transform: translateY(-10px); }
          }
          .animate-float-premium {
              animation: floatIklan 4s ease-in-out infinite;
          }
      </style>

      <style>
          @keyframes floatIklan {
              0%, 100% { transform: translateY(0px); }
              50% { transform: translateY(-10px); }
          }
          .animate-float-premium {
              animation: floatIklan 4s ease-in-out infinite;
          }
      </style>

      <header class="relative py-20 md:py-28 overflow-hidden bg-gradient-to-br from-slate-50 via-gray-50 to-stone-100/50 px-6">
          <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-400/15 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12 relative z-10">

              <!-- Lajur Kiri: Teks Penerangan -->
              <div class="lg:w-7/12 text-center lg:text-left">
                  <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/80 shadow-sm mb-6">
                      <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                      <span class="text-blue-700 font-bold text-[10px] uppercase tracking-[0.2em]">{{ __('Pertandingan') }}</span>
                  </div>

                  <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 leading-[1.15] tracking-tight">
                      {{ $competition->name }}
                  </h2>

                  <p class="text-gray-600 mt-6 max-w-xl mx-auto lg:mx-0 leading-relaxed text-lg font-medium opacity-90">
                      {{ $competition->description }}
                  </p>
              </div>

              <!-- Lajur Kanan: Ruang Iklan Gambar & Butang -->
              <div class="lg:w-5/12 flex flex-col items-center lg:items-end justify-center w-full">
                  <!-- Pembungkus Utama Gambar + Butang (Kekal max-w-[460px] & gap-8) -->
                  <div class="w-full max-w-[460px] flex flex-col gap-8">

                      <!-- Bekas Gambar: h-[420px] & object-contain -->
                      <div class="relative group w-full h-[420px] flex items-center justify-center animate-float-premium">
                          <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/20 via-purple-500/20 to-pink-500/25 blur-3xl opacity-75 group-hover:opacity-100 group-hover:scale-130 transition-all duration-700 ease-out pointer-events-none"></div>

                          @if($competition->image_path)
                              <img src="{{ asset('storage/' . $competition->image_path) }}"
                                   alt="{{ $competition->name }}"
                                   class="relative z-10 w-full h-full object-contain transform scale-100 group-hover:scale-115 filter drop-shadow-[0_20px_30px_rgba(0,0,0,0.08)] group-hover:drop-shadow-[0_35px_45px_rgba(59,130,246,0.2)] transition-all duration-500 ease-out block">
                          @else
                              <div class="relative z-10 flex flex-col items-center justify-center text-gray-400 gap-2">
                                  <svg class="w-16 h-16 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 00-1.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                                  <span class="text-sm font-semibold tracking-wider uppercase text-gray-400">Tiada Gambar</span>
                              </div>
                          @endif
                      </div> <!-- Tag penutup bekas gambar yang betul -->

                      <div class="w-full flex justify-center">
                          <div class="w-full max-w-xs">
                              @if($hasSubmitted)

                              @elseif($isExpired)
                                  <button disabled
                                          class="w-full bg-slate-100 text-slate-400 font-bold text-xs uppercase tracking-widest py-4 px-8 rounded-[1.8rem] text-center cursor-not-allowed block">
                                      Pendaftaran Ditutup
                                  </button>
                              @elseif(!$program)
                                  <button disabled
                                          class="w-full bg-amber-50 text-amber-600 font-bold text-xs uppercase tracking-widest py-4 px-8 rounded-[1.8rem] text-center cursor-not-allowed block">
                                      Program Tidak Ditemui
                                  </button>
                              @else
                                  @auth
                                      @if(auth()->user()->role === 'admin' || auth()->user()->is_admin)
                                          <a href="#"
                                             onclick="alert('Akses tidak dibenarkan untuk Admin.'); return false;"
                                             class="w-full bg-slate-900 text-white font-bold text-xs uppercase tracking-widest py-4 px-8 rounded-[1.8rem] text-center shadow-md block transition-all duration-300 transform active:scale-95">
                                              Sertai Sekarang
                                          </a>
                                      @else
                                          <a href="{{ route('project.submit', $program->id) }}"
                                             class="w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 hover:from-blue-700 hover:via-indigo-700 hover:to-violet-700 text-white font-bold text-xs uppercase tracking-widest py-4 px-8 rounded-[1.8rem] text-center shadow-[0_10px_25px_rgba(79,70,229,0.2)] hover:shadow-[0_15px_30px_rgba(79,70,229,0.3)] block transition-all duration-300 transform active:scale-95 subpixel-antialiased">
                                              Sertai Sekarang &rarr;
                                          </a>
                                      @endif
                                  @else
                                      <a href="{{ route('login') }}?intended={{ urlencode(route('project.submit', $program->id)) }}"
                                         class="w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 hover:from-blue-700 hover:via-indigo-700 hover:to-violet-700 text-white font-bold text-xs uppercase tracking-widest py-4 px-8 rounded-[1.8rem] text-center shadow-[0_10px_25px_rgba(79,70,229,0.2)] hover:shadow-[0_15px_30px_rgba(79,70,229,0.3)] block transition-all duration-300 transform active:scale-95 subpixel-antialiased">
                                          Daftar & Sertai &rarr;
                                      </a>
                                  @endauth
                              @endif
                          </div>
                      </div>
                  </div> <!-- Penutup bagi w-full max-w-[460px] -->
              </div> <!-- Penutup bagi lg:w-5/12 -->

          </div>
      </header>


      <div class="max-w-7xl mx-auto px-6 my-8">
            <div class="grid lg:grid-cols-3 gap-8 mb-16">
                <div class="lg:col-span-2 bg-white p-10 rounded-[3rem] shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-black text-[#002966] mb-6 flex items-center gap-3">
                        <span class="text-3xl">🔍</span> Pengenalan
                    </h2>
                    <div class="space-y-4 text-gray-600 leading-relaxed text-justify">
                        <p>
                            {{ $competition->introduction }}
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-900 to-blue-800 p-10 rounded-[3rem] text-white flex flex-col justify-center shadow-xl">
                    <div class="text-sm font-black uppercase tracking-[0.2em] text-blue-300 mb-4 text-center">Kitaran Acara</div>
                    <div class="text-6xl font-black text-center mb-2">{{ $competition->cycle }}</div>
                    <div class="text-xl font-bold text-center text-blue-100 uppercase tracking-widest">Tahun Sekali</div>
                </div>
            </div>

            <div class="mb-20">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-black text-[#002966] uppercase tracking-tighter">Tujuan</h2>
                    <p class="text-gray-500 mt-2 italic font-medium">"{{ $competition->objectives['main'] ?? 'Meningkatkan mutu penyampaian perkhidmatan kerajaan.' }}"</p>
                </div>

                <div class="flex flex-wrap justify-center gap-6 text-center">
                    @foreach($competition->objectives['items'] as $item)
                        <div class="group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 w-full sm:w-80 flex flex-col items-center">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-4xl mb-6 group-hover:scale-110 transition-transform">
                                    {{ $item['icon'] ?? '💡' }}
                                </div>
                                <h4 class="font-black text-gray-900 text-lg uppercase mb-3 tracking-tight">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="text-gray-500 text-sm leading-relaxed">
                                    {{ $item['desc'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @php
                $showTwoColumns = ($competition->categories && count($competition->categories) > 0) || ($competition->tracks && count($competition->tracks) > 0);
            @endphp

            <div class="grid {{ $showTwoColumns ? 'lg:grid-cols-2' : 'grid-cols-1 justify-items-center' }} gap-10 mb-20">
                <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-black text-blue-900 mb-8 uppercase tracking-widest flex items-center gap-3">
                        <span class="text-2xl">📝</span> Syarat Penyertaan
                    </h3>
                    <ul class="space-y-6">
                        @foreach($competition->requirements as $req)
                            <li class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center {{ $req['is_allowed'] ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500' }}">
                                    @if($req['is_allowed'])
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ $req['title'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $req['desc'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if(($competition->categories && count($competition->categories) > 0) || ($competition->tracks && count($competition->tracks) > 0))
                <div class="bg-blue-900 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 opacity-10 text-8xl translate-x-10 translate-y-10">✨</div>
                    <h3 class="text-xl font-black text-blue-300 mb-8 uppercase tracking-widest">{{ $competition->categories && count($competition->categories) > 0 ? 'Kategori &' : '' }} {{ $competition->tracks && count($competition->tracks) > 0 ? 'Bidang' : '' }}</h3>
                    <div class="grid sm:grid-cols-2 gap-8">
                        @if($competition->categories && count($competition->categories) > 0)
                        <div class="space-y-4">
                            <h4 class="text-amber-500 font-bold uppercase text-xs tracking-widest">Kategori</h4>
                            <div class="space-y-3">
                                @forelse($competition->categories as $cat)
                                    <div class="bg-emerald-500/20 border border-emerald-400/20 p-4 rounded-2xl">
                                        <p class="font-bold text-sm">{{ $cat }}</p>
                                        <p class="text-[10px] text-emerald-200 uppercase">{{ $cat }}</p>
                                    </div>
                                @empty
                                    <div class="p-4 rounded-2xl border border-dashed border-gray-700/50 flex flex-col items-center justify-center">
                                        <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest italic">Tiada Kategori Ditetapkan</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        @endif

                        @if($competition->tracks && count($competition->tracks) > 0)
                        <div class="space-y-4">
                            <h4 class="text-amber-500 font-bold uppercase text-xs tracking-widest">Bidang</h4>
                            <div class="space-y-3">
                            @forelse($competition->tracks as $track)
                                <div class="bg-white/10 p-4 rounded-2xl">
                                    <p class="font-bold text-sm">{{ $track }}</p>
                                </div>
                            @empty
                                <!--<div class="p-4 rounded-2xl border border-dashed border-gray-700/50 flex flex-col items-center justify-center">
                                    <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest italic">Tiada Bidang Ditetapkan</p>
                                </div>-->
                            @endforelse
                            </div>
                      </div>
                      @endif
                </div>
            </div>
            @endif
        </div>

        <div class="mb-20 bg-white rounded-[4rem] p-12 border border-gray-100 shadow-sm">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black text-[#002966]">GANJARAN & PENGIKTIRAFAN</h2>
                <div class="w-20 h-1.5 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end max-w-5xl mx-auto">
                <div class="order-2 md:order-1 flex flex-col items-center">
                    <div class="text-4xl mb-4">🥈</div>
                    <div class="w-full bg-gray-50 rounded-t-[3rem] p-8 border border-gray-200 border-b-0 text-center">
                        <h3 class="font-black text-gray-500 text-xs uppercase mb-2">Naib Johan</h3>
                        <p class="text-3xl font-black text-gray-800">{{ $competition->prizes['naib_johan'] }}</p>
                        <p class="text-[10px] text-white-100 mt-4 font-bold uppercase tracking-widest">Sijil Penghargaan & Penyertaan</p>
                    </div>
                </div>

                <div class="order-1 md:order-2 flex flex-col items-center">
                    <div class="text-6xl mb-4 animate-bounce">🥇</div>
                    <div class="w-full bg-gradient-to-b from-amber-400 to-amber-600 rounded-t-[3rem] p-10 text-center shadow-2xl">
                        <h3 class="font-black text-amber-950 text-xs uppercase mb-2">Johan Keseluruhan</h3>
                        <p class="text-4xl font-black text-white">RM {{ $competition->prizes['johan'] }}</p>
                        <p class="text-[10px] text-amber-100 mt-4 font-bold uppercase tracking-widest">Piala Pusingan & Iringan</p>
                    </div>
                </div>

                <div class="order-3 flex flex-col items-center">
                    <div class="text-4xl mb-4">🥉</div>
                    <div class="w-full bg-gray-50 rounded-t-[3rem] p-8 border border-gray-200 border-b-0 text-center">
                        <h3 class="font-black text-gray-500 text-xs uppercase mb-2">Tempat Ketiga</h3>
                        <p class="text-3xl font-black text-gray-800">RM {{ $competition->prizes['ketiga'] }}</p>
                        <p class="text-[10px] text-white-100 mt-4 font-bold uppercase tracking-widest">Sijil Penghargaan & Penyertaan</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-12 pt-12 border-t border-gray-100">
                <!--<div class="text-center p-4 bg-gray-50 rounded-3xl">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Inovasi Terbaik</p>
                    <p class="font-black text-blue-900">RM 2,000</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-3xl">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Hibrid Terbaik</p>
                    <p class="font-black text-blue-900">RM 2,000</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-3xl">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Dokumentasi</p>
                    <p class="font-black text-blue-900">RM 1,500</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-3xl">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Persembahan</p>
                    <p class="font-black text-blue-900">RM 1,500</p>
                </div>-->
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-10 border-t border-gray-200">
            <div class="flex items-center gap-4 text-gray-400">
                <!--<div class="text-sm font-medium italic">Kongsi info ini:</div>
                <div class="flex gap-2">
                    <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm">f</button>
                    <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all shadow-sm">w</button>
                </div>-->
            </div>

            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="group flex items-center gap-3 px-8 py-3 bg-gray-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl">
                Kembali ke Atas
                <span class="group-hover:-translate-y-1 transition-transform">↑</span>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    .animate-float {
        animation: float 5s ease-in-out infinite;
    }
    /* Font serif khusus untuk 'Inovasi' */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap');
    .font-serif {
        font-family: 'Playfair Display', serif;
    }
</style>

    <x-footer />
</div>
