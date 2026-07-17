<?php

use App\Models\Department;
use App\Models\Competition;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{layout, title, state, with, mount};

layout('layouts.app');
title('Laporan Analitik Arkib Projek');

// 1. Set data tahun berasaskan data sebenar dalam DB (2024 & 2025)
state([
    'selectedYear' => 2025, // Paparan lalai bermula dengan 2025 supaya graf tidak kosong
    'selectedCompetitionId' => null,
    'years' => [2024, 2025, 2026] // Butang pilihan tahun yang disediakan
]);

mount(function() {
    $firstComp = Competition::first();
    $this->selectedCompetitionId = $firstComp ? $firstComp->id : null;
});

with([
    'competitionsList' => fn() => Competition::get(['id', 'name']),

    'barChartData' => function() {
        if (!$this->selectedCompetitionId) {
            return [];
        }

        $selectedDeptId = request('dept_id');

        return Department::when($selectedDeptId, function($query) use ($selectedDeptId) {
                return $query->where('id', $selectedDeptId);
            })
            ->get()
            ->map(function($dept) {

                // Query terus ke table menggunakan matching selectedYear (Integer/String safe)
                $count = DB::table('archive_competition')
                    ->join('archives', 'archive_competition.archive_id', '=', 'archives.id')
                    ->where('archives.department_id', $dept->id)
                    ->where('archive_competition.year', $this->selectedYear)
                    ->where('archive_competition.competition_id', $this->selectedCompetitionId)
                    ->count();

                return [
                    'dept'  => $dept->code ?? $dept->name,
                    'total' => $count
                ];
            })
            // Tapis keluar jabatan yang tiada rekod langsung untuk tahun/pertandingan ini
            ->filter(fn($item) => $item['total'] > 0)
            ->values()
            ->toArray();
    },
]); ?>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Papan Penapis (Tahun & Pertandingan) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-700 mb-1">Analisis Penglibatan Tertinggi Jabatan</h2>
                <p class="text-sm text-gray-500">Pilih tahun dan kategori pertandingan untuk melihat carta ranking jabatan.</p>
            </div>

            <div class="flex flex-wrap gap-6 items-center border-t border-gray-100 pt-4">
                <!-- Penapis 1: Tahun -->
                <div class="space-y-1.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tahun</span>
                    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-lg">
                        @foreach($years as $year)
                            <button
                                type="button"
                                wire:click="$set('selectedYear', {{ $year }})"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all {{ $selectedYear === $year ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                            >
                                {{ $year }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Penapis 2: Jenis Pertandingan -->
                <div class="space-y-1.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kategori Pertandingan</span>
                    <div class="flex flex-wrap gap-1 bg-gray-100 p-1 rounded-lg">
                        @foreach($competitionsList as $comp)
                            <button
                                type="button"
                                wire:click="$set('selectedCompetitionId', {{ $comp->id }})"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all {{ $selectedCompetitionId === $comp->id ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-indigo-600 hover:bg-white/50' }}"
                            >
                                {{ $comp->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Ruangan Graf Carta Bar -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div
                x-data="{
                    chart: null,
                    hasData: true,
                    initChart() {
                        const dataRaw = @js($barChartData);

                        // Kawal paparan jika tiada data dalam return array
                        if (!dataRaw || dataRaw.length === 0) {
                            this.hasData = false;
                            if (this.chart) this.chart.destroy();
                            return;
                        }

                        this.hasData = true;

                        this.$nextTick(() => {
                            if (this.chart) this.chart.destroy();

                            const labels = dataRaw.map(item => item.dept);
                            const totals = dataRaw.map(item => item.total);

                            this.chart = new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Jumlah Penyertaan',
                                        data: totals,
                                        backgroundColor: '#4f46e5',
                                        borderRadius: 6,
                                        barThickness: 30
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: { precision: 0 }
                                        }
                                    }
                                }
                            });
                        });
                    }
                }"
                x-init="initChart(); $watch('$wire.barChartData', () => initChart())"
                class="h-96 w-full relative"
            >
                <!-- Loading State Overlay -->
                <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-xs flex items-center justify-center z-10 rounded-xl">
                    <span class="text-sm font-semibold text-indigo-600 animate-pulse">Memuatkan data penapis...</span>
                </div>

                <!-- Canvas Graf -->
                <div x-show="hasData" class="w-full h-full">
                    <canvas x-ref="canvas"></canvas>
                </div>

                <!-- Mesej Alternatif jika data kosong -->
                <div x-show="!hasData" class="h-full flex flex-col items-center justify-center text-gray-400 italic text-sm">
                    Tiada sebarang rekod penyertaan untuk kombinasi penapis ini.
                </div>
            </div>
        </div>

    </div>
</div>
