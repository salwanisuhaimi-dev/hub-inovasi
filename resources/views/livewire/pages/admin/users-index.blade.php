<?php

use App\Models\User;
use function Livewire\Volt\{layout, state, with, usesPagination, usesFileUploads};

layout('layouts.app');
usesFileUploads();
usesPagination();

state([
    'showModal' => false,
    'editing' => null,
    'search' => '',
    'name' => '',
    'email' => '',
    'credits' => '',
    'department_id' => '',
    'position' => '',
    'grade' => '',
    'telephone_num' => '',
    'office_num' => '',
    'role' => '',
    'sortField' => 'role',
    'sortDirection' => 'asc',
]);

$edit = function (User $user) {
    $this->editing = $user->id;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->credits = $user->credits;
    $this->department_id = $user->department_id;
    $this->position = $user->position;
    $this->grade = $user->grade;
    $this->telephone_num = $user->telephone_num;
    $this->office_num = $user->office_num;
    $this->role = $user->role;

    $this->showModal = true;
};

$sortBy = function ($field) {
    if ($this->sortField === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }
};

with([
  'users' => fn() => User::latest()
      ->when($this->search, function ($query) {
          $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
      })
      ->orderBy($this->sortField, $this->sortDirection)
      ->paginate(12),

]);

$save = function () {
    $data = $this->validate([
        'name' => 'required',
        'email' => 'required',
        'credits' => '',
        'department_id' => 'required',
        'position' => '',
        'grade' => '',
        'telephone_num' => '',
        'office_num' => '',
        'role' => 'required',
    ]);

    $payload = [
        'name' => $this->name,
        'email' => $this->email,
        'credits' => $this->credits,
        'department_id' => $this->department_id,
        'position' => $this->position,
        'grade' => $this->grade,
        'telephone_num' => $this->telephone_num,
        'office_num' => $this->office_num,
        'role' => $this->role,
    ];

    if ($this->editing) {
        User::find($this->editing)->update($payload);
        session()->flash('message', 'Pengguna berjaya dikemaskini!');
    }

    $this->reset();
    $this->showModal = false;
};

$openCreateModal = function() {
    $this->reset(['editing', 'name', 'email', 'credits', 'department_id', 'position', 'grade', 'telephone_num', 'office_num', 'role']);
    $this->showModal = true;
}

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Senarai Pengguna</h2>
            <p class="text-sm text-gray-500">Urus dan pantau semua pengguna di sini.</p>
        </div>
        <!--
        <button wire:click="openCreateModal" class="flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Pengguna
        </button>-->

    </div>

    <div class="mb-6 max-w-sm">
        <div class="relative group">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari..."
                class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm outline-none transition-all duration-300 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-300 text-sm"
            >
            <!-- Modern Search Icon -->
            <div class="absolute right-3 top-2.5 text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                </svg>
            </div>
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
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Nama</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Bahagian</th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">E-mel</th>
                    <th class="px-6 py-4 cursor-pointer hover:bg-gray-50 transition" wire:click="sortBy('role')">
                        <div class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest">
                            Peranan
                            <span>
                                @if ($sortField === 'role')
                                    {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                @else
                                    <span class="text-gray-300">↕</span>
                                @endif
                            </span>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $user->department->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 font-medium">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 mt-1 flex items-center">{{ $user->role }}</div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3">
                                <button wire:click="edit({{ $user->id }})"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors group"
                                    title="Edit Pengguna">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <button wire:click="delete({{ $user->id }})"
                                    wire:confirm="Adakah anda pasti mahu memadam pengguna ini?"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Padam Pengguna">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">Tiada pengguna ditemui.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {{ $users->links() }}
    </div>


    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6">
                        {{ $editing ? 'Kemaskini Pengguna' : 'Tambah Pengguna Baru' }}
                    </h3>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Nama</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1 tracking-wider">Emel</label>
                                <input type="text" wire:model="email"
                                class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('email') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1 tracking-wider">Peranan</label>
                            <select wire:model="role"
                                class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">Pilih Peranan</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                            </select>
                            @error('role') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="pt-4 flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                                {{ $editing ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
