<?php

use App\Models\Pitch;
use App\Models\Vote;
use App\Models\Review;
use function Livewire\Volt\{layout, state, computed};

layout('layouts.landing');

state([
    'selectedMonth' => (int) date('m'),
    'selectedYear' => (int) date('Y'),
    'showModal' => false,
    'showViewModal' => false,
    'viewingPitch' => null,
    'title' => '',
    'description' => '',
    'method' => '',
    'page_name' => '',
    'body' => '',
    'rating' => 5,
    'months' => [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
        5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
    ]
]);

// 1. Ambil Juara tertinggi sepanjang zaman secara bebas
$topPitch = computed(function () {
    return Pitch::withCount('votes')
        ->withExists(['votes as has_voted' => function($query) {
            $query->where('user_id', auth()->id());
        }])
        ->orderByDesc('votes_count')
        ->first();
});

$pitches = computed(function () {
    return Pitch::withCount('votes')
        ->withExists(['votes as has_voted' => function($query) {
            $query->where('user_id', auth()->id());
        }])
        ->orderByDesc('votes_count') // Paling tinggi undi gerenti duduk atas sekali (Index 0)
        ->latest() // Pemutus seri jika undi sama
        ->get();
});

$viewDetails = function($id) {
    $this->viewingPitch = Pitch::withCount('votes')
        ->with(['votes.user' => function($query) {
            // Mengambil maklumat pengundi dan jabatan mereka (jika perlu dipapar)
            $query->with('department');
        }])
        ->find($id);

    $this->showViewModal = true;
};

$vote = function (Pitch $pitch) {
    $user = auth()->user();

    if ($pitch->user_id === $user->id) {
        session()->flash('error', 'Anda tidak boleh mengundi idea anda sendiri!');
        return;
    }

    if ((isset($user->is_admin) && $user->is_admin) || (isset($user->role) && $user->role === 'admin')) {
        session()->flash('error', 'Pengguna berstatus Pentadbir (Admin) tidak dibenarkan mengundi.');
        return;
    }

    $existingVote = Vote::where('user_id', $user->id)->where('pitch_id', $pitch->id)->first();

    if ($existingVote) {
        $existingVote->delete();
        $user->decrement('credits', 1);
    } else {
        Vote::create(['user_id' => $user->id, 'pitch_id' => $pitch->id]);
        $user->increment('credits', 1);
    }
};

$reviews = computed(function () {
    return Review::query()
        ->with(['user'])
        ->where('page_name', 'pitch')
        ->latest()
        ->limit(5)
        ->get();
});

$save = function () {
    $this->validate([
      'body' => 'required|string|max:1000',
      'rating' => 'required|integer|min:1|max:5',
    ]);

    Review::create([
        'user_id' => auth()->id(),
        'body' => $this->body,
        'rating' => $this->rating,
        'page_name' => 'pitch',
    ]);

    $this->body = '';
    $this->rating = 5;

    session()->flash('success', 'Ulasan anda telah berjaya dihantar!');

    $this->dispatch('review-added');
};

$delete = function ($id) {
    $review = Review::find($id);

    if ($review && $review->user_id === auth()->id()) {
        $review->delete();

        session()->flash('success', 'Ulasan anda telah dipadam!');

        $this->dispatch('review-added');
    }
};


