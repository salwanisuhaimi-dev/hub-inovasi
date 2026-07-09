<?php

use App\Models\Quiz;
use App\Models\Program;
use function Livewire\Volt\{layout, state, with, usesFileUploads};

layout('layouts.app');
usesFileUploads();

state([
    'showModal' => false,
    'editing' => null,
    'question' => '',
    'option_a' => '',
    'option_b' => '',
    'option_c' => '',
    'option_d' => '',
    'correct_answer' => '',
    'extras' => '',
    'currentTab' => 'pop',
    'isQuizModalOpen' => false,
    'quiz_type' => 'pop',
    'program_id' => null,
]);

$edit = function (Quiz $quiz) {
    $this->editing = $quiz->id;
    $this->question = $quiz->question;

    $this->option_a = $quiz->option_a;
    $this->option_b = $quiz->option_b;
    $this->option_c = $quiz->option_c;
    $this->option_d = $quiz->option_d;
    $this->correct_answer = $quiz->correct_answer;
    $this->extras = $quiz->extras;

    $this->showModal = true;
};

with(fn () => [
  'quizzes' => Quiz::query()
                       ->when($this->currentTab === 'pop', function ($query) {
                           $query->where('quiz_type', 'pop');
                       })
                       ->when($this->currentTab === 'program', function ($query) {
                           $query->where('quiz_type', 'program')
                                 ->where('program_id', $this->program_id);
                       })
                       ->latest()
                       ->get(),
    'programs' => Program::where('category_id', 3)
                      ->latest()
                      ->get(),
]);

$save = function () {
    $data = $this->validate([
        'question' => 'required',
        'option_a' => 'required',
        'option_b' => 'required',
        'option_c' => 'required',
        'option_d' => 'required',
        'correct_answer' => 'required',
        'extras' => 'required',

        'quiz_type'      => 'required|in:pop,program',
        'program_id'     => 'nullable|integer',
    ]);

    $payload = [
        'question' => $this->question,
        'option_a' => $this->option_a,
        'option_b' => $this->option_b,
        'option_c' => $this->option_c,
        'option_d' => $this->option_d,
        'correct_answer' => $this->correct_answer,
        'extras' => $this->extras,
        'quiz_type' => $this->quiz_type,
        'program_id' => $this->program_id,
    ];

    if ($this->editing) {
        Quiz::find($this->editing)->update($payload);
        session()->flash('message', 'Kuiz berjaya dikemaskini!');
    } else {
        Quiz::create($payload);
        session()->flash('message', 'Kuiz berjaya disimpan!');
    }

    $savedTab = $this->currentTab;
    $savedProgramId = $this->program_id;

    $this->reset();

    $this->currentTab = $savedTab;
    $this->quiz_type = $savedTab; 
    $this->program_id = $savedProgramId;
    $this->showModal = false;
    session()->flash('message', 'Kuiz berjaya disimpan!');
};

$openCreateModal = function($programId = null) {
    $this->reset(['editing', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'extras']);

    if ($programId) {
        $this->quiz_type = 'program';
        $this->program_id = $programId;
    } else {
        $this->quiz_type = 'pop';
        $this->program_id = null;
    }
    $this->showModal = true;
};

$openQuizModal = function ($programId) {
    $this->program_id = $programId;
    $this->currentTab = 'program';
    $this->quiz_type = 'program';
    $this->isQuizModalOpen = true;
};

$closeQuizModal = function () {
    $this->currentTab = 'program';
    $this->quiz_type = 'program';
    $this->isQuizModalOpen = false;
};

?>

