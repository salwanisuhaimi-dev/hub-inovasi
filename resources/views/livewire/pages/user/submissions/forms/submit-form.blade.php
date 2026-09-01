<?php

use App\Models\Program;
use App\Models\Submission;
use App\Models\GeneralSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{state, mount, usesFileUploads};

usesFileUploads();

state([
    'program' => null,
    'user_notes' => '',
    'user_file' => null,
]);

mount(function (Program $program) {
    $this->program = $program;
});

$submit = function () {
    $format = $this->program->other_submission_format ?? 'notes';

    // 1. Pengesahan dinamik mengikut format pilihan Admin
    if ($format === 'upload_form') {
        $this->validate([
            'user_file' => 'required|file|max:10240|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,application/zip',
            'user_notes' => 'nullable|string|max:1000',
        ]);
    } else {
        $this->validate([
            'user_notes' => 'required|string|min:3|max:1000',
        ]);
    }

    // 2. Simpan fail jika peserta muat naik dokumen
    $filePath = null;
    if ($this->user_file) {
        $filePath = $this->user_file->store('user_submissions', 'public');
    }

    // 3. Cipta rekod penyerahan utama dalam jadual `submissions`
    $newSubmission = Submission::create([
        'program_id' => $this->program->id,
        'user_id'    => Auth::id(),
    ]);

    // 4. Cipta rekod butiran dalam jadual `generic_submissions`
    GeneralSubmission::create([
        'submission_id' => $newSubmission->id,
        'notes'         => $this->user_notes,
        'file_path'     => $filePath,
    ]);


    session()->flash('success', 'Penyertaan anda telah berjaya dihantar!');
};
?>

<div class="max-w-3xl mx-auto p-6 bg-white rounded-3xl shadow-sm border border-gray-200">

    {{-- Header & Butang Kembali --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">Borang Penyertaan Program</h2>
            <div class="mt-1">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">
                    {{ $program->title }}
                </span>
            </div>
        </div>
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
            Kembali
        </a>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 text-sm font-bold rounded-2xl flex items-center gap-2">
            {{ session('success') }}
        </div>
    @endif

    {{-- KASUS 1: ADMIN SEDIAKAN PAUTAN LUAR (Google Forms / Drive / URL) --}}
    @if($program->other_submission_format === 'external_link' && $program->submission_external_link)
        <div class="p-5 bg-blue-50/70 border border-blue-100 rounded-2xl mb-6">
            <h4 class="text-xs font-black text-blue-900 uppercase tracking-wider mb-1">🔗 Pautan Borang</h4>
            <p class="text-xs text-blue-700 mb-3">Sila buka pautan di bawah untuk menghantar penyertaan, kemudian klik Hantar Penyertaan setelah selesai.</p>
            <a href="{{ $program->submission_external_link }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition">
                Buka Pautan Borang
            </a>
        </div>
    @endif

    {{-- KASUS 2: ADMIN SEDIAKAN TEMPLAT BORANG (PDF / WORD) UNTUK DIMUAT TURUN --}}
    @if($program->other_submission_format === 'upload_form' && $program->submission_pdf_form)
        <div class="p-5 bg-amber-50/70 border border-amber-200 rounded-2xl mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider mb-1">📄 Muat Turun Templat Borang</h4>
                <p class="text-xs text-amber-700">Sila muat turun borang templat di bawah, lengkapkan maklumat, dan muat naik semula borang yang telah diisi.</p>
            </div>
            <a href="{{ Storage::url($program->submission_pdf_form) }}" download class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition whitespace-nowrap">
                Muat Turun Borang
            </a>
        </div>
    @endif

    {{-- BORANG PENYERAHAN PENGGUNA --}}
    <form wire:submit="submit" class="space-y-6">

        {{-- Input Muat Naik Fail Pengguna (Wajib jika Admin pilih 'upload_form') --}}
        @if($program->other_submission_format === 'upload_form')
            <div>
                <label class="block text-xs font-black text-gray-700 uppercase mb-2">
                    Muat Naik Borang Telah Diisi (PDF / Word) <span class="text-red-500">*</span>
                </label>
                <input type="file" wire:model="user_file" class="w-full text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-2xl p-3 focus:border-blue-500">
                <p class="text-[11px] text-gray-400 mt-1">Maksimum saiz fail: 10MB (PDF, DOCX, Images, ZIP)</p>
                @error('user_file') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        @endif

        {{-- Ruang Catatan / Notes --}}
        <div>
            <label class="block text-xs font-black text-gray-700 uppercase mb-2">
                {{ $program->other_submission_format === 'notes' ? 'Jawapan ' : 'Catatan Tambahan' }}
                @if($program->other_submission_format !== 'upload_form')
                    <span class="text-red-500">*</span>
                @else
                    <span class="text-gray-400 font-normal">(Optional)</span>
                @endif
            </label>
            <textarea wire:model="user_notes" rows="4"
                      placeholder="{{ $program->other_submission_format === 'notes' ? 'Sila tuliskan jawapan anda di sini...' : 'Tambah sebarang penerangan atau catatan ringkas jika ada...' }}"
                      class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            @error('user_notes') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Butang Hantar --}}
        <button type="submit" wire:loading.attr="disabled" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-blue-100 transition">
            <span wire:loading.remove>Hantar Penyertaan</span>
            <span wire:loading>Sedang Memproses...</span>
        </button>

    </form>
</div>