?>
<div class="min-h-screen bg-[#faf7f2] text-[#4a3728] font-sans pb-20 overflow-x-hidden">
    <style>
        .pitch-gradient-header {
            background: linear-gradient(135deg, #111827 0%, #1e1b4b 100%);
        }
        [x-cloak] { display: none !important; }

        @keyframes paper-fly-left {
            0% {
                transform: translateY(20px) translateX(0px) rotate(0deg) scale(0.3);
                opacity: 0;
            }
            30% {
                opacity: 1;
                transform: translateY(-40px) translateX(-20px) rotate(-15deg) scale(0.8);
            }
            70% {
                transform: translateY(-100px) translateX(-40px) rotate(-5deg) scale(1);
            }
            100% {
                transform: translateY(-160px) translateX(-60px) rotate(-25deg) scale(0.4);
                opacity: 0;
            }
        }
        .animate-paper-left {
            animation: paper-fly-left 6s ease-in-out infinite;
        }

        @keyframes paper-fly-right {
            0% {
                transform: translateY(30px) translateX(0px) rotate(0deg) scale(0.2);
                opacity: 0;
            }
            25% {
                opacity: 1;
                transform: translateY(-30px) translateX(30px) rotate(25deg) scale(0.7);
            }
            60% {
                transform: translateY(-90px) translateX(15px) rotate(45deg) scale(0.9);
            }
            100% {
                transform: translateY(-180px) translateX(50px) rotate(15deg) scale(0.5);
                opacity: 0;
            }
        }
        .animate-paper-right {
            animation: paper-fly-right 5s ease-in-out infinite;
        }

        @keyframes paper-fly-center {
            0% {
                transform: translateY(40px) translateX(-10px) rotate(-10deg) scale(0.4);
                opacity: 0;
            }
            40% {
                opacity: 1;
                transform: translateY(-50px) translateX(10px) rotate(10deg) scale(0.9);
            }
            80% {
                transform: translateY(-120px) translateX(-5px) rotate(-20deg) scale(0.7);
            }
            100% {
                transform: translateY(-200px) translateX(0px) rotate(5deg) scale(0.3);
                opacity: 0;
            }
        }
        .animate-paper-center {
            animation: paper-fly-center 7s ease-in-out infinite;
        }

        @keyframes box-float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-8px) rotate(2deg); /* Naik 8px dan senget 2 darjah ke kanan */
            }
        }

        .animate-box-float {
            animation: box-float 5s ease-in-out infinite;
        }
    </style>
    <x-top-nav />

    <div class="fixed top-20 -left-10 opacity-20 rotate-45 pointer-events-none">
        <span class="text-8xl">❓❓</span>
    </div>
    <div class="fixed bottom-10 -right-10 opacity-10 -rotate-12 pointer-events-none">
        <span class="text-[120px]">✨</span>
    </div>

    <div class="max-w-7xl mx-auto px-6">
         <header class="my-5 rounded-[50px] p-10 md:p-16 mb-16 shadow-2xl relative overflow-hidden text-white border border-cyan-500/20
               bg-slate-950 bg-[radial-gradient(circle_at_center,rgba(6,182,212,0.12)_0%,transparent_75%)]">
               <div class="absolute inset-0 rounded-[50px] border-2 border-cyan-500/10 pointer-events-none shadow-[inset_0_0_30px_rgba(6,182,212,0.1)] z-0"></div>
               <div class="absolute -top-20 -left-20 w-80 h-80 bg-amber-500/5 rounded-full blur-[100px] pointer-events-none z-0"></div>
               <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-cyan-500/20 rounded-full blur-[130px] pointer-events-none z-0"></div>

               <div class="absolute inset-0 w-full h-full opacity-40 pointer-events-none z-0"
                        style="mask-image: radial-gradient(circle, white, transparent 80%); -webkit-mask-image: radial-gradient(circle, white, transparent 80%);">
                       <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                           <defs>
                               <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                   <feGaussianBlur stdDeviation="2" result="blur" />
                                   <feComposite in="SourceGraphic" in2="blur" operator="over" />
                               </filter>
                           </defs>

                           <g stroke="rgba(6, 182, 212, 0.15)" stroke-width="1">
                               <line x1="10%" y1="20%" x2="25%" y2="45%" />
                               <line x1="25%" y1="45%" x2="45%" y2="25%" />
                               <line x1="45%" y1="25%" x2="40%" y2="70%" />
                               <line x1="25%" y1="45%" x2="15%" y2="80%" />
                               <line x1="40%" y1="70%" x2="65%" y2="85%" />
                               <line x1="45%" y1="25%" x2="70%" y2="35%" />
                               <line x1="70%" y1="35%" x2="85%" y2="15%" />
                               <line x1="70%" y1="35%" x2="75%" y2="75%" />
                               <line x1="75%" y1="75%" x2="90%" y2="50%" />
                               <line x1="40%" y1="70%" x2="75%" y2="75%" />
                           </g>

                           <g fill="rgba(6, 182, 212, 0.6)" filter="url(#glow)">
                               <circle cx="10%" cy="20%" r="3" class="animate-pulse" />
                               <circle cx="25%" cy="45%" r="4" />
                               <circle cx="45%" cy="25%" r="3.5" />
                               <circle cx="15%" cy="80%" r="2.5" />
                               <circle cx="40%" cy="70%" r="5" />
                               <circle cx="65%" cy="85%" r="3" />
                               <circle cx="70%" cy="35%" r="4.5" />
                               <circle cx="85%" cy="15%" r="3.5" />
                               <circle cx="75%" cy="75%" r="4" />
                               <circle cx="90%" cy="50%" r="3" />
                           </g>
                       </svg>
              </div>
              <div class="relative z-10 grid lg:grid-cols-2 gap-12 items-center">
                  <div class="space-y-6 text-center lg:text-left">
                       <div class="inline-flex items-center px-4 py-1.5 bg-indigo-600/30 border border-indigo-500/40 rounded-full">
                            <span class="text-indigo-300 text-[9px] font-black uppercase tracking-[0.3em]">KREATIVITI & INOVASI</span>
                       </div>

                       <h1 class="text-5xl md:text-6xl font-black leading-tight tracking-tighter flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">

                           <div class="relative w-40 h-40 md:w-52 md:h-52 flex-shrink-0 origin-center">
                               <img src="/images/paper.png" class="absolute bottom-20 left-1/2 -translate-x-1/2 animate-paper-left w-12 h-14 pointer-events-none" style="animation-delay: 0s;">
                               <img src="/images/paper.png" class="absolute bottom-20 left-1/2 -translate-x-1/2 animate-paper-right w-10 h-12 pointer-events-none" style="animation-delay: 1.5s;">
                               <img src="/images/paper.png" class="absolute bottom-20 left-1/2 -translate-x-1/2 animate-paper-center w-10 h-13 pointer-events-none" style="animation-delay: 3s;">

                               <img src="/images/box-of-ideas.png" class="w-full h-full object-contain relative z-10 animate-box-float"
                               style="filter: drop-shadow(0 25px 25px rgba(0,0,0,0.5));">
                           </div>

                           <div class="font-serif font-bold text-5xl md:text-6xl lg:text-7xl tracking-tighter text-stone-100 leading-[0.85]">
                                Ruang <br>
                                <span class="font-normal text-amber-500 italic">Idea-Idea</span><br>
                                Baru.
                          </div>
                       </h1>
                       <p class="text-indigo-100/70 text-lg max-w-md font-medium leading-relaxed">
                            Zon interaktif untuk berkongsi rancangan inovasi, mengundi strategi terbaik, dan menyumbang maklum balas membina.
                      </p>
                      @if(!auth()->check())
                      <a href="{{ route('login') }}?intended={{ urlencode(route('user.pitches')) }}"
                          class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-black uppercase tracking-wider px-5 py-3 rounded-2xl shadow-[0_10px_20px_rgba(245,158,11,0.2)] hover:shadow-[0_12px_25px_rgba(245,158,11,0.3)] transform hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                          💡 Hantar Idea Baru
                      </a>
                      @elseif(!(auth()->user()->is_admin ?? false) && !(auth()->user()->role === 'admin'))
                      <a href="{{ route('user.pitches') }}"
                          class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-black uppercase tracking-wider px-5 py-3 rounded-2xl shadow-[0_10px_20px_rgba(245,158,11,0.2)] hover:shadow-[0_12px_25px_rgba(245,158,11,0.3)] transform hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                          💡 Hantar Idea Baru
                      </a>
                      @endif
                  </div>

                  <div class="space-y-12 text-white">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-8 rounded-[2.5rem] border border-white/10 md:col-span-2 space-y-4" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                                 <div class="flex items-center gap-4 border-b border-white/10 pb-4">
                                      <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center text-white text-lg shadow-md">⚡</div>
                                      <h3 class="text-orange-400 font-black text-lg uppercase tracking-widest">Terma Rujukan</h3>
                                 </div>

                                 <ul class="space-y-3.5">
                                      <li class="flex items-start gap-3 text-sm text-stone-200">
                                          <span class="text-orange-500 font-black text-lg leading-none">•</span>
                                          <div>
                                               <strong class="text-white">Insentif Interaksi Kredit</strong>
                                               Setiap satu klik undian Like yang sah akan menganugerahkan pengguna kredit secara langsung.
                                          </div>
                                      </li>
                                      <li class="flex items-start gap-3 text-sm text-stone-200">
                                           <span class="text-orange-500 font-black text-lg leading-none">•</span>
                                           <div>
                                                <strong class="text-white">Scoreboard Idea</strong>
                                                Idea dengan timbunan undian bulanan paling dominan akan dikunci masuk ke dalam *Carta Utama* untuk dibentang terus ke peringkat atasan agensi.
                                           </div>
                                      </li>
                                </ul>
                           </div>
                      </div>
                 </div>
              </div>
         </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="lg:col-span-4 space-y-12">
               <section class="bg-white rounded-[40px] p-10 shadow-xl shadow-stone-200 border border-stone-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-[#1e1b4b]"></div>
                    <h2 class="text-2xl font-black italic mb-2 tracking-tighter">Cadangan Umum</h2>
                    <p class="text-stone-400 text-[10px] mb-8 font-black uppercase tracking-[0.2em] italic">Saluran maklum balas am portal</p>
                    @guest
                        <div class="bg-[#faf7f2] rounded-2xl p-6 text-center border border-dashed border-stone-200 flex flex-col items-center py-8">
                            <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-stone-800 mb-1">Log Masuk Diperlukan</h4>
                            <p class="text-stone-500 text-xs max-w-xs mb-5 leading-relaxed">Sila log masuk ke akaun anda terlebih dahulu untuk mula berkongsi ulasan.</p>

                            <a href="{{ route('login') }}?intended={{ urlencode(route('pitch')) }}" class="inline-flex items-center gap-2 bg-[#3e2723] hover:bg-orange-700 text-white font-bold text-xs px-6 py-3 rounded-xl transition-all shadow-md shadow-stone-300">
                                <span>Log Masuk Sekarang</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                    @endguest

                    @auth
                    <form wire:submit.prevent="save" class="space-y-4">
                        @if (session()->has('success'))
                          <div class="bg-green-50 border border-green-200 text-green-800 text-xs font-semibold p-4 rounded-2xl mb-4 flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-green-600">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                              </svg>
                              <span>{{ session('success') }}</span>
                          </div>
                        @endif
                        <div class="flex items-center gap-2 bg-[#faf7f2] px-4 py-2.5 rounded-xl border border-stone-100">
                            <div class="w-5 h-5 rounded-full bg-blue-900 flex items-center justify-center text-[10px] text-white font-bold uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="text-xs text-stone-600"><strong class="text-stone-900">{{ auth()->user()->name }}</strong></span>
                        </div>

                        <div>
                            <textarea
                                wire:model="body"
                                rows="4"
                                placeholder="Kongsikan sesuatu..."
                                class="w-full p-4 rounded-2xl bg-[#faf7f2] border-none focus:ring-2 focus:ring-blue-600 outline-none text-sm placeholder:text-stone-400 transition-all">
                            </textarea>

                            @error('body')
                                <span class="text-red-500 text-xs mt-1 block px-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bg-[#faf7f2] p-4 rounded-2xl border border-stone-100 flex flex-col gap-1">
                             <label class="text-xs font-bold text-stone-500 uppercase tracking-wider">Berikan Penilaian:</label>
                             <div class="flex items-center gap-1.5 mt-1">
                                  @for ($i = 1; $i <= 5; $i++)
                                  <button type="button"
                                          wire:click="$set('rating', {{ $i }})"
                                          class="transition-all duration-200 transform hover:scale-125 focus:outline-none">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 class="w-7 h-7 {{ $i <= $rating ? 'fill-amber-400 text-amber-400' : 'fill-none text-stone-300' }} transition-colors"
                                                 stroke="currentColor"
                                                 stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.172-.436.784-.436.956 0l2.22 4.473 4.925.711c.48.069.672.66.326 1.005l-3.567 3.477.842 4.902c.08.47-.417.83-.838.608L12 18.754l-4.418 2.322c-.42.22-.919-.139-.838-.608l.842-4.903-3.567-3.477c-.346-.345-.154-.936.326-1.005l4.925-.711 2.22-4.472Z" />
                                        </svg>
                                    </button>
                                    @endfor

                                    <span class="text-xs font-bold text-stone-600 ml-2">
                                          ({{ $rating }}/5)
                                    </span>
                              </div>
                        </div>

                        <button type="submit" class="w-full bg-[#3e2723] hover:bg-blue-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-stone-300 flex items-center justify-center gap-2 group disabled:opacity-50" wire:loading.attr="disabled">
                            <span wire:loading.remove>Hantar</span>
                            <span wire:loading>Menghantar...</span>

                            <svg wire:loading.remove class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                    </form>

                    @endauth
                </section>

                <section class="px-4 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-stone-800 uppercase tracking-widest">Sembang Am</h3>
                        <div class="h-[1px] flex-1 bg-stone-200 mx-4"></div>
                    </div>

                    @forelse($this->reviews as $review)
                        <div class="bg-white p-6 rounded-[32px] border border-stone-100 shadow-sm hover:shadow-md transition-all group">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-[#faf7f2] flex items-center justify-center text-stone-700 font-black text-xs shadow-inner uppercase">
                                    @php
                                        $words = explode(' ', $review->user->name);
                                        $initials = isset($words[1])
                                            ? substr($words[0], 0, 1) . substr($words[1], 0, 1)
                                            : substr($words[0], 0, 2);
                                    @endphp
                                    {{ $initials }}
                                </div>
                                <div class="flex-1 min-w-0">
                                      <div class="flex items-center gap-2 flex-wrap">
                                           <h4 class="text-sm font-black text-stone-800 uppercase leading-none truncate">
                                                {{ \Illuminate\Support\Str::words($review->user->name, 2, '') }}
                                           </h4>

                                           @auth
                                                @if($review->user_id === auth()->id())
                                                    <button type="button"
                                                             wire:click="delete({{ $review->id }})"
                                                             wire:confirm="Adakah anda pasti mahu memadam ulasan ini?"
                                                             class="text-stone-400 hover:text-red-500 transition-colors focus:outline-none p-0.5 rounded"
                                                             title="Padam Ulasan">
                                                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                               <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.72 0-.34-9m9.96-3.24l-.81 10.63a2.25 2.25 0 0 1-2.24 2.25H8.55a2.25 2.25 0 0 1-2.24-2.25L5.5 5.76M19.5 5.76A10.5 10.5 0 0 0 4.5 5.76M10.5 3.5h3" />
                                                          </svg>
                                                     </button>
                                                  @endif
                                            @endauth
                                        </div>
                                        <span class="text-[9px] text-stone-400 font-bold uppercase tracking-widest block mt-1">
                                              {{ $review->created_at->diffForHumans() }}
                                        </span>
                                </div>

                                <div class="flex gap-0.5 text-orange-500 text-lg leading-none select-none">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $review->rating)
                                            <span class="text-amber-400">★</span>
                                        @else
                                            <span class="text-stone-200">★</span>
                                        @endif
                                    @endfor
                                </div>
                            </div>

                            <p class="text-stone-600 text-sm leading-relaxed italic border-l-2 border-orange-100 pl-4">
                                "{{ $review->body }}"
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-8 bg-[#faf7f2]/50 border border-dashed border-stone-200 rounded-[32px] p-6">
                            <p class="text-stone-400 text-xs font-medium italic">Belum ada ulasan untuk halaman ini. Jadilah yang pertama memberikan maklum balas!</p>
                        </div>
                    @endforelse
                </section>
            </div>

            <div class="lg:col-span-8 space-y-6">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-sm font-black text-stone-800 uppercase tracking-widest">Senarai Idea</h3>
                    <div class="h-[1px] flex-1 bg-stone-200 mx-4"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">
                    @forelse($this->pitches as $pitch)
                    <div class="bg-gradient-to-b from-pink-50/70 via-white to-white p-8 rounded-[40px] transition-all duration-500 border border-pink-200/60 hover:border-orange-400/50 relative group flex flex-col justify-between min-h-[350px] shadow-[0_15px_40px_rgba(244,63,94,0.015)] hover:shadow-[0_20px_45px_rgba(245,158,11,0.08)] transform hover:-translate-y-2">
                         <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-orange-400/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>
                         <div>
                              <div class="flex items-center gap-4 mb-8">
                                  <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-400 via-pink-400 to-rose-500 flex items-center justify-center text-white font-black shadow-md tracking-tighter text-sm transform group-hover:rotate-3 transition-transform duration-300">
                                      @php
                                          $name = $pitch->user->name ?? 'N/A';
                                          $words = explode(' ', trim($name));
                                          $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                                      @endphp
                                      {{ $initials }}
                                  </div>

                                  <div class="space-y-0.5">
                                        <h4 class="text-sm font-black text-stone-800 tracking-tight">
                                              {{ $pitch->user->name ?? 'Unknown' }}
                                        </h4>
                                        <span class="text-[10px] text-rose-600/80 font-sans uppercase tracking-wider block font-bold">
                                              {{ $pitch->user->department->name ?? 'N/A' }}
                                        </span>
                                  </div>
                              </div>

                              <div class="space-y-3">
                                  <h3 class="text-2xl font-serif text-stone-800 leading-snug group-hover:text-rose-600 transition-colors duration-300 font-black">
                                      {{ $pitch->title }}
                                  </h3>

                                  <p class="text-sm text-stone-600 leading-relaxed font-medium line-clamp-3">
                                      {{ $pitch->description }}
                                  </p>
                              </div>
                        </div>

    <!-- TUKAR: border-white/10 -> border-stone-100 -->
    <div class="mt-8 pt-4 border-t border-stone-100 flex items-center justify-between gap-4">
         <!-- TUKAR: text-stone-300 -> text-stone-400 & hover:text-orange-500 -->
         <button type="button"
              wire:click="viewDetails({{ $pitch->id }})"
              class="text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-orange-500 transition-colors focus:outline-none flex items-center gap-1.5 cursor-pointer group/btn">
              Lihat Butiran <span class="inline-block transform group-hover/btn:translate-x-1 transition-transform text-orange-400">&rarr;</span>
         </button>

         <!-- TUKAR: bg-stone-800/60 & border-white/10 -> bg-white & border-pink-200 -->
         <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-2xl border border-pink-200 shadow-2xs transition-all duration-300 group-hover:border-orange-300">
              @if(!auth()->check())
              <button type="button"
                      onclick="window.location.href = '{{ route('login') }}?intended=' + encodeURIComponent(window.location.href);"
                      class="w-7 h-7 inline-flex items-center justify-center rounded-xl transition-all focus:outline-none text-stone-400 hover:text-rose-500"
                      title="Log masuk untuk undi">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                  </svg>
              </button>
              @elseif(auth()->id() !== $pitch->user_id && !(auth()->user()->is_admin ?? false) && !(auth()->user()->role === 'admin'))
              <button type="button"
                      wire:click="vote({{ $pitch->id }})"
                      class="w-7 h-7 inline-flex items-center justify-center rounded-xl transition-all focus:outline-none
                      {{ $pitch->has_voted ? 'scale-110 drop-shadow-[0_4px_10px_rgba(244,63,94,0.35)]' : '' }}"
                      title="{{ $pitch->has_voted ? 'Batal Undi' : 'Suka Idea Ini' }}">
                  @if($pitch->has_voted)
                      <!-- Ikon Solid Full Pink Menyala Bila Dah Diundi -->
                      <svg class="w-4 h-4 text-rose-500 fill-current" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.28 2.25 8.5c0-3.78 2.462-6.5 5.5-6.5 1.76 0 3.35.828 4.25 2.108C12.9 2.828 14.49 2 16.25 2c3.038 0 5.5 2.422 5.5 5.5 0 3.788-2.438 6.86-4.768 10.012a25.18 25.18 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                  @else
                      <!-- Ikon Garisan Kosong Biasa Bila Belum Diundi -->
                      <svg class="w-4 h-4 text-stone-400 hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                  @endif
              </button>
              @else
              <div class="w-7 h-7 inline-flex items-center justify-center text-stone-300"
                   title="{{ auth()->id() === $pitch->user_id ? 'Idea Anda Sendiri' : 'Admin Tidak Boleh Mengundi' }}">
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                      <path d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 5a3 3 0 0 1 6 0v3H9V7zm3 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                  </svg>
              </div>
              @endif

              <!-- TUKAR: text-stone-100 -> text-stone-800 -->
              <span class="text-xs font-mono font-black text-stone-800 pr-0.5">
                   {{ $pitch->votes_count }}<span class="text-[9px] font-sans text-stone-400 font-normal ml-0.5"></span>
              </span>
         </div>
    </div>
</div>
                        @empty
                        <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-[40px] border-2 border-dashed border-stone-200 shadow-sm relative overflow-hidden">
                            <div class="relative z-10 flex flex-col items-center text-center px-6">
                                <div class="w-20 h-20 bg-[#faf7f2] rounded-[30px] flex items-center justify-center shadow-inner mb-4">
                                    <span class="text-4xl grayscale opacity-40">🌱</span>
                                </div>
                                <h3 class="text-xl font-black text-stone-800 uppercase italic tracking-tight">
                                    Tiada Idea <br> <span class="text-amber-500">Dikongsi Lagi</span>
                                </h3>
                                <p class="mt-2 text-xs text-stone-400 font-medium italic max-w-[280px] leading-relaxed">
                                    Belum ada sebarang cadangan strategi atau metodologi pitching dikemukakan buat masa ini.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div x-data="{ open: @entangle('showViewModal') }"
         x-show="open"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
         style="display: none;">

        <div class="fixed inset-0 bg-stone-900/40 backdrop-blur-xs transition-opacity" @click="open = false; $wire.set('showViewModal', false)"></div>

        <div class="relative w-full max-w-2xl bg-white rounded-[40px] shadow-2xl border border-stone-100 p-8 md:p-10 z-50 max-h-[90vh] overflow-y-auto space-y-6">
            @if($viewingPitch)
                <div class="flex items-start justify-between border-b border-stone-100 pb-4">
                    <div>
                        <span class="text-[9px] bg-orange-100 px-2.5 py-1 rounded-full text-orange-700 font-black uppercase tracking-widest">Butiran Cadangan</span>
                        <h2 class="text-2xl font-black text-stone-900 uppercase italic tracking-tight mt-2">
                            {{ $viewingPitch->title }}
                        </h2>
                        <p class="text-xs text-stone-400 mt-1 uppercase font-bold">
                            Oleh: {{ $viewingPitch->user->name ?? 'Ahli Kumpulan' }} ({{ $viewingPitch->user->department->name ?? 'Jabatan Am' }})
                        </p>
                    </div>
                    <button @click="open = false; $wire.set('showViewModal', false)" class="text-stone-400 hover:text-stone-700 transition-colors bg-stone-100 p-2 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs text-stone-600 font-medium bg-pink-50/50 p-3.5 rounded-2xl border border-pink-100/70">
                        <span class="text-base animate-pulse">❤️</span>
                        <div>
                            @php
                                // 1. Dapatkan senarai semua nama pengundi (hanya ambil nama depan untuk kekemasan)
                                $rawVoters = $this->viewingPitch->votes->map(fn($v) => explode(' ', trim($v->user->name ?? 'User'))[0])->filter()->values();
                                $totalVotes = $this->viewingPitch->votes_count ?? $rawVoters->count();

                                // 2. Semak jika anda (current user) ada mengundi
                                $hasMe = auth()->check() && $this->viewingPitch->votes->contains('user_id', auth()->id());

                                // 3. Bina array nama untuk dipaparkan
                                $displayNames = [];

                                if ($hasMe) {
                                    $displayNames[] = 'Anda';
                                    // Buang user semasa daripada senarai raw untuk elak pertindihan
                                    $rawVoters = $this->viewingPitch->votes->filter(fn($v) => $v->user_id !== auth()->id())->map(fn($v) => explode(' ', trim($v->user->name ?? 'User'))[0])->values();
                                }

                                // 4. Masukkan nama orang lain sehingga penuh kuota 3 nama
                                foreach ($rawVoters as $name) {
                                    if (count($displayNames) < 3) {
                                        $displayNames[] = $name;
                                    }
                                }

                                // 5. Kira baki untuk perkataan "yang lain"
                                $displayedCount = count($displayNames);
                                $othersCount = $totalVotes - $displayedCount;

                                // Senarai penuh nama penuh untuk tujuan tooltip box
                                $fullNamesList = $this->viewingPitch->votes->map(fn($v) => $v->user->name ?? 'Unknown')->implode(', ');
                            @endphp

                            @if($totalVotes == 0)
                                <span>Belum ada sesiapa yang menyokong idea ini lagi. Jadilah yang pertama!</span>
                            @else
                                <span>
                                    @if($othersCount > 0)
                                        <!-- Jika ada baki orang lain: Gabung 3 nama pertama dengan koma -->
                                        <strong class="text-stone-800 font-black">{{ implode(', ', $displayNames) }}</strong>
                                        dan <span class="text-rose-600 font-black cursor-help border-b border-dashed border-rose-300" title="{{ $fullNamesList }}">{{ $othersCount }} yang lain</span>
                                    @else
                                        <!-- Jika tiada baki: Papar nama-nama tersebut dengan gaya tatabahasa betul (guna 'dan' di hujung) -->
                                        @if(count($displayNames) == 1)
                                            <strong class="text-stone-800 font-black">{{ $displayNames[0] }}</strong>
                                        @elseif(count($displayNames) == 2)
                                            <strong class="text-stone-800 font-black">{{ $displayNames[0] }}</strong> dan <strong class="text-stone-800 font-black">{{ $displayNames[1] }}</strong>
                                        @else
                                            <strong class="text-stone-800 font-black">{{ $displayNames[0] }}, {{ $displayNames[1] }},</strong> dan <strong class="text-stone-800 font-black">{{ $displayNames[2] }}</strong>
                                        @endif
                                    @endif

                                    menyukai idea ini.
                                </span>
                            @endif
                        </div>
                    </div>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <h4 class="text-xs font-black text-stone-400 uppercase tracking-wider">Penerangan Ringkas:</h4>
                        <p class="text-stone-600 text-sm leading-relaxed bg-stone-50 p-5 rounded-[24px] whitespace-pre-line">
                            {{ $viewingPitch->description }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="text-xs font-black text-stone-400 uppercase tracking-wider">Metodologi & Cara Pelaksanaan:</h4>
                        <p class="text-stone-700 text-sm leading-relaxed italic border-l-4 border-orange-400 pl-4 font-serif bg-orange-50/30 p-5 rounded-r-[24px] whitespace-pre-line">
                            "{{ $viewingPitch->method ?? 'Tiada butiran metodologi disediakan.' }}"
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-stone-100">
                    <button @click="open = false; $wire.set('showViewModal', false)" class="px-6 py-3 bg-stone-900 hover:bg-stone-800 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl transition-all shadow-md">
                        Tutup Paparan
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<x-footer />
