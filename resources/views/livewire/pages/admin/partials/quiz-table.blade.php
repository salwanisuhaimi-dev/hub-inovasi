<thead class="bg-gray-50/50">
    <tr>
        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Soalan & Pilihan</th>
        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Jawapan Betul</th>
        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Fakta Menarik</th>
        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Tindakan</th>
    </tr>
</thead>
<tbody class="divide-y divide-gray-50 bg-white">
    @forelse($quizzes as $quiz)
        <tr class="hover:bg-blue-50/30 transition-all duration-200 group">
            <td class="px-6 py-5">
                <div class="max-w-md">
                    <div class="font-bold text-gray-900 mb-2 leading-snug line-clamp-3" title="{{ $quiz->question }}">
                        {{ $quiz->question }}
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                        <div class="text-m flex items-center gap-1.5">
                            <span class="font-black text-blue-500">A.</span>
                            <span class="text-gray-500 truncate" title="{{ $quiz->option_a }}">{{ $quiz->option_a }}</span>
                        </div>
                        <div class="text-m flex items-center gap-1.5">
                            <span class="font-black text-blue-500">B.</span>
                            <span class="text-gray-500 truncate" title="{{ $quiz->option_b }}">{{ $quiz->option_b }}</span>
                        </div>
                        <div class="text-m flex items-center gap-1.5">
                            <span class="font-black text-blue-500">C.</span>
                            <span class="text-gray-500 truncate" title="{{ $quiz->option_c }}">{{ $quiz->option_c }}</span>
                        </div>
                        <div class="text-m flex items-center gap-1.5">
                            <span class="font-black text-blue-500">D.</span>
                            <span class="text-gray-500 truncate" title="{{ $quiz->option_d }}">{{ $quiz->option_d }}</span>
                        </div>
                    </div>
                </div>
            </td>

            <td class="px-6 py-5 text-center uppercase">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-green-50 text-green-600 font-black border border-green-100 shadow-sm">
                    {{ $quiz->correct_answer }}
                </span>
            </td>
            <td class="px-6 py-5 text-center">
                    {{ $quiz->extras }}
            </td>


            <td class="px-6 py-5 text-right">
                <div class="flex justify-end gap-2">
                    <button wire:click="edit({{ $quiz->id }})"
                        class="p-2.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all active:scale-90"
                        title="Edit Soalan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>

                    <button wire:click="delete({{ $quiz->id }})"
                        wire:confirm="Adakah anda pasti mahu memadam soalan ini?"
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
                    <span class="text-gray-400 font-medium">Tiada soalan ditemui.</span>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
