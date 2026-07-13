<?php

use App\Models\Pitch;
use function Livewire\Volt\{layout, state, with, usesFileUploads};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

layout('layouts.app');
usesFileUploads();

state([
    'showModal' => false,
    'editing' => null,
    'title' => '',
    'description' => '',
    'method' => '',
    'showViewModal' => false,
    'selectedIdea' => null,
]);

with([
    'pitches' => fn() => Pitch::latest()->get(),
]);

$delete = function ($id) {
    $pitch = Pitch::findOrFail($id);

    $pitch->delete();
    session()->flash('message', 'Idea berjaya dipadam!');
};

$viewIdea = function ($id) {
    $this->selectedIdea = Pitch::find($id);
    $this->showViewModal = true;
};

$closeViewModal = function () {
    $this->showViewModal = false;
    $this->selectedIdea = null;
};


?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Senarai Idea</h2>
            <p class="text-sm text-gray-500">Urus dan pantau semua idea inovasi di sini.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 font-bold">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Tajuk</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Cara Pelaksanaan</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pitches as $pitch)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $pitch->title }}</div>
                            <div class="text-xs text-gray-500 truncate w-48">{{ $pitch->description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $pitch->method }}</div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3">
                              <button
                                  wire:click="viewIdea({{ $pitch->id }})"
                                  class="p-2.5 bg-blue-50 hover:bg-blue-600 rounded-xl transition-all text-blue-400 hover:text-white group/view relative"
                               >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span class="absolute -top-10 left-1/2 -translate-x-1/2 scale-0 group-hover/view:scale-100 transition-all bg-slate-900 text-white text-[9px] font-black px-2 py-1.5 rounded-lg shadow-xl uppercase whitespace-nowrap z-30">
                                        Lihat Butiran
                                    </span>
                                </button>
                                <button wire:click="delete({{ $pitch->id }})" wire:confirm="Adakah anda pasti mahu memadam idea ini?" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Padam Program">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Tiada idea ditemui. </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showViewModal && $selectedIdea)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md" wire:click="closeViewModal"></div>
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-2xl w-full flex flex-col transform transition-all"
                 style="height: 80vh; max-height: 80vh; min-height: 300px;">
                <div class="p-10 pb-6 border-b border-slate-50 flex-none">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-[8px] font-black uppercase tracking-[0.2em] {{ $selectedIdea->category == 'inovasi' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                                {{ str_replace('_', ' ', $selectedIdea->category) }}
                            </span>
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Butiran Idea</h3>
                        </div>
                        <button wire:click="closeViewModal" class="p-2 bg-slate-50 text-slate-400 hover:text-red-500 rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
                <div class="flex-grow p-10 space-y-8"
                     style="overflow-y: auto !important; -webkit-overflow-scrolling: touch;">

                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block italic">Tajuk Projek/Idea</label>
                        <p class="text-xl font-black text-slate-900 uppercase italic leading-tight">{{ $selectedIdea->title }}</p>
                    </div>

                    <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block italic">Penerangan</label>
                        <p class="text-sm font-bold text-slate-700 leading-relaxed italic whitespace-pre-line">
                            {{ $selectedIdea->description }}
                        </p>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block italic text-blue-600">Cara Pelaksanaan</label>
                        <div class="p-8 bg-blue-50/30 border border-blue-100 rounded-[2.5rem] mb-4">
                            <p class="text-sm font-bold text-slate-700 leading-relaxed tracking-tight whitespace-pre-line">
                                {{ $selectedIdea->method?: 'Tiada tindakan direkodkan lagi.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-10 pt-6 border-t border-slate-50 bg-white flex-none rounded-b-[3rem]">
                    <button wire:click="closeViewModal" class="w-full py-5 bg-slate-900 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-[0.3em] hover:bg-slate-700 transition shadow-xl">
                        Tutup Paparan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
