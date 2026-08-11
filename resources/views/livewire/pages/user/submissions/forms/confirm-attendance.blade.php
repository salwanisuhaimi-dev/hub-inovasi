<?php

use App\Models\Program;
use App\Models\Submission;
use App\Models\AttendanceSubmission;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, usesFileUploads, with};

usesFileUploads();

state([
    'program',
    'myProjectSubmission' => null,
    'dept_id' => '',
    'pdf_path' => null,
]);

with([
    'departments' => fn() => \App\Models\Department::where('status', 'aktif')->orderBy('name')->get(),
]);


mount(function (Program $program) {
    $this->program = $program;
    $currentUserId = Auth::id();
    $this->dept_id = auth()->user()->department_id;

    // 1. Ambil array ID daripada program
    $targetedIds = $program->target_submission_ids ?? [];

    if (!empty($targetedIds) && is_array($targetedIds)) {
        // 2. Cari projek sasaran milik pengguna yang sedang log masuk
        $this->myProjectSubmission = Submission::with('projectDetail')
            ->whereIn('id', $targetedIds)
            ->where('user_id', $currentUserId)
            ->first();
    }
});

$submitAttendance = function () {
    if (!$this->myProjectSubmission) {
        session()->flash('error', 'Anda tidak dibenarkan menghantar kehadiran kerana tiada projek sasaran ditemui di bawah akaun anda.');
        return;
    }

    $this->validate([
        'dept_id' => 'required|integer',
        'pdf_path' => [
                'required',
                'file',
                'max:10240', // 10MB
                // Menggunakan mimetypes lebih stabil untuk DOCX
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
    ]);

    $newSubmission = Submission::create([
        'program_id' => $this->program->id,
        'user_id' => Auth::id(),
    ]);

    $filePath = $this->pdf_path->store('attendance_submission', 'public');

    AttendanceSubmission::create([
        'submission_id' => $newSubmission->id,
        'target_submission_id' => $this->myProjectSubmission->id,
        'dept_id' => $this->dept_id,
        'pdf_path' => $filePath,
    ]);

    session()->flash('success', 'Pengesahan kehadiran berjaya dihantar!');
};

$downloadDocument = function () {
    /** @var Program $program */
    $program = $this->program;

    $publication = $program->formPublication ?? null;

    if (!$publication || !$publication->pdf_paths) {
        session()->flash('error', 'Dokumen tidak dijumpai.');
        return;
    }

    $paths = $publication->pdf_paths;
    $fileName = is_array($paths) ? ($paths[0] ?? null) : $paths;

    if (!$fileName) {
        session()->flash('error', 'Nama fail tidak sah.');
        return;
    }

    $relativePath = ltrim($fileName, '/');
    if (!Str::startsWith($relativePath, 'publications/')) {
        $relativePath = 'publications/' . $relativePath;
    }

    if (!Storage::disk('public')->exists($relativePath)) {
        session()->flash('error', 'Fail tiada dalam storan.');
        return;
    }

    $extension = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'pdf';

    $title = $publication->title ?? $program->title ?? 'Unknown';
    $downloadName = Str::slug($title) . '.' . $extension;

    return Storage::disk('public')->download($relativePath, $downloadName);
};


?>

<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Tajuk Program -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
             <h2 class="text-3xl font-black text-gray-900 tracking-tight">Maklum Balas Kehadiran</h2>
             <div class="mt-2 flex items-center gap-2">
                  <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">
                        {{ $program->title }}
                  </span>
             </div>
        </div>

        <div class="flex flex-col items-start md:items-end">
            @if (session()->has('error'))
                <div class="mb-2 text-sm text-red-600">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                @if($program->formPublication && $program->formPublication->pdf_paths)
                    <button
                        wire:click="downloadDocument"
                        type="button"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Borang
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($myProjectSubmission && $myProjectSubmission->projectDetail)
        <!-- Paparan Projek Dikesan -->
        <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
            <h3 class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-2">
                Projek
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs">Tajuk Projek:</span>
                    <span class="font-semibold text-gray-800">{{ $myProjectSubmission->projectDetail->project_title }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Nama Kumpulan:</span>
                    <span class="font-medium text-gray-700">{{ $myProjectSubmission->projectDetail->group_name ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 text-sm rounded-md">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded-md">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="submitAttendance" class="space-y-4">
          <div class="mt-4">
              <label class="block text-sm font-bold text-gray-700 mb-2 italic">Bahagian</label>
              <select wire:model="dept_id"
                      class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                  <option value="{{ auth()->user()->department_id }}">
                      {{ auth()->user()->department->name }}
                  </option>
              </select>
              @error('dept_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
          </div>
           <div class="mt-4">
               <label class="block text-sm font-bold text-gray-700 mb-2 italic">Lampiran Maklum Balas</label>
               <input type="file" wire:model="pdf_path" accept="application/pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="mt-1 block w-full text-sm text-gray-500">
               @error('pdf_path') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
           </div>

           <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-medium transition shadow-sm">
                <span wire:loading.remove>Hantar Pengesahan Kehadiran</span>
                <span wire:loading>Sedang memuat naik fail...</span>
            </button>

        </form>
    @else
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            <p class="font-bold mb-1">Tiada Rekod Projek Ditemui</p>
            <p>Akaun anda tidak didaftarkan untuk mana-mana projek bagi bengkel ini.</p>
        </div>
    @endif
</div>
