<?php

use App\Models\Submission;
use function Livewire\Volt\{layout, with, state};

layout('layouts.app');

with([
    'submissions' => fn() => Submission::where('user_id', auth()->id())
        ->with(['program', 'department'])
        ->latest()
        ->get(),
]);

$delete = function (Submission $submission) {
    if ($submission->user_id === auth()->id() && $submission->status === 'pending') {
        $submission->delete();
        session()->flash('success', 'Penyertaan telah dipadam.');
    }
};

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900">Penyertaan Saya</h2>
            <p class="text-gray-500">Senarai projek yang telah anda hantar untuk pertandingan.</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-[2rem] border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500">Projek</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500">Kumpulan / Bahagian</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500">Status</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($submissions as $submission)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-6">
                                    <p class="text-[10px] font-bold text-blue-600 uppercase mb-1">{{ $submission->program->title }}</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $submission->project_title }}</p>
                                    <p class="text-xs text-gray-500 italic mt-1">Dihantar pada: {{ $submission->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-6">
                                    <p class="text-lg text-gray-900">{{ $submission->group_name }}</p>
                                </td>
                                <td class="px-6 py-6">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                        ][$submission->status] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $statusClasses }}">
                                        {{ $submission->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($submission->status === 'pending')
                                            <a href="{{ route('submissions.edit', [
                                                                      'program' => $submission->program->id,
                                                                      'submission_slug' => $submission->program->category->submission_slug,
                                                                      'submission' => $submission->id
                                                                    ])
                                                      }}"
                                               class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition-all">
                                                Kemaskini
                                            </a>

                                            <button
                                                wire:click="delete({{ $submission->id }})"
                                                wire:confirm="Adakah anda pasti mahu memadam penyertaan ini?"
                                                class="px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-xl hover:bg-red-100 transition-all">
                                                Padam
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Not Allowed</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <p class="text-gray-500 font-medium">Anda belum menghantar sebarang penyertaan.</p>
                                    <a href="{{ route('user.dashboard') }}" class="text-blue-600 font-bold text-sm underline mt-2 inline-block">Cari Program</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
