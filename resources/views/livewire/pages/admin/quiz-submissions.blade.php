<?php

use App\Models\Program;
use App\Models\QuizSubmission;
use function Livewire\Volt\{layout, with, state};

layout('layouts.app');

state([
    'program' => fn (Program $program) => $program,
    'selectedSubmission' => null,
    'showModal' => false,
]);

with([
    'quiz_submissions' => fn() => QuizSubmission::where('program_id', $this->program->id)
        ->with(['user', 'department'])
        ->orderBy('score', 'desc')
        ->orderBy('time_taken', 'asc')
        ->get()
]);



?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 mt-2">{{ $program->title }}</h2>
                <p class="text-gray-500 font-medium">Senarai penyertaan bagi program ini.</p>
            </div>
            <div class="bg-blue-600 px-6 py-3 rounded-2xl text-white shadow-lg shadow-blue-100 text-center">
                <p class="text-[10px] font-bold uppercase opacity-80 tracking-tighter">Jumlah Penyertaan</p>
                <p class="text-2xl font-black">{{ $quiz_submissions->count() }}</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2.5rem] border border-gray-100">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                  <thead class="bg-gray-50/50">
                      <tr>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400">Peserta</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400">Bahagian</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400 text-center">Jumlah Soalan</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400 text-center">Betul</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400 text-center">Markah</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400 text-center">Masa Diambil</th>
                          <th class="px-6 py-5 text-[10px] font-black uppercase text-gray-400 text-right">Tindakan</th>
                      </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                      @forelse($quiz_submissions as $sub)
                          @php
                              // Semak jika peserta berada di kedudukan 3 teratas
                              $isTop3 = $loop->index < 3;

                              // Set warna border/background khas mengikut ranking
                              $rowClass = '';
                              $badge = '';

                              if ($loop->index === 0) {
                                  $rowClass = 'bg-amber-50/60 hover:bg-amber-50 border-l-4 border-amber-500'; // Tempat Pertama (Emas)
                                  $badge = '🥇 ';
                              } elseif ($loop->index === 1) {
                                  $rowClass = 'bg-slate-50/80 hover:bg-slate-100 border-l-4 border-slate-400';  // Tempat Kedua (Perak)
                                  $badge = '🥈';
                              } elseif ($loop->index === 2) {
                                  $rowClass = 'bg-orange-50/60 hover:bg-orange-100 border-l-4 border-orange-400'; // Tempat Ketiga (Gangsa)
                                  $badge = '🥉';
                              } else {
                                  $rowClass = 'hover:bg-gray-50/50'; // Peserta biasa
                              }
                          @endphp

                          <tr class="{{ $rowClass }} transition-colors">
                              <td class="px-6 py-6">
                                  <div class="flex items-center gap-3">
                                    @if($badge)
                                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-white shadow-sm border border-gray-100 text-gray-700">
                                            {{ $badge }}
                                        </span>
                                    @endif
                                      <div class="h-10 w-10 rounded-full {{ $isTop3 ? 'bg-white shadow-sm ring-1 ring-black/5' : 'bg-blue-100' }} flex items-center justify-center text-blue-700 font-bold text-xs uppercase">
                                          {{ substr($sub->user->name, 0, 2) }}
                                      </div>
                                      <div>
                                          <div class="flex items-center gap-2">
                                              <p class="font-bold text-gray-900 leading-none">{{ $sub->user->name }}</p>

                                          </div>
                                          <p class="text-xs text-gray-500 mt-1 uppercase font-black tracking-tighter text-blue-600">{{ $sub->group_name }}</p>
                                      </div>
                                  </div>
                              </td>

                              <td class="px-6 py-6">
                                  <p class="text-xs text-gray-600 font-medium">{{ $sub->user->department->code ?? '-' }}</p>
                              </td>

                              <td class="px-6 py-6 text-center">
                                  <p class="font-bold text-gray-900">{{ $sub->total_questions }}</p>
                              </td>

                              <td class="px-6 py-6 text-center">
                                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                      {{ $sub->correct_answers }}
                                  </span>
                              </td>

                              <td class="px-6 py-6 text-center">
                                  <p class="font-black {{ $isTop3 ? 'text-amber-600 text-base' : 'text-blue-600 text-sm' }}">{{ $sub->score }}%</p>
                              </td>

                              <td class="px-6 py-6 text-center text-xs text-gray-500 font-mono">
                                  {{ $sub->time_taken }}
                              </td>

                              <td class="px-6 py-6 text-right">
                                  <div class="flex justify-end gap-2">
                                      <button wire:click="viewDetails({{ $sub->id }})"
                                          class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                          </svg>
                                      </button>
                                  </div>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="7" class="px-6 py-20 text-center">
                                  <p class="text-gray-400 font-bold uppercase tracking-widest">Tiada penyertaan masuk lagi</p>
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-data="{ open: @entangle('showModal') }"
     x-show="open"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" x-on:click="open = false"></div>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[2.5rem] shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-10">

            @if($selectedSubmission)
                <div>
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">
                                {{ $selectedSubmission->status }}
                            </span>
                            <h3 class="text-2xl font-black text-gray-900 mt-2">{{ $selectedSubmission->project_title }}</h3>
                            <p class="text-sm text-blue-600 font-bold uppercase tracking-tight">{{ $selectedSubmission->group_name }}</p>
                        </div>
                        <button x-on:click="open = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 rounded-2xl flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600 uppercase">
                                {{ substr($selectedSubmission->user->name, 0, 2) }}
                            </div>
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Dihantar Oleh</p>
                            <p class="font-bold text-gray-900">{{ $selectedSubmission->user->name }} ({{ $selectedSubmission->user->email }})</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 italic">Penerangan Projek</h4>
                        <div class="text-gray-700 leading-relaxed bg-blue-50/30 p-6 rounded-3xl border border-blue-50">
                            {{ $selectedSubmission->project_description }}
                        </div>
                    </div>

                    @if($selectedSubmission->file_path)
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-600 italic">Dokumen Sokongan Terlampir</span>
                        <a href="{{ Storage::url($selectedSubmission->file_path) }}" target="_blank"
                           class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-blue-600 transition-all">
                           Lihat Fail (PDF/ZIP)
                        </a>
                    </div>
                    @endif
                </div>

                <div class="mt-10 flex gap-3">
                    <button x-on:click="open = false" class="flex-1 py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold text-sm">Tutup</button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
