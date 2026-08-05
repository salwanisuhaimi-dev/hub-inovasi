<?php

use App\Models\Publication;
use Livewire\WithFileUploads;
use function Livewire\Volt\{layout, state, with, usesFileUploads, updated, computed, usesPagination};

layout('layouts.app');
usesFileUploads();
usesPagination();

state([
    'showModal' => false,
    'editing' => null,
    'title' => '',
    'description' => '',
    'url' => '',
    'type' => 'TOR',
    'year' => date('Y'),

    'pdfs' => fn() => [],
    'existing_pdfs' => fn() => [],
    'new_pdfs' => fn() => [],
    'is_active' => 1,
]);

with([
    'publications' => fn () => Publication::latest()->paginate(15),
]);

updated(['new_pdfs' => function ($value) {
    if (!$value) return;
    $files = is_array($value) ? $value : [$value];

    $currentPdfs = $this->pdfs;
    foreach ($files as $file) {
        $currentPdfs[] = $file;
    }
    $this->pdfs = $currentPdfs;
    $this->new_pdfs = [];
}]);

$openCreateModal = function() {
    $this->reset(['editing', 'title', 'description', 'type', 'url', 'is_active']);
    $this->year = date('Y');
    $this->pdfs = [];
    $this->existing_pdfs = [];
    $this->new_pdfs = [];
    $this->showModal = true;
};

$edit = function (Publication $publication) {
    $this->editing = $publication->id;
    $this->title = $publication->title;
    $this->description = $publication->description;
    $this->type = $publication->type;
    $this->year = $publication->year;
    $this->url = $publication->url;
    $this->is_active = $publication->is_active;

    $this->existing_pdfs = is_array($publication->pdf_paths) ? $publication->pdf_paths : [];
    $this->pdfs = [];

    $this->showModal = true;
};

$removePdf = function ($index) {
    $currentPdfs = $this->pdfs;
    if (isset($currentPdfs[$index])) {
        unset($currentPdfs[$index]);
        $this->pdfs = array_values($currentPdfs);
    }
};

$removeExistingPdf = function ($index) {
    $currentExisting = $this->existing_pdfs;
    if (isset($currentExisting[$index])) {
        unset($currentExisting[$index]);
        $this->existing_pdfs = array_values($currentExisting);
    }
};

// Action to toggle status
$toggleStatus = function () {
    // Switch between 1 and 0
    $this->is_active = $this->is_active === 1 ? 0 : 1;

};


$save = function () {
    $this->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'type' => 'required|string',
        'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        'pdfs.*' => 'nullable|mimes:pdf,doc,docx|max:20480',
        'url' => 'nullable|string',
        'is_active' => 'nullable',
    ]);

    if (!$this->editing && count($this->pdfs) === 0) {
        $this->addError('pdfs', 'Sila muat naik sekurang-kurangnya satu fail PDF.');
        return;
    }

    $finalPaths = is_array($this->existing_pdfs) ? $this->existing_pdfs : [];

    foreach ($this->pdfs as $pdf) {
        $finalPaths[] = $pdf->store('publications', 'public');
    }

    $payload = [
        'title' => $this->title,
        'description' => $this->description,
        'url' => $this->url,
        'type' => $this->type,
        'year' => $this->year,
        'pdf_paths' => $finalPaths,
        'created_by' => auth()->id(),
        'is_active' => $this->is_active,
    ];

    if ($this->editing) {
        Publication::find($this->editing)->update($payload);
        session()->flash('message', 'Dokumen rasmi berjaya dikemaskini!');
    } else {
        Publication::create($payload);
        session()->flash('message', 'Dokumen rasmi berjaya diterbitkan!');
    }

    $this->showModal = false;
    $this->reset(['editing', 'title', 'description', 'type', 'is_active']);
    $this->pdfs = [];
    $this->existing_pdfs = [];
    $this->new_pdfs = [];
};

$delete = function (Publication $publication) {
    $publication->delete();
    session()->flash('message', 'Dokumen rasmi berjaya dipadam dari sistem.');
};


