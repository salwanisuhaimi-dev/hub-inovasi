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
    'currentTab' => 'pop'
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
    'quizzes' => Quiz::where('quiz_type', $this->currentTab)
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
    ]);

    $payload = [
        'question' => $this->question,
        'option_a' => $this->option_a,
        'option_b' => $this->option_b,
        'option_c' => $this->option_c,
        'option_d' => $this->option_d,
        'correct_answer' => $this->correct_answer,
        'extras' => $this->extras,
    ];

    if ($this->editing) {
        Quiz::find($this->editing)->update($payload);
        session()->flash('message', 'Kuiz berjaya dikemaskini!');
    } else {
        // Simpan data baru
        Quiz::create($payload);
        session()->flash('message', 'Kuiz berjaya disimpan!');
    }

    $this->reset();
    $this->showModal = false;
    session()->flash('message', 'Kuiz berjaya disimpan!');
};

$openCreateModal = function() {
    $this->reset(['editing', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'extras']);
    $this->showModal = true;
}

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

    <button wire:click="openCreateModal" class="flex items-center px-5 py-2.5 my-5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
         <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
         </svg>
        Tambah Kuiz
    </button>

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
                                <a href="" class="p-2.5 bg-slate-50 hover:bg-amber-600 rounded-xl transition-all text-slate-400 hover:text-white group/btn relative">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <span class="absolute -top-10 left-1/2 -translate-x-1/2 scale-0 group-hover/btn:scale-100 transition-all bg-slate-900 text-white text-[9px] font-black px-2 py-1.5 rounded-lg shadow-xl uppercase whitespace-nowrap z-30">Urus Soalan</span>
                                </a>

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

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            @include('livewire.pages.admin.partials.quiz-table')
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6">
                        {{ $editing ? 'Kemaskini Kuiz' : 'Tambah Kuiz Baru' }}
                    </h3>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Soalan</label>
                            <textarea wire:model="question" rows="5"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                            </textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan A</label>
                            <input type="text" wire:model="option_a" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan B</label>
                            <input type="text" wire:model="option_b" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan C</label>
                            <input type="text" wire:model="option_c" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilihan Jawapan D</label>
                            <input type="text" wire:model="option_d" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Jawapan</label>
                            <input type="text" wire:model="correct_answer" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Fakta Menarik</label>
                             <textarea wire:model="extras" rows="5"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                            </textarea>

                        </div>
                        <div class="pt-4 flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                                {{ $editing ? 'Simpan Perubahan' : 'Simpan Kuiz' }}                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
