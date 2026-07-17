<?php
// resources/views/livewire/pages/user/dashboard.blade.php
use App\Models\Program;
use App\Models\Submission;
use App\Models\CoffeeBreakSession;
use App\Models\Pitch;
use function Livewire\Volt\{layout, state, with};

layout('layouts.app');

state([

]);

with([
   'programs' => fn() => Program::latest()
      ->where('deadline', '>=', now())
      ->withExists(['submissions as has_submitted' => function($query) {
          $query->where('user_id', auth()->id());
      }])
      ->get(),
    'penyertaanSaya' => fn() => Submission::where('user_id', auth()->id())->count(),
    'myCoffB' => fn() => CoffeeBreakSession::where('created_by', auth()->id())->count(),
    'myPitches' => fn() => Pitch::where('user_id', auth()->id())->count(),
])
?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Panel Pengguna') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(empty(auth()->user()->department_id) || empty(auth()->user()->telephone_num) || empty(auth()->user()->position))
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 p-5 mb-6 shadow-sm sm:rounded-2xl">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 bg-amber-100 p-3 rounded-xl">
                            <svg class="h-6 w-6 text-amber-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-amber-900 font-bold text-sm uppercase tracking-tight">Profil Belum Lengkap</h4>
                            <p class="text-amber-700 text-xs mt-1">Sila kemaskini maklumat jawatan, bahagian dan telefon.</p>
                        </div>
                    </div>

                    <a href="{{ route('profile') }}"
                        class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-[0.15em] hover:bg-amber-700 active:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-amber-200">
                            Lengkapkan Sekarang
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-bold">Selamat Datang, {{ auth()->user()->name }}!</h3>
                <p class="text-sm text-gray-600">Sumbang idea dan sertai pertandingan dalam Hab Inovasi.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('user.submissions') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Penyertaan Saya</p>
                            <h3 class="text-2xl font-black">{{ $penyertaanSaya }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Penyertaan</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.coffb') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h14v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Coff-B</p>
                            <h3 class="text-2xl font-black">{{ $myCoffB }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Sesi</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.pitches') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                      <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 0A3.75 3.75 0 0 0 12 18Z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 22h.008v.008H12V22ZM12 18v2M9.5 20h5" />
                          </svg>
                      </div>
                      <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Idea Inovasi</p>
                            <h3 class="text-2xl font-black">{{ $myPitches }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Idea</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 uppercase font-bold">Status Akaun</p>
                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Aktif</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center transform hover:scale-[1.02] transition-transform duration-300">
                <!-- Ikon Dompet Comel dengan Kesan Glow -->
                <div class="p-3.5 bg-emerald-50 rounded-xl text-emerald-500 shadow-inner group-hover:animate-bounce">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 1.66-4.03 3-9 3s-9-1.34-9-3M3 12V6c0-1.66 4.03-3 9-3s9 1.34 9 3v6M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3m0 0v6c0 1.66-4.03 3-9 3s-9-1.34-9-3v-6" />
                    </svg>
                </div>

                <!-- Informasi Kredit -->
                <div class="ml-4 flex flex-col gap-1">
                    <p class="text-xs text-gray-400 uppercase font-black tracking-wider">Kredit Saya</p>
                    <div class="flex items-center gap-2">
                        <!-- Badge Nilai Kredit yang Bulat & Comel -->
                        <span class="px-3 py-1 text-sm font-extrabold bg-emerald-100 text-emerald-700 rounded-full border border-emerald-200 shadow-sm">
                            💰 {{ auth()->user()->credits }} Token
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-xl font-black text-gray-900 mb-6">Program Inovasi Untuk Disertai</h3>

            @if($programs->isEmpty())
                <div class="bg-gray-50 rounded-3xl p-10 text-center border-2 border-dashed border-gray-200">
                    <p class="text-gray-500 font-medium">Tiada program baru ditawarkan buat masa ini.</p>
                </div>
            @else

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($programs as $program)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 hover:shadow-md transition-all flex flex-col h-full">
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-[10px] font-black uppercase rounded-full">
                            {{ $program->prize }}
                        </span>
                    </div>

                <div class="flex-grow">
                    <h4 class="font-bold text-gray-900 leading-tight mb-2 text-lg">
                        {{ $program->title }}
                    </h4>

                    <div class="text-xs text-gray-500 flex items-center mb-6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Tamat: {{ \Carbon\Carbon::parse($program->deadline)->format('d/m/Y') }}
                    </div>
                </div>

                <div class="mt-auto pt-4">
                  @if($program->has_submitted)
                      <button type="button" disabled
                          class="block w-full text-center py-4 bg-emerald-100 text-emerald-700 rounded-2xl font-black text-sm cursor-not-allowed flex items-center justify-center gap-2">
                          Telah Memohon
                      </button>
                  @else
                      <a href="{{ route('project.submit', $program->id) }}"
                          class="block w-full text-center py-4 bg-blue-600 text-white rounded-2xl font-black text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                          Sertai Sekarang
                      </a>
                  @endif
                </div>
            </div>
                @endforeach
        </div>
            @endif
    </div>
</div>
