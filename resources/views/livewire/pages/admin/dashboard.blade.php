<?php
// resources/views/livewire/pages/admin/dashboard.blade.php
use App\Models\Submission;
use App\Models\Program;
use App\Model\User;

use function Livewire\Volt\{layout, with};

layout('layouts.app');

with([
    'programs' => fn () => Program::latest()->withCount('submissions')->get(),
]);
?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Panel Pentadbir') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('admin.users') }}">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Jumlah Pengguna</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\User::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Pengguna</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.programs') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Program</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\Program::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Program</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.archives') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9l-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Projek Inovasi</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\Archive::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Projek</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.coffb') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h14v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Coff-B</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\CoffeeBreakSession::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua Sesi</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.publication') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="flex items-center">
                      <div class="p-3 bg-purple-100 rounded-lg text-purple-600">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                              />
                          </svg>
                      </div>
                      <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Penerbitan</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\Publication::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.pitch') }}" class="block transform transition hover:scale-[1.02] active:scale-95">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg text-yellow-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 uppercase font-bold">Idea Inovasi</p>
                            <h3 class="text-2xl font-black">{{ \App\Models\Pitch::count() }}</h3>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-blue-600 font-bold">
                        <span>Lihat Semua</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
       </div>

            <div class="mt-10">
                <h3 class="text-xl font-black text-gray-900 mb-6">Penyertaan Mengikut Program</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($programs as $program)
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:border-blue-500 transition-all border-b-4 border-b-blue-500">
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase rounded-md"></span>
                                <span class="text-[10px] font-bold text-gray-400 italic">{{ $program->status }}</span>
                            </div>
                            <h4 class="font-bold text-gray-900 leading-tight mb-4 h-20 overflow-hidden">
                                {{ $program->title }}
                            </h4>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase tracking-tighter">Jumlah Penyertaan</p>
                                    <h3 class="text-3xl font-black text-blue-600">{{ $program->submissions_count }}</h3>
                                </div>
                                <a href="{{ $program->category_id == 3 ? route('admin.program.quiz-submissions', $program->id) : route('admin.program.submissions', $program->id) }}" class="p-2 bg-gray-900 text-white rounded-xl hover:bg-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 my-5">
                <h3 class="text-lg font-bold mb-4">Aktiviti Terkini</h3>
                <p class="text-gray-600 text-sm italic">Sistem sedia untuk menerima data baru.</p>
            </div>
        </div>
    </div>
</div>
