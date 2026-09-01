<?php

use App\Models\Program;
use App\Models\Submission;
use App\Models\ActivityLog;
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
    'other_submission_format' => 'notes',
    'submission_external_link' => '',
    'submission_pdf_form' => null,
    'existing_submission_pdf_form' => null,
    'showGlobalHistory' => false,

    // Visibility Settings
    'visibility_type' => 'all', // 'all' or 'program_participants'
    'target_program_id' => '',  // Bound to the single select dropdown in UI


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

    // Visibility Hydration
    $this->visibility_type = $program->visibility_type ?? 'all';
    $this->target_program_id = is_array($program->target_program_ids) ? ($program->target_program_ids[0] ?? '') : '';

    if ((int) $program->category_id === 8) {
        $this->other_submission_format = $program->other_submission_format ?? 'notes';
        $this->submission_external_link = $program->submission_external_link ?? '';
        $this->existing_submission_pdf_form = $program->submission_pdf_form ?? null;
    } else {
        // Reset jika bertukar ke kategori biasa
        $this->other_submission_format = 'notes';
        $this->submission_external_link = '';
        $this->existing_submission_pdf_form = null;
    }

    $this->submission_pdf_form = null;
    $this->showModal = true;

};

with([
   'programs' => fn() => Program::latest()
          ->when($this->search, function ($query) {
              $query->where('title', 'like', '%' . $this->search . '%');
            })
          ->when($this->date_from, function ($query) {
              $query->whereDate('start_date', '>=', $this->date_from);
            })
          ->when($this->date_to, function ($query) {
              $query->whereDate('start_date', '<=', $this->date_to);
            })
          ->paginate(12),
    'categories' => fn() => \App\Models\ProgramType::where('is_active', '1')->orderBy('name')->get(),
    'competitions' => fn() => \App\Models\Competition::latest()->get(),
    'globalActivities' => fn() => $this->showGlobalHistory
            ? ActivityLog::with(['causer', 'loggable' => function ($query) {
                    $query->withTrashed();
                }])
                ->where('loggable_type', Program::class)
                ->latest()
                ->paginate(15)
            : [],
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
        'visibility_type' => 'required|in:all,program_participants',
        'target_program_id' => 'required_if:visibility_type,program_participants|nullable|exists:programs,id',
    ]);

    // Compute target arrays on backend automatically
    $targetProgramIds = null;
    $targetSubmissionIds = null;

    if ($this->visibility_type === 'program_participants' && $this->target_program_id) {
        $targetProgramIds = [(int) $this->target_program_id];

        // Auto pull all unique user_ids who submitted to the selected target program
        $targetSubmissionIds = Submission::where('program_id', $this->target_program_id)
            ->pluck('id')
            ->unique()
            ->map(fn($id) => (int) $id)
            ->values()
            ->toArray();
    }

    if ((int) $this->category_id === 8) {
        $rules['other_submission_format'] = 'required|in:notes,external_link,upload_form';

        if ($this->other_submission_format === 'external_link') {
            $rules['submission_external_link'] = 'required|url';
        }

        if ($this->other_submission_format === 'upload_form') {
            // Hanya wajib jika baru dimuat naik (elak ralat bila edit)
            $rules['submission_pdf_form'] = ($this->submission_pdf_form || !$this->existing_submission_pdf_form)
                        ? 'required|file|mimes:pdf,doc,docx|max:10240'
                        : 'nullable';
        }
    }

    $this->validate($rules);

    // 4. Pengendalian Muat Naik Fail PDF (Kategori 8)
    $pdfPath = $this->existing_submission_pdf_form;

    if ((int) $this->category_id === 8 && $this->other_submission_format === 'upload_form') {
        if ($this->submission_pdf_form instanceof \Illuminate\Http\UploadedFile) {
            // Padam fail lama jika wujud apabila muat naik fail baharu
            if ($this->existing_submission_pdf_form && Storage::disk('public')->exists($this->existing_submission_pdf_form)) {
                Storage::disk('public')->delete($this->existing_submission_pdf_form);
            }
            $pdfPath = $this->submission_pdf_form->store('program_forms', 'public');
        }
    } else {
        $pdfPath = null;
    }

    $payload = [
        'title' => $this->title,
        'start_date' => $this->start_date ?: null,
        'end_date' => $this->end_date ?: null,
        'start_time' => $this->start_time ?: null,
        'end_time' => $this->end_time ?: null,
        'time_limit' => $this->time_limit ?: null,
        'location' => $this->location ?: null,
        'description' => $this->description ?: null,
        'deadline' => $this->deadline,
        'publication_id' => $this->publication_id ?: null,
        'form_publication_id' => $this->form_publication_id ?: null,
        'category_id' => $this->category_id ?: null,
        'competition_id' => $this->competition_id ?: null,
        'created_by' => $this->created_by,
        'visibility_type' => $this->visibility_type,
        'target_program_ids' => $targetProgramIds,
        'target_submission_ids' => $targetSubmissionIds,
        'other_submission_format' => ((int) $this->category_id === 8) ? $this->other_submission_format : null,
        'submission_external_link' => ((int) $this->category_id === 8 && $this->other_submission_format === 'external_link') ? $this->submission_external_link : null,
        'submission_pdf_form' => $pdfPath
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
    $this->reset([
        'editing', 'title', 'start_date', 'end_date', 'start_time', 'end_time',
        'time_limit', 'location', 'description', 'deadline', 'image', 'currentImage',
        'category_id', 'publication_id', 'form_publication_id', 'competition_id',
        'created_by', 'visibility_type', 'target_program_id'
    ]);
    $this->created_by = auth()->user()->id;
    $this->showModal = true;
};

