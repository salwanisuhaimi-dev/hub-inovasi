<?php

use App\Models\Archive;
use App\Models\Competition;
use function Livewire\Volt\{layout, state, with, usesFileUploads, usesPagination};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

layout('layouts.app');
usesFileUploads();
usesPagination();

state([
    'showModal' => false,
    'editing' => null,
    'search' => '',
    'selectedCompetition' => '',
    'department_id' => '',
    'project_name' => '',
    'group_name' => '',
    'description' => '',
    'track' => '',
    'currentImage' => '',
    'video_link' => '',
    'selected_comps' => [],
    'showDetailModal' => false,
    'viewingArchive' => null,
    'image' => null,
    'image_slot_0' => null,
    'image_slot_1' => null,
    'image_slot_2' => null,
]);

$removeImageSlot = function ($index) {
    if ($index == 0) $this->image_slot_0 = null;
    if ($index == 1) $this->image_slot_1 = null;
    if ($index == 2) $this->image_slot_2 = null;
};

$edit = function (Archive $archive) {
    $this->editing = $archive->id;
    $this->department_id = $archive->department_id;
    $this->project_name = $archive->project_name;
    $this->group_name = $archive->group_name;
    $this->description = $archive->description;
    $this->track = $archive->track;
    $this->currentImage = $archive->thumbnail ?? '';
    $this->video_link = $archive->video_link;
    $this->image = null; //thumbnail

    $this->selected_comps = [];
    foreach ($archive->competitions as $comp) {
        $this->selected_comps[$comp->id] = [
            'active' => true,
            'achievement' => $comp->pivot->achievement,
            'year' => $comp->pivot->year,
        ];
    }

    //image_paths
    $this->existing_images = $session->image_paths ?? [];
    $paths = $session->image_paths ?? [];
    $this->image_slot_0 = $paths[0] ?? null;
    $this->image_slot_1 = $paths[1] ?? null;
    $this->image_slot_2 = $paths[2] ?? null;

    $this->showModal = true;
};

with([
    'archives' => fn() => Archive::with(['department', 'competitions'])->latest()
            ->when($this->search, function ($query) {
                $query->where('project_name', 'like', '%' . $this->search . '%');
              })
              ->when($this->selectedCompetition, function ($query) {
                      $query->whereHas('competitions', function ($q) {
                          $q->where('competitions.id', $this->selectedCompetition);
                      });
              })
              ->paginate(12),
    'departments' => fn() => \App\Models\Department::where('status', 'aktif')->orderBy('name')->get(),
    'competitions' => fn() => Competition::where('status', 'aktif')->orderBy('name')->get(),
]);

$save = function () {
    $this->validate([
        'department_id' => 'required',
        'project_name' => 'required',
        'group_name' => 'required',
        'selected_comps' => 'required|array|min:1',
        'image' => 'nullable|image|max:10420'
    ]);


    if ($this->image) {
        $temporaryPath = $this->image->getRealPath();
        $extension = strtolower($this->image->getClientOriginalExtension());

        $filename = 'archives/' . Str::random(40) . '.jpg';
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
            $payload['thumbnail'] = $filename;
        } else {
            $path = $this->image->store('archives', 'public');
            $payload['thumbnail'] = $path;
        }
    }


    if (empty($this->image_slot_0) || empty($this->image_slot_1) || empty($this->image_slot_2)) {
        $this->addError('image_slots', "Sila pastikan ketiga-tiga slot gambar diisi.");
        return;
    }

    $manager = new ImageManager(new Driver());
    $finalPaths = [];

    if (!file_exists(storage_path('app/public/archives'))) {
        mkdir(storage_path('app/public/archives'), 0755, true);
    }

    $slots = [$this->image_slot_0, $this->image_slot_1, $this->image_slot_2];

    foreach ($slots as $slot) {
        if (is_string($slot)) {
            $finalPaths[] = $slot;
        } elseif (is_object($slot) && method_exists($slot, 'getRealPath')) {
            $filename = 'arc_' . uniqid() . '.webp';
            $image = $manager->read($slot->getRealPath());
            $image->scale(width: 1200);
            $encoded = $image->toWebp(80);

            file_put_contents(storage_path('app/public/archives/' . $filename), $encoded);
            $finalPaths[] = 'archives/' . $filename;
        }
    }

    $payload = [
        'department_id' => $this->department_id,
        'project_name' => $this->project_name,
        'group_name' => $this->group_name,
        'description' => $this->description,
        'track' => $this->track,
        'video_link' => $this->video_link,
        'image_paths' => $finalPaths,
    ];


    if ($this->editing) {
        $archive = Archive::find($this->editing);
        $archive->update($payload);
    } else {
        $archive = Archive::create($payload);
    }

    $pivotData = [];
    foreach ($this->selected_comps as $compId => $data) {
        if (!empty($data['active'])) {
            $pivotData[$compId] = [
                'achievement' => $data['achievement'] ?? null,
                'year' => $data['year'] ?? null,
            ];
        }
    }

    $archive->competitions()->sync($pivotData);



    $this->reset();
    $this->showModal = false;
    session()->flash('message', 'Arkib berjaya dikemaskini!');
};

