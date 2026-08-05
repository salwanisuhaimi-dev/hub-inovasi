<?php

use App\Models\Program;
use App\Models\Submission;
use function Livewire\Volt\{layout, state, usesFileUploads, with};

layout('layouts.app');

usesFileUploads();

?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Hantar Maklum Balas Kehadiran</h2>
            <div class="mt-2 flex items-center gap-2">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">
                    {{ $program->title }}
                </span>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2rem] border border-gray-100">
            <form wire:submit.prevent="submit" class="p-8 md:p-12 space-y-6">

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 italic">
                      @if($program->category_id == 5)
                           Tajuk Idea / Cadangan Inovasi
                      @else
                           Tajuk Projek
                      @endif
                    </label>
                    <input type="text" wire:model="project_title"
                        class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 font-semibold"
                        placeholder="Contoh: Sistem Smart Parking Universiti">
                    @error('project_title') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                </div>

                @if($program->category_id != 5)
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 italic">Nama Kumpulan</label>
                        <input type="text" wire:model="group_name"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4 font-semibold"
                            placeholder="Contoh: The Grea8">
                        @error('group_name') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2 italic">Bahagian</label>
                        <select wire:model="department_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 p-4">
                            <option value="{{ auth()->user()->department_id }}" selected>
                                {{ auth()->user()->department->name }}
                            </option>
                        </select>
                        @error('department_id') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <label class="block text-sm font-bold text-gray-700 mb-2 italic">Laporan</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-3xl hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label class="relative cursor-pointer bg-white rounded-md font-bold text-blue-600 hover:text-blue-500">
                                        <span>Muat naik fail</span>
                                        <input type="file" wire:model="file_upload" class="sr-only">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 italic">PDF, DOC, ZIP up to 10MB</p>
                            </div>
                        </div>

                        <div x-show="uploading" class="mt-4">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1 font-bold">Uploading: <span x-text="progress"></span>%</p>
                        </div>

                        @if ($file_upload)
                            <div class="mt-4 p-3 bg-green-50 rounded-xl flex items-center gap-2 border border-green-100">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                                <span class="text-xs font-bold text-green-700">Fail sedia: {{ $file_upload->getClientOriginalName() }}</span>
                            </div>
                        @endif
                        @error('file_upload') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                   </div>
                @endif


                <div class="pt-6">
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full flex justify-center py-4 px-6 border border-transparent rounded-2xl shadow-sm text-sm font-black uppercase tracking-widest text-white bg-gray-900 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-50">
                        <span wire:loading.remove>Hantar Sekarang</span>
                        <span wire:loading class="italic">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
