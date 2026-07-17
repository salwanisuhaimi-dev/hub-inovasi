<?php

use App\Models\Submission;
use App\Models\Review;
use function Livewire\Volt\{layout, state, computed};

layout('layouts.landing');

state([
  'openIndex' => null,
  'page_name' => '',
  'body' => '',
  'rating' => 5,
]);

$programs = computed(function () {
    return \App\Models\Program::whereIn('category_id', [1, 4, 5])
        ->where('deadline', '>=', now())
        ->latest()
        ->get();
});

$activePrograms = computed(function () {
    return \App\Models\Program::whereIn('category_id', [1, 4, 5])
        ->latest()
        ->get();
});


$reviews = computed(function () {
    return Review::query()
        ->with(['user'])
        ->where('page_name', 'entries')
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
        'page_name' => 'entries',
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
<div>

<style>
    .quiz-gradient-header {
        background: linear-gradient(135deg, #1b9a4c 0%, #51bc47 100%);
    }
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(-12deg); }
        50% { transform: translateY(-20px) rotate(-8deg); }
    }
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
</style>
    <div class="min-h-screen bg-[#faf7f2] text-[#4a3728] font-sans overflow-x-hidden">
        <x-top-nav />

        <div class="max-w-7xl mx-auto px-6 py-10">
          <header class="relative overflow-hidden rounded-[40px] p-8 md:p-16 mb-12 shadow-[0_30px_80px_rgba(4,120,87,0.12)] border border-emerald-600/20 bg-gradient-to-br from-[#022c22] via-[#064e3b] to-[#022c22]">
              <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-400/15 rounded-full -mr-40 -mt-40 blur-[120px] pointer-events-none"></div>
              <div class="absolute bottom-0 left-0 w-80 h-80 bg-teal-400/10 rounded-full -ml-24 -mb-24 blur-[100px] pointer-events-none"></div>

              <div class="absolute inset-0 bg-[linear-gradient(to_right,#0596690a_1px,transparent_1px),linear-gradient(to_bottom,#0596690a_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

              <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">

                  <div class="lg:w-1/2 space-y-6 text-center lg:text-left">
                      <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-950/80 border border-emerald-500/30 rounded-full shadow-inner">
                          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                          <span class="text-emerald-300 text-[10px] font-bold uppercase tracking-[0.25em]">{{ __('Hab Transformasi Kreatif') }}</span>
                      </div>

                      <h1 class="text-5xl md:text-6xl lg:text-7xl font-black leading-[1.1] text-white tracking-tight">
                          Penyertaan <br>
                          <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-teal-300 italic font-serif">Pertandingan.</span>
                      </h1>

                      <p class="text-emerald-100/70 text-lg font-medium max-w-xl leading-relaxed">
                          Menerajui perubahan melalui penyampaian idea kreatif dan solusi organisasi yang efektif untuk masa depan jabatan.
                      </p>
                  </div>

                  <div class="lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-5 w-full">

                      <div class="group p-6 rounded-[2.5rem] border border-white/[0.08] bg-white/[0.04] backdrop-blur-xl shadow-xl hover:border-emerald-400/30 hover:bg-white/[0.08] hover:-translate-y-1.5 transition-all duration-500">
                          <div class="w-12 h-12 bg-emerald-500/20 border border-emerald-400/30 rounded-2xl flex items-center justify-center text-emerald-300 mb-5 shadow-inner transform group-hover:rotate-6 transition-transform duration-300">
                              <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                              </svg>
                          </div>
                          <h3 class="text-emerald-300 font-black text-sm mb-1 uppercase tracking-wider">Langkah Mudah</h3>
                          <p class="text-emerald-100/70 text-sm leading-relaxed">Isi borang atas talian dan lampirkan kertas kerja anda dengan mudah.</p>
                      </div>

                      <div class="group p-6 rounded-[2.5rem] border border-white/[0.08] bg-white/[0.04] backdrop-blur-xl shadow-xl hover:border-amber-400/30 hover:bg-white/[0.08] hover:-translate-y-1.5 transition-all duration-500">
                          <div class="w-12 h-12 bg-amber-500/20 border border-amber-400/30 rounded-2xl flex items-center justify-center text-amber-300 mb-5 shadow-inner transform group-hover:-rotate-6 transition-transform duration-300">
                              <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                              </svg>
                          </div>
                          <h3 class="text-amber-300 font-black text-sm mb-1 uppercase tracking-wider">Kriteria Penilaian</h3>
                          <p class="text-emerald-100/70 text-sm leading-relaxed">Fokus utama diberikan kepada impak, keaslian, dan kebolehlaksanaan idea.</p>
                      </div>

                      <div class="p-6 rounded-[2.5rem] border border-emerald-500/20 bg-gradient-to-r from-emerald-950/60 via-emerald-900/20 to-transparent backdrop-blur-xl sm:col-span-2 flex flex-col sm:flex-row items-center sm:items-start gap-6 shadow-xl">
                          <div class="w-14 h-14 bg-emerald-500/10 border border-emerald-500/20 rounded-full flex-shrink-0 flex items-center justify-center text-2xl shadow-inner">
                              🌱
                          </div>
                          <div class="text-center sm:text-left">
                              <h3 class="text-emerald-200 font-black text-sm mb-1.5 uppercase tracking-[0.2em]">Sumbangkan Aspirasi</h3>
                              <p class="text-emerald-100/60 text-sm leading-relaxed">Setiap idea kecil anda adalah pemacu utama kepada transformasi besar dalam jabatan kita.</p>
                          </div>
                      </div>

                  </div>
              </div>
          </header>


            <div class="grid lg:grid-cols-12 gap-12">
                <div class="lg:col-span-4 space-y-12">
                    <section class="bg-[#efebe9] rounded-[32px] p-8 border border-emerald-200">
                        <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <span class="bg-[#1b9a4c] text-white p-1 rounded-md text-xs italic">Akan Datang</span> Pertandingan
                        </h3>
                        <div class="space-y-4">
                            @forelse($this->activePrograms as $program)
                            <div class="group flex items-center gap-4 p-3 hover:bg-white rounded-2xl transition-all cursor-default">
                                <div class="bg-white group-hover:bg-green-600 group-hover:text-white w-12 h-12 rounded-xl flex flex-col items-center justify-center shadow-sm transition-colors">
                                    <span class="text-[10px] font-bold">
                                    {{ \Carbon\Carbon::parse($program->start_date)->format('M') }}
                                    </span>
                                    <span class="text-lg font-black leading-none text-green-600 group-hover:text-white">
                                    {{ \Carbon\Carbon::parse($program->start_date)->format('d') }}
                                    </span>
                                </div>
                                <div>
                                <h4 class="text-sm font-bold text-emerald-800">{{ Str::limit($program->title, 30, '...') }}</h4>
                                <p class="text-[10px] text-emerald-500 uppercase">{{\Carbon\Carbon::parse($program->start_time)->format('h:i A')}} • {{$program->location}}</p>
                                </div>
                            </div>
                            @empty
                            <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-emerald-300 rounded-[24px] opacity-60">
                                <svg class="w-10 h-10 text-emerald-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.5 8V18a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V8M15 11v-4a3 3 0 0 0-6 0v4M2 8h20" />
                                </svg>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 italic">Tiada Pertandingan Dijadualkan</p>
                                <p class="text-[9px] text-emerald-400 mt-1 uppercase">Sila semak semula kemudian</p>
                            </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="bg-white rounded-[40px] p-10 shadow-xl shadow-emerald-200 border border-emerald-100 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-2 h-full bg-[#1b9a4c]"></div>
                        <h2 class="text-2xl font-black italic mb-2 tracking-tighter">Leave a review</h2>
                        <p class="text-emerald-400 text-[10px] mb-8 font-black uppercase tracking-[0.2em] italic">Maklum balas anda dihargai</p>

                        @guest
                            <div class="bg-[#faf7f2] rounded-2xl p-6 text-center border border-dashed border-emerald-200 flex flex-col items-center py-8">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-emerald-800 mb-1">Log Masuk Diperlukan</h4>
                                <p class="text-emerald-500 text-xs max-w-xs mb-5 leading-relaxed">Sila log masuk ke akaun anda terlebih dahulu untuk mula berkongsi ulasan atau pengalaman.</p>

                                <a href="{{ route('login') }}?intended={{ urlencode(route('entries')) }}" class="inline-flex items-center gap-2 bg-[#3e2723] hover:bg-green-700 text-white font-bold text-xs px-6 py-3 rounded-xl transition-all shadow-md shadow-emerald-300">
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
                                    <div class="w-5 h-5 rounded-full bg-green-600 flex items-center justify-center text-[10px] text-white font-bold uppercase">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs text-stone-600"><strong class="text-stone-900">{{ auth()->user()->name }}</strong></span>
                                </div>

                                <div>
                                    <textarea
                                        wire:model="body"
                                        rows="4"
                                        placeholder="Kongsikan sesuatu..."
                                        class="w-full p-4 rounded-2xl bg-[#faf7f2] border-none focus:ring-2 focus:ring-green-600 outline-none text-sm placeholder:text-stone-400 transition-all">
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

                                <button type="submit" class="w-full bg-[#3e2723] hover:bg-green-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-stone-300 flex items-center justify-center gap-2 group disabled:opacity-50" wire:loading.attr="disabled">
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
                            <h3 class="text-sm font-black text-emerald-800 uppercase tracking-widest">Komen</h3>
                            <div class="h-[1px] flex-1 bg-emerald-200 mx-4"></div>
                        </div>
                        @forelse($this->reviews as $review)
                        <div class="bg-white/60 p-6 rounded-[30px] border border-emerald-100 relative">
                            <div class="flex gap-4 items-center mb-3">
                                <div class="w-8 h-8 rounded-lg bg-green-100 text-black-700 flex items-center justify-center font-black text-[10px]">
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
                            <p class="text-xs text-emerald-500 leading-relaxed italic">"{{ $review->body }}"</p>
                        </div>
                        @empty
                        <div class="text-center py-8 bg-[#faf7f2]/50 border border-dashed border-emerald-200 rounded-[32px] p-6">
                            <p class="text-emerald-400 text-xs font-medium italic">Belum ada ulasan untuk halaman ini. Jadilah yang pertama memberikan maklum balas!</p>
                        </div>

                        @endforelse
                    </section>
                </div>

                <div class="lg:col-span-8 space-y-16">
                    <div class="grid grid-cols-1 gap-8 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-emerald-800 uppercase tracking-widest">Senarai Pertandingan</h3>
                            <div class="h-[1px] flex-1 bg-emerald-200 mx-4"></div>
                        </div>
                        <div class="flex flex-col gap-4 w-full">
                        @forelse($this->programs as $program)

                        <div class="group bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-500 flex flex-col md:flex-row items-center overflow-hidden w-full max-w-4xl mx-auto min-h-[140px] p-4 md:p-5 gap-6">
                            <div class="w-[90px] h-[120px] bg-sky-50 rounded-xl border border-gray-50 flex items-center justify-center overflow-hidden flex-shrink-0 p-1.5 relative shadow-inner">
                                @if($program->image_path)
                                    <img src="{{ asset('storage/' . $program->image_path) }}" alt="{{ $program->title }}" class="w-full h-full object-contain block transform group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <span class="text-[8px] font-black text-gray-300 uppercase tracking-wider text-center">Tiada Imej</span>
                                @endif
                            </div>
                            <div class="flex-1 w-full space-y-2 text-center md:text-left">
                                <div class="flex flex-col md:flex-row md:items-center gap-2">
                                    <!--<span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[9px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider border border-emerald-100/60 w-fit mx-auto md:mx-0">
                                        <span class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></span>
                                        Terbuka
                                    </span>-->

                                    <h4 class="text-base font-extrabold text-gray-950 tracking-tight" title="{{ $program->title }}">
                                        {{ $program->title }}
                                    </h4>
                                </div>

                                <p class="text-xs text-gray-500 font-medium max-w-xl line-clamp-3">
                                    {{ $program->description }}
                                </p>

                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-1 text-[11px] text-gray-500 font-semibold pt-1">
                                    <!--<div class="flex items-center gap-1">
                                        <span class="text-gray-400 font-medium">Yuran:</span>
                                        <span class="text-gray-900 font-bold">RM50 / Pasukan</span>
                                    </div>
                                    <div class="hidden md:block text-gray-300">•</div>-->
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-400 font-medium">Tarikh Tutup:</span>
                                        <span class="text-red-500 font-bold">{{ \Carbon\Carbon::parse($program->deadline)->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row md:flex-col items-center md:items-end gap-3 w-full md:w-auto border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6 flex-shrink-0">
                                @if(\Carbon\Carbon::parse($program->start_date)->isFuture())
                                <div class="inline-flex items-center gap-1 bg-red-50 text-red-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-red-100 shadow-sm">
                                    <span class="w-1 h-1 bg-red-500 rounded-full animate-pulse"></span>
                                    {{ round(now()->diffInDays(\Carbon\Carbon::parse($program->start_date))) }} hari lagi
                                </div>
                                @endif

                                <div class="flex flex-col gap-2 w-full sm:w-auto"> @if($program->form_publication_id && $program->formPublication)
                                        @php
                                            // Memandangkan formPublication menggunakan table publications yang sama,
                                            // kita ambil paths fail borang tersebut (anda guna `pdf_paths` sebelum ini)
                                            $formFiles = $program->formPublication->pdf_paths ?? [];
                                        @endphp

                                        @foreach($formFiles as $formIndex => $formPath)
                                            <a href="{{ asset('storage/' . $formPath) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-[10px] font-black text-amber-600 uppercase tracking-widest hover:text-amber-800 transition-colors px-2 py-1 group/form-link">

                                                <span>Muat Turun Borang {{ count($formFiles) > 1 ? ($formIndex + 1) : '' }}</span>

                                                <svg class="w-3 h-3 transition-transform group-hover/form-link:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        @endforeach
                                    @endif

                                    @php
                                        $files = $program->publication->pdf_paths ?? [];
                                    @endphp

                                    @if(count($files) > 0)
                                        @foreach($files as $index => $path)
                                            <a href="{{ asset('storage/' . $path) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-blue-900 transition-colors px-2 py-1 group/link">

                                                <span>Garis Panduan {{ count($files) > 1 ? ($index + 1) : '' }}</span>

                                                <svg class="w-3 h-3 transition-transform group-hover/link:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        @endforeach
                                    @else
                                        <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest px-2 py-1 cursor-not-allowed">
                                             Tiada Garis Panduan
                                        </span>
                                    @endif

                                    <div class="mt-2">
                                        @php
                                            $hasApplied = false;
                                            if (auth()->check()) {
                                                $hasApplied = auth()->user()->submissions()
                                                    ->where('program_id', $program->id)
                                                    ->exists();
                                            }
                                        @endphp

                                        @if(!auth()->check())
                                            <a href="{{ route('login') }}?intended={{ urlencode(url()->current()) }}"
                                                class="bg-blue-900 hover:bg-blue-950 text-white font-extrabold text-[10px] uppercase tracking-widest py-2 px-5 rounded-xl text-center transition-all duration-300 transform active:scale-95 whitespace-nowrap shadow-sm block w-full sm:w-auto">
                                                Sertai
                                            </a>
                                        @elseif($hasApplied)
                                            <button type="button" disabled
                                                class="bg-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-widest py-2 px-4 rounded-xl text-center whitespace-nowrap cursor-not-allowed border border-gray-200 flex items-center justify-center gap-1 w-full sm:w-auto">
                                                Telah Memohon
                                            </button>
                                        @else
                                            <a href="{{ route('project.submit', $program->id) }}"
                                                class="bg-blue-900 hover:bg-blue-950 text-white font-extrabold text-[10px] uppercase tracking-widest py-2 px-5 rounded-xl text-center transition-all duration-300 transform active:scale-95 whitespace-nowrap shadow-sm block w-full sm:w-auto">
                                                Sertai
                                            </a>
                                        @endif
                                      </div>
                                </div>
                            </div>
                        </div>

                        @empty
                        </div>
                        <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white rounded-3xl border-2 border-dashed border-emerald-100 relative overflow-hidden group">
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-emerald-50 rounded-full blur-[80px] opacity-50 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative z-10 flex flex-col items-center text-center">
                                <div class="w-24 h-24 bg-emerald-50 rounded-2xl flex items-center justify-center shadow-inner mb-6 transition-transform duration-500 group-hover:scale-110">
                                    <svg class="w-12 h-12 text-emerald-600/40 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>

                                <h3 class="text-xl font-bold text-emerald-900 tracking-tight leading-tight">
                                    Tiada Program Aktif <br>
                                    <span class="text-emerald-600 font-medium">Buat Masa Ini</span>
                                </h3>

                                <p class="mt-3 text-sm text-gray-500 max-w-[320px] leading-relaxed">
                                    Terima kasih atas minat anda. Sila semak semula dalam masa terdekat untuk peluang penyertaan baru.
                                </p>

                                <button wire:click="$refresh" class="mt-8 px-10 py-3 bg-white border border-emerald-200 rounded-xl text-xs font-bold uppercase tracking-widest text-emerald-700 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-sm transition-all duration-300">
                                    Semula ↻
                                </button>
                            </div>
                            <div class="absolute top-10 left-10 text-emerald-100 opacity-50 animate-bounce">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div class="absolute bottom-10 right-10 text-emerald-100 opacity-50 animate-pulse">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-footer />

</div>