$openCreateModal = function() {
    $this->reset();
    $this->selected_comps = [];

    $this->image_slot_0 = null;
    $this->image_slot_1 = null;
    $this->image_slot_2 = null;

    $this->showModal = true;
};

$viewDetails = function ($id) {
    $this->viewingArchive = Archive::with(['department', 'competitions'])->find($id);
    $this->showDetailModal = true;
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Senarai Projek</h2>
            <p class="text-sm text-gray-500">Urus dan pantau semua projek anda di sini.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.archives.report')}}" wire:navigate class="flex items-center px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-100">
                <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
                Laporan Analitik
            </a>

            <button wire:click="openCreateModal" class="flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Projek
              </button>
        </div>

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

        <div class="relative group w-48">
              <select
                  wire:model.live="selectedCompetition"
                  class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm outline-none transition-all duration-300 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 text-slate-600 text-sm appearance-none cursor-pointer"
              >
                  <option value="">Pilih Pertandingan...</option>
                  @foreach($competitions as $competition)
                      <option value="{{ $competition->id }}">
                          {{ $competition->name }}
                      </option>
                  @endforeach
              </select>
        </div>
    </div>


    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 font-bold">
            {{ session('message') }}
        </div>
    @endif

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
<table class="w-full text-left border-collapse">
    <thead class="bg-gray-50/80 backdrop-blur-md sticky top-0 z-10">
        <tr>
            <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Projek</th>
            <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kumpulan</th>
            <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pertandingan</th>
            <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Media</th>
            <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Tindakan</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
        @forelse($archives as $archive)
            <tr class="hover:bg-blue-50/30 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 flex-shrink-0 shadow-sm">
                            @if($archive->thumbnail)
                                <img src="{{ asset('storage/' . $archive->thumbnail) }}"
                                        alt="{{ $archive->project_name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col min-w-0">
                            <div class="font-bold text-gray-900 transition tracking-tight leading-tight">
                                {{ $archive->project_name }}
                            </div>

                            @if($archive->description)
                            <p class="text-[11px] text-gray-500 font-medium leading-relaxed mt-1 line-clamp-2">
                                {{ Str::words($archive->description, 20, '...') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-[11px] text-gray-500 font-medium flex items-center mt-1">
                        {{ $archive->group_name }}
                    </div>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">

                        {{ $archive->department->code ?? 'N/A' }}
                    </span>
                </td>

                <td class="px-6 py-4">
                @if($archive->competitions->count() > 0)
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-700 truncate max-w-[120px]">
                            {{ $archive->competitions->first()->name }}
                        </span>
                    @if($archive->competitions->count() > 1)
                        <span class="flex-shrink-0 bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-full">
                            +{{ $archive->competitions->count() - 1 }}
                        </span>
                    @endif
                    </div>
                @else
                    <span class="text-gray-300 text-xs italic">Tiada data</span>
                @endif
                    <!--<span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-100 uppercase">
                        TREK {{ $archive->track }}
                    </span>-->
                </td>
                <td class="px-6 py-4 text-center">
                    @if($archive->video_link)
                        <div class="flex flex-col items-center group/link">
                            <a href="{{ $archive->video_link }}"
                                target="_blank"
                                class="relative flex items-center justify-center w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300 shadow-sm"
                                title="Buka Pautan">

                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>

                                <span class="absolute -top-8 scale-0 group-hover/link:scale-100 transition-all bg-gray-900 text-white text-[9px] font-black px-2 py-1 rounded shadow-lg uppercase tracking-widest whitespace-nowrap z-20">
                                    Lihat Pautan
                                </span>
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col items-center opacity-30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter mt-1">Tiada</span>
                        </div>
                    @endif
                </td>

                <td class="px-6 py-4 text-right whitespace-nowrap">
                    <button wire:click="viewDetails({{ $archive->id }})"
                        class="p-2 hover:bg-slate-100 rounded-xl transition text-gray-400 hover:text-slate-900 group/view relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>

                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 scale-0 group-hover/view:scale-100 transition-all bg-gray-900 text-white text-[9px] font-black px-2 py-1 rounded shadow-lg uppercase tracking-widest whitespace-nowrap z-30">
                            Lihat Detail
                        </span>
                    </button>
                    <button wire:click="edit({{ $archive->id }})" class="p-2 hover:bg-blue-50 rounded-xl transition text-gray-400 hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button wire:click="confirmDelete({{ $archive->id }})" class="p-2 hover:bg-red-50 rounded-xl transition text-gray-400 hover:text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            </tr>
        @empty
            @endforelse
    </tbody>
</table>
</div>

<div class="mt-10">
    {{ $archives->links() }}
</div>


    @if($showModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showModal', false)">
                </div>
                <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                    <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">
                        {{ $editing ? 'Kemaskini Arkib' : 'Tambah Arkib Baru' }}
                    </h3>
                    <p class="text-sm text-gray-500 font-medium">Sila isi maklumat projek inovasi di bawah.</p>
                </div>
            </div>

            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Projek</label>
                    <input type="text" wire:model="project_name" placeholder="Contoh: Sistem AI Pengesanan Hama"
                        class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 font-bold text-gray-900">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Kumpulan</label>
                        <input type="text" wire:model="group_name" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Bahagian / Jabatan</label>
                        <select wire:model="department_id" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700">
                            <option value="">Pilih Bahagian</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Trek</label>
                    <input type="text" wire:model="track" placeholder="cth: Keselamatan"
                        class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 font-bold text-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-1">Penerangan Projek</label>
                        <textarea wire:model="description" rows="5"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                        </textarea>
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Senarai Pertandingan & Pencapaian</label>
                    <div class="space-y-3 bg-gray-50 p-4 rounded-[2rem] border border-gray-100 max-h-[300px] overflow-y-auto">
                    @foreach($competitions as $comp)
                        <div class="flex flex-col gap-3 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" wire:model="selected_comps.{{ $comp->id }}.active" class="rounded text-blue-600">
                                <span class="font-bold text-gray-900 text-sm">{{ $comp->name }}</span>
                            </label>

                            <div x-show="$wire.selected_comps[{{ $comp->id }}]?.active"
                                class="grid grid-cols-2 gap-3 pl-7">
                                <input type="text"
                                        wire:model="selected_comps.{{ $comp->id }}.achievement"
                                        placeholder="Pencapaian (cth: Juara)"
                                        class="text-xs p-2 rounded-lg border-gray-100 bg-gray-50 focus:ring-blue-500">

                                <input type="number"
                                        wire:model="selected_comps.{{ $comp->id }}.year"
                                        placeholder="Tahun"
                                        class="text-xs p-2 rounded-lg border-gray-100 bg-gray-50 focus:ring-blue-500">
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>

                <div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Link Video (URL)</label>
                        <input type="url" wire:model="video_link" placeholder="https://youtube.com/..."
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Poster / Gambar Thumbnail</label>
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

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Gambar Sesi</label>

                    @error('image_slots')
                        <span class="text-red-500 text-[10px] font-bold mb-3 block">{{ $message }}</span>
                    @enderror

                    <div class="grid grid-cols-3 gap-4 mb-4">
                         {{-- SLOT 1 --}}
                         <div class="relative group h-28 w-full border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center" wire:key="slot-0-panel">
                             @if(!empty($image_slot_0))
                                 @if(is_string($image_slot_0))
                                     <img src="{{ asset('storage/' . $image_slot_0) }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-amber-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Asal</span>
                                 @elseif(is_object($image_slot_0) && method_exists($image_slot_0, 'temporaryUrl'))
                                     <img src="{{ $image_slot_0->temporaryUrl() }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-blue-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Baru</span>
                                 @endif
                                 <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                     <button type="button" onclick="document.getElementById('file_input_0').click()" class="p-2 bg-white text-slate-700 rounded-xl shadow hover:bg-amber-500 hover:text-white transition">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                         </svg>
                                     </button>
                                     <button type="button" wire:click="removeImageSlot(0)" class="p-2 bg-white text-red-500 rounded-xl shadow hover:bg-red-500 hover:text-white transition">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                     </button>
                                 </div>
                             @else
                                 <button type="button" onclick="document.getElementById('file_input_0').click()" class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-amber-50/30 transition focus:outline-none">
                                     <div wire:loading wire:target="image_slot_0" class="text-center">
                                         <svg class="animate-spin h-5 w-5 text-amber-600 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                     </div>
                                     <div wire:loading.remove wire:target="image_slot_0" class="text-center p-2">
                                         <svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                         <span class="text-[8px] font-black text-slate-400 block mt-1 uppercase tracking-wider">Slot 1</span>
                                     </div>
                                 </button>
                             @endif
                             <input type="file" id="file_input_0" wire:model="image_slot_0" class="hidden" accept="image/*" />
                         </div>

                         {{-- SLOT 2 --}}
                         <div class="relative group h-28 w-full border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center" wire:key="slot-1-panel">
                             @if(!empty($image_slot_1))
                                 @if(is_string($image_slot_1))
                                     <img src="{{ asset('storage/' . $image_slot_1) }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-amber-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Asal</span>
                                 @elseif(is_object($image_slot_1) && method_exists($image_slot_1, 'temporaryUrl'))
                                     <img src="{{ $image_slot_1->temporaryUrl() }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-blue-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Baru</span>
                                 @endif
                                 <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                     <button type="button" onclick="document.getElementById('file_input_1').click()" class="p-2 bg-white text-slate-700 rounded-xl shadow hover:bg-amber-500 hover:text-white transition">
                                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                       </svg>
                                     </button>
                                     <button type="button" wire:click="removeImageSlot(1)" class="p-2 bg-white text-red-500 rounded-xl shadow hover:bg-red-500 hover:text-white transition">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                     </button>
                                 </div>
                             @else
                                 <button type="button" onclick="document.getElementById('file_input_1').click()" class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-amber-50/30 transition focus:outline-none">
                                     <div wire:loading wire:target="image_slot_1" class="text-center">
                                         <svg class="animate-spin h-5 w-5 text-amber-600 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                     </div>
                                     <div wire:loading.remove wire:target="image_slot_1" class="text-center p-2">
                                         <svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                         <span class="text-[8px] font-black text-slate-400 block mt-1 uppercase tracking-wider">Slot 2</span>
                                     </div>
                                 </button>
                             @endif
                             <input type="file" id="file_input_1" wire:model="image_slot_1" class="hidden" accept="image/*" />
                         </div>

                         {{-- SLOT 3 --}}
                         <div class="relative group h-28 w-full border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center" wire:key="slot-2-panel">
                             @if(!empty($image_slot_2))
                                 @if(is_string($image_slot_2))
                                     <img src="{{ asset('storage/' . $image_slot_2) }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-amber-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Asal</span>
                                 @elseif(is_object($image_slot_2) && method_exists($image_slot_2, 'temporaryUrl'))
                                     <img src="{{ $image_slot_2->temporaryUrl() }}" class="h-full w-full object-cover">
                                     <span class="absolute bottom-1 left-1 bg-blue-500 text-[8px] text-white px-1.5 rounded font-bold uppercase tracking-tight">Baru</span>
                                 @endif
                                 <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                     <button type="button" onclick="document.getElementById('file_input_2').click()" class="p-2 bg-white text-slate-700 rounded-xl shadow hover:bg-amber-500 hover:text-white transition">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                         </svg>
                                     </button>
                                     <button type="button" wire:click="removeImageSlot(2)" class="p-2 bg-white text-red-500 rounded-xl shadow hover:bg-red-500 hover:text-white transition">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                     </button>
                                 </div>
                             @else
                                 <button type="button" onclick="document.getElementById('file_input_2').click()" class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-amber-50/30 transition focus:outline-none">
                                     <div wire:loading wire:target="image_slot_2" class="text-center">
                                         <svg class="animate-spin h-5 w-5 text-amber-600 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                     </div>
                                     <div wire:loading.remove wire:target="image_slot_2" class="text-center p-2">
                                         <svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                         <span class="text-[8px] font-black text-slate-400 block mt-1 uppercase tracking-wider">Slot 3</span>
                                     </div>
                                 </button>
                             @endif
                             <input type="file" id="file_input_2" wire:model="image_slot_2" class="hidden" accept="image/*" />
                         </div>
                    </div>
                </div>


                <!--<div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Penerangan Projek</label>
                    <textarea wire:model="description" rows="4"
                        class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4"></textarea>
                </div>-->
                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                         {{ $editing ? 'Simpan Perubahan' : 'Simpan Projek' }}
                    </button>
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                </div>
            </form>
        </div>
    @endif

@if($showDetailModal && $viewingArchive)
<div class="fixed inset-0 z-[150] overflow-y-auto" x-transition>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showDetailModal', false)"></div>
        <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all p-0">
            <div class="relative h-48 bg-slate-100">
                @if($viewingArchive->thumbnail)
                    <img src="{{ asset('storage/' . $viewingArchive->thumbnail) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif

                <button wire:click="$set('showDetailModal', false)" class="absolute top-6 right-6 p-2 bg-white/20 backdrop-blur-md text-white rounded-full hover:bg-white hover:text-slate-900 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="p-10 -mt-12 relative bg-white rounded-t-[3rem]">
                <div class="mb-8">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest mb-4">
                        {{ $viewingArchive->department->name ?? 'Tiada Bahagian' }}
                    </span>
                    <h2 class="text-3xl font-black text-slate-900 leading-tight">
                        {{ $viewingArchive->project_name }}
                    </h2>
                    <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-100 uppercase">
                        TREK {{ $archive->track }}
                    </span>
                </div>

                <div class="mb-10 text-slate-600 leading-relaxed">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 text-left">Mengenai Projek</h4>
                    <p class="text-sm bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        {{ $viewingArchive->description ?? 'Tiada penerangan disediakan untuk projek ini.' }}
                    </p>
                </div>

                <div class="mb-10 text-left">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Sejarah Pencapaian</h4>
                    <div class="grid grid-cols-1 gap-3">
                        @forelse($viewingArchive->competitions as $comp)
                            <div class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500">
                                        🏆
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $comp->name }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ $comp->pivot->year }}</div>
                                    </div>
                                </div>
                                <div class="bg-amber-50 text-amber-700 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                    {{ $comp->pivot->achievement }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-300 italic text-sm">Tiada rekod pertandingan ditemui.</div>
                        @endforelse
                    </div>
                </div>

                @if($viewingArchive->video_link)
                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <span class="text-xs font-bold text-slate-400">Pautan Bahan:</span>
                        <a href="{{ $viewingArchive->video_link }}" target="_blank"
                           class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition shadow-lg shadow-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Buka Lampiran
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

</div>