$resetFilters = function () {
    $this->reset(['search', 'date_from', 'date_to']);
};

$restoreFromLog = function ($programId) {
    $program = Program::withTrashed()->find($programId);
    if ($program) {
        $program->restore();
    }
};

$openGlobalHistoryModal = function () {
    $this->showGlobalHistory = true;
};

$closeGlobalHistoryModal = function () {
    $this->showGlobalHistory = false;
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

        <!-- 4. Reset Filters Button -->
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

        <button
            wire:click="openGlobalHistoryModal"
            type="button"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl shadow-sm hover:bg-slate-50 transition-all duration-200 cursor-pointer ml-auto"
        >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Sejarah Aktiviti
        </button>
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
                            <div class="flex items-center gap-1.5 text-sm text-gray-500 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>

                                <div class="text-xs text-slate-500">
                                    @if ($program->lastEditor && $program->lastEditor->user_id)
                                    <div>
                                         <span>Dikemaskini oleh:</span>
                                         @if ($program->lastEditor->user_id === auth()->id())
                                              <strong class="text-emerald-600 font-semibold">Anda</strong>
                                         @else
                                              <span class="font-medium text-slate-700">{{ $program->lastEditor->causer->name ?? 'Kosong' }}</span>
                                         @endif
                                         <span class="text-[10px] text-slate-400">({{ $program->lastEditor->created_at->diffForHumans() }})</span>
                                    </div>
                                    @else
                                    <div>
                                        <span>Disediakan oleh:</span>
                                        @if ($program->created_by === auth()->id())
                                              <strong class="text-blue-600 font-semibold">Anda</strong>
                                        @else
                                              <span class="font-medium text-slate-700">{{ $program->author->name ?? 'Kosong' }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
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

                {{-- Widened Modal Container (sm:max-w-4xl) --}}
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6">
                        {{ $editing ? 'Kemaskini Program' : 'Tambah Program Baru' }}
                    </h3>

                    <form wire:submit.prevent="save">
                        {{-- Two-Column Grid Shell --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- 1. Tajuk Program (Full Width) --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tajuk Program</label>
                                <input type="text" wire:model="title" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                @error('title') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            {{-- 2. Kategori --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Kategori</label>
                                <select wire:model.live="category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-3 text-sm">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            @if((int) $category_id === 8)
                                <div class="p-5 bg-blue-50/50 border border-blue-100 rounded-2xl space-y-4 mt-4">
                                     <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                                          <h4 class="text-xs font-black text-blue-900 uppercase tracking-wider flex items-center gap-2">
                                              Jenis Penyertaan
                                          </h4>
                                     </div>

                                     {{-- 1. PILIHAN FORMAT PENYERTAAN --}}
                                     <div>
                                          <label class="block text-xs font-black text-gray-500 uppercase mb-1">
                                            Format Penyertaan <span class="text-red-500">*</span>
                                          </label>
                                          <select wire:model.live="other_submission_format" class="w-full rounded-xl border-gray-200 bg-white focus:border-blue-500 focus:ring-blue-500 p-3 text-sm font-bold text-gray-700">
                                                <option value="notes">Teks Sahaja</option>
                                                <option value="external_link">Pautan Luar / Google Forms</option>
                                                <option value="upload_form">Muat Naik Borang</option>
                                          </select>
                                          @error('other_submission_format') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                     </div>

                                     {{-- 2. INPUT PAUTAN URL (Hanya jika 'external_link' dipilih) --}}
                                     @if($other_submission_format === 'external_link')
                                     <div class="pt-2">
                                          <label class="block text-xs font-black text-gray-700 uppercase mb-1">
                                              Pautan URL Borang Luar <span class="text-red-500">*</span>
                                          </label>
                                          <input type="url"
                                                wire:model="submission_external_link"
                                                placeholder="https://forms.google.com/..."
                                                class="w-full rounded-xl border-gray-200 bg-white p-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                          <p class="text-[10px] text-gray-400 mt-1">Sediakan pautan Google Form, Drive, atau laman web luar untuk diakses peserta.</p>
                                          @error('submission_external_link') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                     </div>
                                     @endif

                                     @if($other_submission_format === 'upload_form')
                                     <div class="pt-2">
                                          <label class="block text-xs font-black text-gray-700 uppercase mb-1">
                                                Muat Naik Borang Templat (PDF / Word) <span class="text-red-500">*</span>
                                          </label>

                                          @if($existing_submission_pdf_form)
                                          <div class="mb-3 p-3 bg-white rounded-xl border border-blue-200 flex items-center justify-between shadow-sm">
                                               <div class="flex items-center gap-2">
                                                    <div>
                                                        <span class="text-xs font-bold text-gray-800 block">Fail Sedia Ada</span>
                                                        <span class="text-[10px] text-gray-400">Muat naik fail baharu di bawah jika mahu menggantikannya.</span>
                                                    </div>
                                              </div>
                                              <a href="{{ Storage::url($existing_submission_pdf_form) }}" target="_blank" class="px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold transition">
                                                    Lihat Fail
                                              </a>
                                          </div>
                                          @endif

                                          <input type="file"
                                              wire:model="submission_pdf_form"
                                              accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                              class="w-full text-sm text-gray-500 bg-white border border-gray-200 rounded-xl p-2.5 focus:border-blue-500">
                                              <p class="text-[10px] text-gray-400 mt-1">Maksimum saiz fail: 10MB (Format PDF, DOC, atau DOCX)</p>
                                          @error('submission_pdf_form') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                              </div>
                           @endif

                            {{-- 3. Pertandingan --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pertandingan <span class="text-gray-400 font-normal lowercase">(optional)</span></label>
                                <select wire:model.live="competition_id" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-3 text-sm">
                                    <option value="">Pilih Pertandingan</option>
                                    @foreach($competitions as $competition)
                                        <option value="{{ $competition->id }}">{{ $competition->name }}</option>
                                    @endforeach
                                </select>
                                @error('competition_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            {{-- 4. Tarikh Mula --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Mula Program</label>
                                <input type="date" wire:model="start_date" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('start_date') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            {{-- 5. Tarikh Tamat --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Tamat Program</label>
                                <input type="date" wire:model="end_date" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('end_date') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            {{-- 6. Masa Mula --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Masa Mula</label>
                                <input type="time" wire:model="start_time" class="w-full rounded-xl border-gray-200 text-sm">
                            </div>

                            {{-- 7. Masa Akhir --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Masa Akhir</label>
                                <input type="time" wire:model="end_time" class="w-full rounded-xl border-gray-200 text-sm">
                            </div>

                            {{-- 8. Had Masa Kuiz (Conditional - Full Width) --}}
                            @if($category_id == 3)
                                <div class="md:col-span-2" x-data="{ show: false }"
                                     x-init="setTimeout(() => show = true, 50)"
                                     x-show="show"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 transform translate-y-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-xs font-black text-gray-400 uppercase">Had Masa Kuiz</label>
                                        <span class="text-xs text-gray-400 font-semibold bg-gray-100 px-2 py-0.5 rounded-full">Pilihan (Optional)</span>
                                    </div>
                                    <div class="relative flex items-center shadow-sm rounded-xl">
                                        <input type="number"
                                               wire:model="time_limit"
                                               placeholder="Tiada had masa (Sila isi jika ada)"
                                               min="1"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-3 pr-20 text-sm text-gray-900 font-medium">
                                        <div class="absolute right-4 flex items-center pointer-events-none border-l border-gray-200 pl-3">
                                            <span class="font-bold text-xs text-gray-400">Minit</span>
                                        </div>
                                    </div>
                                    @error('time_limit')
                                        <span class="text-red-500 text-xs font-semibold mt-1 flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            @endif

                            {{-- 9. Tarikh Tutup Penyertaan --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Tarikh Tutup Penyertaan</label>
                                <input type="date" wire:model="deadline" class="w-full rounded-xl border-gray-200 text-sm">
                                @error('deadline') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            {{-- 10. Lokasi --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Lokasi</label>
                                <input type="text" wire:model="location" placeholder="Contoh: Dewan Mezzanine" class="w-full rounded-xl border-gray-200 text-sm">
                            </div>

                            {{-- 11. Penerangan (Full Width) --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Penerangan</label>
                                <textarea wire:model="description" rows="2" placeholder="Berikan sedikit ringkasan tentang program ini..." class="w-full bg-slate-50 border-gray-200 rounded-xl p-3 text-sm font-medium focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                                @error('description') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- 12. Dokumen Garis Panduan --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilih Dokumen Garis Panduan</label>
                                <select wire:model="publication_id" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm p-2.5">
                                    <option value="">-- Tiada Garis Panduan --</option>
                                    @foreach(\App\Models\Publication::latest()->get() as $pub)
                                        <option value="{{ $pub->id }}">{{ $pub->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 13. Dokumen Borang Permohonan --}}
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Pilih Borang Permohonan</label>
                                <select wire:model="form_publication_id" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm p-2.5">
                                    <option value="">-- Tiada Borang Manual --</option>
                                    @foreach(\App\Models\Publication::latest()->get() as $pub)
                                        <option value="{{ $pub->id }}">{{ $pub->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 14. HAD AKSES / VISIBILITY SECTION (Full Width) --}}
                            <div class="md:col-span-2 border-t pt-4">
                                <label class="block text-xs font-black text-gray-400 uppercase mb-1">Had Akses (Visibility)</label>
                                <p class="text-xs text-gray-500 mb-3">Tentukan siapa yang dibenarkan untuk melihat dan menghantar permohonan.</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    {{-- Option 1: Open to All --}}
                                    <label class="flex items-start p-3 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ $visibility_type === 'all' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/20' : 'border-gray-200' }}">
                                        <input type="radio" wire:model.live="visibility_type" value="all" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ms-3">
                                            <span class="block text-sm font-medium text-gray-900">Terbuka Kepada Semua (Public)</span>
                                            <span class="block text-xs text-gray-500">Sesiapa sahaja boleh melihat dan mendaftar program ini.</span>
                                        </div>
                                    </label>

                                    {{-- Option 2: Program Participants --}}
                                    <label class="flex items-start p-3 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ $visibility_type === 'program_participants' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/20' : 'border-gray-200' }}">
                                        <input type="radio" wire:model.live="visibility_type" value="program_participants" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ms-3">
                                            <span class="block text-sm font-medium text-gray-900">Peserta Program Prasyarat</span>
                                            <span class="block text-xs text-gray-500">Hanya peserta program terpilih dibenarkan.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- PREREQUISITE PROGRAM SELECTION (Full Width) --}}
                            @if($visibility_type === 'program_participants')
                                <div class="md:col-span-2 p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                                    <label class="block text-xs font-bold text-gray-700 uppercase">Pilih Program Prasyarat:</label>
                                    <select wire:model="target_program_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                        <option value="">-- Pilih Program --</option>
                                        @foreach(App\Models\Program::when($editing, fn($q) => $q->where('id', '!=', $editing))->latest()->get() as $p)
                                            <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->submissions()->count() }} Penyertaan)</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        * Semua peserta yang pernah hantar penyertaan untuk program ini akan auto-dibenarkan akses.
                                    </p>
                                    @error('target_program_id') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- 15. POSTER / GAMBAR PERTANDINGAN (Centered Compact) --}}
                            <div class="md:col-span-2 border-t pt-4 text-center">
                                <label class="block text-xs font-black text-gray-400 uppercase mb-3">Poster / Gambar Pertandingan</label>

                                <div class="flex flex-col items-center justify-center gap-2">
                                    {{-- Thumbnail Box --}}
                                    <div class="relative">
                                        @if ($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 object-cover rounded-2xl shadow-sm border border-gray-200">
                                            <span class="absolute -top-1.5 -right-1.5 flex h-3.5 w-3.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-amber-500"></span>
                                            </span>
                                        @elseif ($currentImage)
                                            <img src="{{ asset('storage/' . $currentImage) }}" class="w-20 h-20 object-cover rounded-2xl shadow-sm border border-gray-200">
                                        @else
                                            <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 border border-dashed border-gray-300">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Status Badge --}}
                                    @if ($image)
                                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Previu Gambar Baru</span>
                                    @elseif ($currentImage)
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Gambar Semasa</span>
                                    @else
                                        <span class="text-[10px] font-medium text-gray-400">Tiada gambar muat naik</span>
                                    @endif

                                    {{-- Upload Action Button --}}
                                    <div class="mt-1">
                                        <input type="file" wire:model="image" id="comp_image_field" class="hidden" accept="image/*">
                                        <label for="comp_image_field" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl cursor-pointer transition border border-slate-200 shadow-sm">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            {{ $image || $currentImage ? 'Tukar Gambar' : 'Pilih Fail Gambar' }}
                                        </label>
                                    </div>

                                    {{-- Loading Indicator & Errors --}}
                                    <div wire:loading wire:target="image" class="text-[10px] text-orange-600 font-bold italic animate-pulse">
                                        Sedang memproses gambar sementara... ⏳
                                    </div>

                                    @error('image') <span class="text-xs text-red-600 font-bold block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Form Submit Buttons --}}
                        <div class="pt-6 mt-4 border-t flex gap-3">
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
    <!-- Global Activity Log Modal -->
    @if ($showGlobalHistory)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="closeGlobalHistoryModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">

                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Sejarah Aktiviti Keseluruhan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Log rekod pembinaan, pengemaskinian, dan pemadaman program.</p>
                        </div>
                        <button wire:click="closeGlobalHistoryModal" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 max-h-[65vh] overflow-y-auto">
                        @if ($globalActivities->isEmpty())
                            <div class="text-center py-8 text-slate-400 text-sm">
                                Tiada rekod aktiviti ditemui.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach ($globalActivities as $activity)
                                    <div class="py-3 flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-0.5 px-2 py-0.5 text-[10px] font-bold uppercase rounded-md shrink-0
                                                {{ $activity->action === 'created' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $activity->action === 'updated' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $activity->action === 'deleted' ? 'bg-rose-100 text-rose-700' : '' }}">
                                                {{ $activity->action }}
                                            </span>

                                            <div>
                                                <p class="text-sm font-medium text-slate-800">
                                                    <strong class="text-slate-900">{{ $activity->causer->name ?? 'Sistem' }}</strong>
                                                    <span class="text-slate-500">
                                                        @if($activity->action === 'created') membina @elseif($activity->action === 'updated') mengemaskini @else memadam @endif
                                                    </span>
                                                    <span class="font-bold text-indigo-600">
                                                        "{{ $activity->loggable->title ?? 'Program' }}"
                                                    </span>
                                                </p>

                                                @if ($activity->action === 'updated' && !empty($activity->changes['new']))
                                                    <div class="mt-1 text-xs text-slate-500">
                                                        @foreach ($activity->changes['new'] as $field => $newValue)
                                                            @if ($field !== 'updated_at')
                                                                <span class="text-emerald-600 font-semibold">{{ $newValue }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($activity->action === 'deleted' && $activity->loggable?->trashed())
                                                    <button
                                                        wire:click="restoreFromLog({{ $activity->loggable->id }})"
                                                        type="button"
                                                        class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded transition cursor-pointer"
                                                    >
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                        Pulihkan Program Ini
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="text-[11px] text-slate-400 whitespace-nowrap shrink-0">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $globalActivities->links() }}
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-right">
                        <button wire:click="closeGlobalHistoryModal" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200/60 rounded-xl transition">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
