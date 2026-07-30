<?php

use App\Models\Program;
use function Livewire\Volt\{layout, state, with, usesFileUploads, usesPagination};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

layout('layouts.app');
usesFileUploads();
usesPagination();


state([
    'showModal' => false,
    'editing' => null,
    'search' => '',
    'title' => '',
    'category_id' => '',
    'publication_id' => '',
    'form_publication_id' => '',
    'start_date' => '',
    'end_date' => '',
    'start_time' => '',
    'end_time' => '',
    'time_limit' => '',
    'location' => '',
    'description' => '',
    'deadline' => '',
    'image' => null,
    'currentImage' => '',
    'competition_id' => '',
    'created_by' => '',
    'date_from' => '',
    'date_to' => '',
]);

$edit = function (Program $program) {
    $this->editing = $program->id;
    $this->title = $program->title;
    $this->category_id = $program->category_id;
    $this->publication_id = $program->publication_id;
    $this->form_publication_id = $program->form_publication_id;
    $this->start_date = $program->start_date;
    $this->end_date = $program->end_date;
    $this->start_time = $program->start_time;
    $this->end_time = $program->end_time;
    $this->time_limit = $program->time_limit;
    $this->location = $program->location;
    $this->description = $program->description;
    $this->deadline = $program->deadline;
    $this->currentImage = $program->image_path ?? '';
    $this->image = null;
    $this->competition_id = $program->competition_id;
    $this->created_by = $program->created_by;

    $this->showModal = true;
};

with([
   'programs' => fn() => Program::latest()
          ->when($this->search, function ($query) {
              $query->where('title', 'like', '%' . $this->search . '%');
            })
            // Date From filter (start_date >= date_from)
            ->when($this->date_from, function ($query) {
              $query->whereDate('start_date', '>=', $this->date_from);
            })
            // Date To filter (start_date <= date_to)
            ->when($this->date_to, function ($query) {
              $query->whereDate('start_date', '<=', $this->date_to);
            })
            ->paginate(12),
    'categories' => fn() => \App\Models\ProgramType::where('is_active', '1')->orderBy('name')->get(),
    'competitions' => fn() => \App\Models\Competition::latest()->get(),
]);

$save = function () {
    $data = $this->validate([
        'title' => 'required',
        'category_id' => 'required',
        'publication_id' => 'nullable',
        'form_publication_id' => 'nullable',
        'description' => 'nullable',
        'start_date' => 'nullable|date',
        'deadline' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'image' => 'nullable|image|max:10420',
        'competition_id' => 'nullable',
        'created_by' => 'required',
    ]);

    $payload = [
        'title' => $this->title,
        'start_date' => $this->start_date ?: null,
        'end_date' => $this->end_date ?: null,
        'start_time' => $this->start_time ?: null,
        'end_time' => $this->end_time ?: null,
        'time_limit' => $this->time_limit ? : null,
        'location' => $this->location ?: null,
        'description' => $this->description ?: null,
        'deadline' => $this->deadline,
        'publication_id' => $this->publication_id ?: null,
        'form_publication_id' => $this->form_publication_id ?: null,
        'category_id' => $this->category_id ?: null,
        'competition_id' => $this->competition_id ?: null,
        'created_by' => $this->created_by,
    ];

    if ($this->image) {
        $temporaryPath = $this->image->getRealPath();
        $extension = strtolower($this->image->getClientOriginalExtension());

        $filename = 'programs/' . Str::random(40) . '.jpg';
        $absoluteStoragePath = storage_path('app/public/' . $filename);

        if (!file_exists(dirname($absoluteStoragePath))) {
            mkdir(dirname($absoluteStoragePath), 0755, true);
        }

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $sourceImage = @imagecreatefromjpeg($temporaryPath);
                break;
            case 'png':
                $sourceImage = @imagecreatefrompng($temporaryPath);
                break;
            case 'webp':
                $sourceImage = @imagecreatefromwebp($temporaryPath);
                break;
            default:
                $sourceImage = false;
        }

        if ($sourceImage) {
            imagejpeg($sourceImage, $absoluteStoragePath, 70);
            imagedestroy($sourceImage);
            $payload['image_path'] = $filename;
        } else {
            $path = $this->image->store('programs', 'public');
            $payload['image_path'] = $path;
        }
    }

    if ($this->editing) {
        Program::find($this->editing)->update($payload);
        session()->flash('message', 'Program berjaya dikemaskini!');
    } else {
        Program::create($payload);
        session()->flash('message', 'Program berjaya disimpan!');
    }

    $this->reset();
    $this->showModal = false;
};