?>

<div class="p-8 max-w-7xl mx-auto">
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Penerbitan</h2>
            <p class="text-sm text-slate-500 font-medium">Urus, kemaskini penerbitan.</p>
        </div>
        <button wire:click="openCreateModal" class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition shadow-xl shadow-blue-100">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Dokumen
        </button>
    </div>

    {{-- Alert Message --}}
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 font-bold text-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- Table Section --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Dokumen & Ringkasan</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Kategori</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Tahun</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Lampiran PDF / URL</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($publications as $pub)
                    <tr class="hover:bg-blue-50/10 transition-colors group">
                        {{-- Title & Description --}}
                        <td class="px-8 py-5">
                            <div class="max-w-md">
                                <div class="font-black text-slate-900 tracking-tight text-base leading-snug">
                                    {{ $pub->title }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium mt-1 line-clamp-2" title="{{ $pub->description }}">
                                    {{ $pub->description }}
                                </div>
                            </div>
                        </td>

                        {{-- Category (Type) Badge --}}
                        <td class="px-8 py-5 text-center whitespace-nowrap">
                            @php
                                $colors = match($pub->type) {
                                    'TOR' => 'text-blue-700 bg-blue-50 border-blue-100',
                                    'Garis Panduan' => 'text-indigo-700 bg-indigo-50 border-indigo-100',
                                    'Template' => 'text-purple-700 bg-purple-50 border-purple-100',
                                    default => 'text-slate-700 bg-slate-50 border-slate-100'
                                };
                            @endphp
                            <span class="text-[10px] font-black px-3 py-1.5 rounded-xl border {{ $colors }} uppercase tracking-widest">
                                {{ $pub->type }}
                            </span>
                        </td>

                        {{-- Program Year --}}
                        <td class="px-8 py-5 text-center">
                            <span class="text-sm font-black text-slate-700 bg-slate-100 px-3 py-1 rounded-lg">
                                {{ $pub->year }}
                            </span>
                        </td>

                        {{-- PDF Attachment Preview Links --}}
                        <td class="px-8 py-5">
                            @php $files = $pub->pdf_paths ?? []; @endphp

                            @if(count($files) > 0)
                                <div class="flex flex-col gap-1">
                                    @foreach($files as $index => $path)
                                        <a href="{{ asset('storage/' . $path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100/70 px-2.5 py-1.5 rounded-lg border border-red-100 transition-all max-w-[180px] truncate"
                                           title="Buka {{ basename($path) }}">

                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                            </svg>

                                            <span>Fail {{ $index + 1 }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <br>

                            @if(!empty($pub->url))
                                <a href="{{ Str::startsWith($pub->url, ['http://', 'https://']) ? $pub->url : 'https://' . $pub->url }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100/70 px-2.5 py-1.5 rounded-lg border border-red-100 transition-all max-w-[180px] truncate">

                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>

                                    <span class="inline-block max-w-[200px] truncate" title="{{ $pub->url }}">
                                        {{ $pub->url }}
                                    </span>
                                </a>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="px-8 py-5 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-2">
                                {{-- Edit Button --}}
                                <button wire:click="edit({{ $pub->id }})" class="p-2.5 bg-slate-50 hover:bg-blue-600 rounded-xl transition-all text-slate-400 hover:text-white group/edit relative active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <button wire:click="delete({{ $pub->id }})" wire:confirm="Adakah anda pasti mahu memadam dokumen rasmi ini?" class="p-2.5 bg-slate-50 hover:bg-red-600 rounded-xl transition-all text-slate-400 hover:text-white active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Empty State --}}
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center opacity-20">
                                <svg class="w-20 h-20 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="font-black uppercase tracking-widest text-xs">Tiada dokumen rasmi ditemui</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {{ $publications->links() }}
    </div>


    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6">
                        {{ $editing ? 'Kemaskini Dokumen' : 'Tambah Dokumen Baru' }}
                    </h3>

                    <form wire:submit.prevent="save" class="space-y-4">
                         <div>
                             <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Tajuk Dokumen / Program</label>
                             <input type="text" wire:model="title" placeholder="cth: TOR Pertandingan Inovasi 2026" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                             @error('title') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                         </div>

                         <div class="grid grid-cols-2 gap-4">
                              <div>
                                   <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Kategori Dokumen</label>
                                   <select wire:model="type" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                        <option value="TOR">Terms of Reference (TOR)</option>
                                        <option value="Garis Panduan">Garis Panduan</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                   </select>
                              </div>

                              <div>
                                   <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Tahun Program</label>
                                   <select wire:model="year" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                      @for($y = date('Y') + 1; $y >= 2024; $y--)
                                          <option value="{{ $y }}">{{ $y }}</option>
                                      @endfor
                                   </select>
                              </div>
                         </div>

                         <div>
                              <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Penerangan / Pengenalan</label>
                              <textarea wire:model="description" rows="3" placeholder="Berikan sedikit ringkasan tentang kandungan fail ini..." class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                              @error('description') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                         </div>

                         <div>
                             <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Fail Lampiran (PDF Sahaja)</label>

                             <div class="space-y-2 mb-3 max-h-48 overflow-y-auto pr-1">
                                 {{-- A. SHOW EXISTING DOCUMENTS (Only visible during Editing) --}}
                                 @if(!empty($existing_pdfs) && is_array($existing_pdfs))
                                 @foreach($existing_pdfs as $index => $path)
                                     <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-blue-100">
                                         <div class="flex items-center gap-2 overflow-hidden">
                                             <span class="text-[9px] font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">ASAL</span>
                                             <span class="text-xs font-bold text-slate-700 truncate max-w-[280px]">
                                                 {{ basename($path) }}
                                             </span>
                                         </div>
                                         <button type="button" wire:click="removeExistingPdf({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xs px-2">Buang</button>
                                     </div>
                                 @endforeach
                                 @endif

                                 {{-- B. SHOW NEW STAGED UPLOADS --}}
                                 @foreach($pdfs as $index => $pdf)
                                     <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-emerald-100">
                                         <div class="flex items-center gap-2 overflow-hidden">
                                             <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">BARU</span>
                                             <span class="text-xs font-bold text-slate-600 truncate max-w-[280px]">
                                                 {{ $pdf->getClientOriginalName() }}
                                             </span>
                                         </div>
                                         <button type="button" wire:click="removePdf({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xs px-2">Buang</button>
                                     </div>
                                 @endforeach
                             </div>

                             {{-- Input Trigger Box --}}
                             <label class="flex items-center justify-center p-4 border-2 border-slate-200 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                                 <span class="text-xs font-black text-slate-400 uppercase tracking-wider">➕ Muat Naik Fail PDF</span>
                                 <input type="file" wire:model="new_pdfs" class="hidden" accept="application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .doc, .docx" multiple />
                             </label>

                             {{-- Buffer Loading Message --}}
                             <div wire:loading wire:target="new_pdfs" class="text-[10px] font-black text-blue-600 mt-2 animate-pulse">
                                 🔄 Memproses muat naik fail sementara...
                             </div>

                             @error('pdfs') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                             @error('pdfs.*') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                         </div>

                         <div>
                             <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">URL</label>
                             <input type="text" wire:model="url" placeholder="cth: https://www.document.my" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                             @error('url') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                         </div>

                         <div class="flex items-center justify-between p-3 border rounded-xl">
                             <div>
                                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Status</span>
                                 <span class="text-xs text-gray-400">Pilih samada dokumen ini aktif atau tidak</span>
                             </div>

                             <button
                                 type="button"
                                 wire:click="toggleStatus"
                                 class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $is_active ? 'bg-blue-600' : 'bg-gray-300' }}"
                             >
                                 <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out {{ $is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                             </button>
                         </div>


                         <div class="pt-4 flex gap-3">
                              <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                                  {{ $editing ? 'Simpan Perubahan' : 'Simpan Dokumen' }}
                              </button>
                              <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                         </div>
                    </form>
                </div>

            </div>
        </div>
    @endif

</div>