<div class="p-6">
     <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Senarai Kuiz</h2>
            <p class="text-sm text-gray-500">Urus dan pantau semua kuiz anda di sini.</p>
        </div>
     </div>

     <div class="mb-6 border-b border-gray-200">
          <div class="flex space-x-8">
              <button
                  wire:click="$set('currentTab', 'pop')"
                  class="pb-4 text-sm font-bold transition-all border-b-2 {{ $currentTab === 'pop' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                  Kuiz Kilat
              </button>

              <button
                  wire:click="$set('currentTab', 'program')"
                  class="pb-4 text-sm font-bold transition-all border-b-2 {{ $currentTab === 'program' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                  Program
              </button>
          </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 font-bold">
            {{ session('message') }}
        </div>
    @endif

    @if($currentTab === 'pop')

    <button wire:click="openCreateModal" class="flex items-center px-5 py-2.5 my-5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
         <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
         </svg>
        Tambah Kuiz
    </button>

    @endif

    @if($currentTab === 'program')
    <div class="mb-4 p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 text-sm font-medium">
        ℹ️ Anda sedang melihat senarai kuiz di bawah kategori <strong>Other Program</strong>.
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Program</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Tarikh</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Masa</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 bg-white">
                @forelse($programs as $program)
                    <tr class="hover:bg-blue-50/30 transition-all duration-200 group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $program->title }}</div>
                            <div class="text-xs text-gray-500 truncate w-48">{{ $program->description }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 font-medium">
                                {{ $program->start_date ? \Carbon\Carbon::parse($program->start_date)->format('d M Y') : 'Tiada Tarikh Mula' }}
                            </div>
                            <div class="text-xs text-gray-400">
                                @if($program->start_date && $program->end_date)
                                    {{ \Carbon\Carbon::parse($program->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($program->end_date)->format('d M Y') }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 mt-1 flex items-center">
                                @if($program->start_time)
                                    {{ \Carbon\Carbon::parse($program->start_time)->format('h:i A') }}
                                    @if($program->end_time)
                                        - {{ \Carbon\Carbon::parse($program->end_time)->format('h:i A') }}
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button"
                                      wire:click="openQuizModal({{ $program->id }})"
                                      class="p-2.5 bg-slate-50 hover:bg-amber-600 rounded-xl transition-all text-slate-400 hover:text-white group/btn relative">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                     </svg>
                                     <span class="absolute -top-10 left-1/2 -translate-x-1/2 scale-0 group-hover/btn:scale-100 transition-all bg-slate-900 text-white text-[9px] font-black px-2 py-1.5 rounded-lg shadow-xl uppercase whitespace-nowrap z-30">
                                          Urus Soalan
                                     </span>
                                </button>
                                <button wire:click=""
                                    class="p-2.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all active:scale-90"
                                    title="Edit Soalan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <button wire:click=""
                                    wire:confirm=""
                                    class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all active:scale-90"
                                    title="Padam Soalan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.364-6.364l-.707-.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M12 21V3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </div>
                                <span class="text-gray-400 font-medium">Tiada program ditemui.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @endif

    @if($currentTab === 'pop' && !$isQuizModalOpen)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            @include('livewire.pages.admin.partials.quiz-table')
        </table>
    </div>
    @endif

    @if($isQuizModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm animate-fade-in">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden w-full max-w-6xl max-h-[85vh] flex flex-col">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Senarai Kuiz: Other Program</h3>
                    <p class="text-xs text-gray-500">Uruskan senarai soalan kuiz bagi modul program di sini.</p>
                </div>
                <button wire:click="closeQuizModal" class="text-gray-400 hover:text-gray-600 p-2 bg-white rounded-xl border border-gray-200 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex justify-end w-full">
                <button wire:click="openCreateModal({{ $program_id }})" class="flex items-center px-5 py-2.5 my-5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Kuiz
                </button>
            </div>

            <div class="overflow-y-auto p-6 flex-1">
                <table class="w-full text-left border-collapse">
                    @include('livewire.pages.admin.partials.quiz-table')
                </table>
            </div>

        </div>
    </div>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">

                <div class="absolute inset-0 bg-gray-600/30 backdrop-blur-sm transition-opacity duration-300 opacity-100"
                     wire:click="$set('showModal', false)"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md transform transition-transform duration-300 ease-in-out bg-white shadow-2xl flex flex-col translate-x-0">

                        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-black text-gray-900" id="slide-over-title">
                                    {{ $editing ? '⚡ Kemaskini Kuiz' : ' Tambah Kuiz Baru' }}
                                </h2>
                                <p class="text-xs text-gray-500 mt-0.5">Sila isi maklumat soalan kuiz di bawah.</p>
                            </div>
                            <button type="button" wire:click="$set('showModal', false)" class="rounded-xl border border-gray-200 bg-white p-2 text-gray-400 hover:text-gray-600 shadow-sm transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6">
                            <form id="drawer-form" wire:submit.prevent="save" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Soalan</label>
                                    <textarea wire:model="question" rows="4"
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 text-sm font-medium"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan A</label>
                                    <input type="text" wire:model="option_a" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan B</label>
                                    <input type="text" wire:model="option_b" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan C</label>
                                    <input type="text" wire:model="option_c" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan D</label>
                                    <input type="text" wire:model="option_d" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Jawapan Betul</label>
                                    <input type="text" wire:model="correct_answer" placeholder="Contoh: A" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium uppercase">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Fakta Menarik</label>
                                    <textarea wire:model="extras" rows="4"
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 text-sm font-medium"></textarea>
                                </div>
                            </form>
                        </div>

                        <div class="border-t border-gray-100 px-6 py-4 bg-gray-50 flex gap-3">
                            <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition text-sm">
                                Batal
                            </button>
                            <button type="submit" form="drawer-form" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 text-sm">
                                {{ $editing ? 'Simpan Perubahan' : 'Simpan Kuiz' }}
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    @endif
  </div>