$delete = function ($id) {
    $program = Program::findOrFail($id);

    if ($program->submissions()->exists()) {
        $program->submissions()->delete();
    }

    $program->delete();
    session()->flash('message', 'Program dan semua penyertaan di bawahnya berjaya dipadam!');
};

$openCreateModal = function() {
    $this->reset(['editing', 'title', 'start_date', 'end_date', 'start_time', 'end_time', 'time_limit', 'location', 'description', 'deadline', 'image', 'currentImage', 'category_id', 'publication_id', 'form_publication_id', 'competition_id', 'created_by']);
    $this->created_by = auth()->user()->id;
    $this->showModal = true;
};

$resetFilters = function () {
    $this->reset(['search', 'date_from', 'date_to']);
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Senarai Program</h2>
            <p class="text-sm text-gray-500">Urus dan pantau semua program inovasi anda di sini.</p>
        </div>
        <button wire:click="openCreateModal" class="flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Program
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-8">
        <!-- 1. Search Input -->
        <div class="relative group w-80">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari..."
                class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm outline-none transition-all duration-300 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-300 text-sm"
            >
            <div class="absolute right-3 top-2.5 text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                </svg>
            </div>
        </div>

        <!-- 2. Date From Input -->
        <div class="flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm">
            <span class="text-xs font-medium text-slate-400">Dari:</span>
            <input
                type="date"
                wire:model.live="date_from"
                class="text-sm text-slate-700 bg-transparent border-none outline-none focus:ring-0 p-0 cursor-pointer"
            >
        </div>

        <!-- 3. Date To Input -->
        <div class="flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-sm">
            <span class="text-xs font-medium text-slate-400">Hingga:</span>
            <input
                type="date"
                wire:model.live="date_to"
                class="text-sm text-slate-700 bg-transparent border-none outline-none focus:ring-0 p-0 cursor-pointer"
            >
        </div>

        <!-- 4. Proper Reset Semula Button -->
        @if ($search || $date_from || $date_to)
            <button
                wire:click="resetFilters"
                type="button"
                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl shadow-sm hover:bg-rose-100 hover:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-500/20 active:scale-95 transition-all duration-200 cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset Semula
            </button>
        @endif
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
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Program</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Kategori</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Tarikh</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Masa</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($programs as $program)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $program->title }}</div>
                            <!--<div class="text-xs text-gray-500 truncate w-48">{{ $program->description }}</div>-->
                            <!-- Author (Subtext with User Icon) -->
                            <div class="flex items-center gap-1.5 text-sm text-gray-500 mt-1">
                                <!-- SVG User Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>

                                <!-- Author Name using the author relation -->
                                Disediakan oleh:
                                            @if ($program->created_by === auth()->id())
                                                <strong class="text-blue-600 font-semibold">Anda</strong>
                                            @else
                                                {{ $program->author->name ?? 'Kosong' }}
                                            @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <!-- Category (Main Title) -->
                            <div class="font-bold text-gray-900 text-base">
                                {{ $program->category->name ?? 'Tiada Kategori' }}
                            </div>

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
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3">
                                <button wire:click="edit({{ $program->id }})" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors group" title="Edit Program">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $program->id }})" wire:confirm="Adakah anda pasti mahu memadam program ini?" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Padam Program">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Tiada program ditemui. Sila tambah program baru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {{ $programs->links() }}
    </div>


    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6">
                        {{ $editing ? 'Kemaskini Program' : 'Tambah Program Baru' }}
                    </h3>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tajuk Program</label>
                            <input type="text" wire:model="title" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            @error('title') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Kategori</label>
                            <select wire:model.live="category_id" class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pertandingan</label>
                            <select wire:model.live="competition_id" class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                                <option value="">Pilih Pertandingan</option>
                                @foreach($competitions as $competition)
                                    <option value="{{ $competition->id }}">{{ $competition->name }}</option>
                                @endforeach
                            </select>
                            @error('competition_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>


                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Mula Program</label>
                                <input type="date" wire:model="start_date" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('start_date') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Tamat Program</label>
                                <input type="date" wire:model="end_date" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('end_date') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Masa Mula</label>
                                <input type="time" wire:model="start_time" class="w-full rounded-xl border-gray-200">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Masa Akhir</label>
                                <input type="time" wire:model="end_time" class="w-full rounded-xl border-gray-200">
                            </div>
                        </div>

                        @if($category_id == 3)
                        <div x-data="{ show: false }"
                                x-init="setTimeout(() => show = true, 50)"
                                x-show="show"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="mt-4">

                              <div class="flex justify-between items-center mb-2">
                                  <label class="block text-xs font-black text-gray-400 uppercase mb-1">Had Masa Kuiz</label>
                                  <span class="text-xs text-gray-400 font-semibold bg-gray-100 px-2.5 py-1 rounded-full">Pilihan (Optional)</span>
                              </div>

                              <div class="relative flex items-center shadow-sm rounded-2xl">
                                   <input type="number"
                                            wire:model="time_limit"
                                            placeholder="Tiada had masa (Sila isi jika ada)"
                                            min="1"
                                            class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 pr-20 transition-all text-gray-900 font-medium">

                                    <div class="absolute right-4 flex items-center pointer-events-none border-l border-gray-200 pl-3">
                                         <span class="font-bold text-sm text-gray-400">
                                              Minit
                                          </span>
                                    </div>
                               </div>
                               @error('time_limit')
                               <span class="text-red-500 text-xs font-semibold mt-1 block flex items-center">
                                     <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                      </svg>
                                      {{ $message }}
                               </span>
                               @enderror
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Tutup Penyertaan</label>
                            <input type="date" wire:model="deadline" class="w-full rounded-xl border-gray-200">
                            @error('deadline') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Lokasi</label>
                            <input type="text" wire:model="location" placeholder="Contoh: Dewan Mezzanine" class="w-full rounded-xl border-gray-200">
                        </div>

                        <div>
                             <label class="block text-xs font-black text-gray-400 uppercase mb-1">Penerangan</label>
                             <textarea wire:model="description" rows="3" placeholder="Berikan sedikit ringkasan tentang program ini..." class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                             @error('description') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                             <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilih Dokumen Garis Panduan (Penerbitan)</label>
                             <select wire:model="publication_id" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                   <option value="">-- Tiada Garis Panduan --</option>
                                   @foreach(\App\Models\Publication::latest()->get() as $pub)
                                        <option value="{{ $pub->id }}">{{ $pub->title }}</option>
                                   @endforeach
                             </select>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilih Dokumen Borang Permohonan</label>
                            <select wire:model="form_publication_id" class="w-full rounded-xl border-gray-200 text-sm">
                                  <option value="">-- Tiada Borang Manual --</option>
                                  @foreach(\App\Models\Publication::latest()->get() as $pub)
                                      <option value="{{ $pub->id }}">{{ $pub->title }}</option>
                                  @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Poster / Gambar Pertandingan</label>
                            <div class="flex flex-col items-center justify-center border-2 border-dashed border-stone-300 rounded-[2rem] p-6 bg-stone-50/50">
                                @if ($image)
                                    <div class="relative w-40 h-40 mb-3">
                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover rounded-2xl shadow-md">
                                    </div>
                                    <span class="text-[10px] bg-amber-100 text-amber-800 px-3 py-0.5 rounded-full uppercase font-black tracking-wider">Previu Fail Baru</span>
                                @elseif ($currentImage)
                                    <div class="relative w-40 h-40 mb-3">
                                        <img src="{{ asset('storage/' . $currentImage) }}" class="w-full h-full object-cover rounded-2xl shadow-md">
                                    </div>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-3 py-0.5 rounded-full uppercase font-black tracking-wider">Gambar Semasa</span>
                                @else
                                    <div class="w-12 h-12 bg-stone-100 rounded-xl flex items-center justify-center text-stone-400 mb-3 text-xl">🖼️</div>
                                    <p class="text-[11px] text-stone-400 uppercase font-black tracking-wider">Tiada Gambar Disertakan</p>
                                @endif
                           </div>
                        </div>

                        <div class="mb-6">
                            <div class="relative">
                                <input type="file" wire:model="image" id="comp_image_field" class="hidden" accept="image/*">
                                <label for="comp_image_field" class="w-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold py-3 px-4 rounded-xl transition-all cursor-pointer block text-center text-xs border border-stone-300 shadow-sm">
                                    {{ $image || $currentImage ? 'Tukar Pilihan Gambar' : 'Pilih Fail Gambar' }}
                                </label>
                            </div>

                            <div wire:loading wire:target="image" class="text-[10px] text-orange-600 font-black italic animate-pulse text-center mt-2 block">
                                Sedang memproses gambar sementara... ⏳
                            </div>

                            @error('image') <span class="text-xs text-red-600 font-bold mt-1 block pl-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                                {{ $editing ? 'Simpan Perubahan' : 'Simpan Program' }}
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
