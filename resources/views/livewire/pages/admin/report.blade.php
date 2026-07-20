<?php

use App\Models\Department;
use App\Models\CoffeeBreakIdea;
use function Livewire\Volt\{layout, title, with};

layout('layouts.app');
title('Laporan Analitik Coff-B');

// jangan delete dulu function data dummy. kena tallykan dulu letak apa patut dekat function real data baru delete.
with([
  'reportData' => function() {
      $selectedMonth = request('month'); // Boleh jadi 'q1', '01', atau null
      $selectedYear = request('year', date('Y'));
      $selectedDeptId = request('dept_id');

      $query = \App\Models\CoffeeBreakIdea::whereHas('session', function($q) use ($selectedDeptId, $selectedYear, $selectedMonth) {

          if ($selectedDeptId) {
              $q->where('department_id', $selectedDeptId);
          }

          $q->whereYear('date_created', $selectedYear);

          if ($selectedMonth) {
              if (str_starts_with($selectedMonth, 'q')) {
                  $quarters = [
                      'q1' => [1, 2, 3],
                      'q2' => [4, 5, 6],
                      'q3' => [7, 8, 9],
                      'q4' => [10, 11, 12],
                  ];
                  $months = $quarters[$selectedMonth] ?? [];
                  $q->whereIn(\DB::raw('MONTH(date_created)'), $months);
              }
              else if (str_starts_with($selectedMonth, 'h')) {
                  $halves = [
                      'h1' => [1, 2, 3, 4, 5, 6],
                      'h2' => [7, 8, 9, 10, 11, 12],
                  ];
                  $months = $halves[$selectedMonth] ?? [];
                  $q->whereIn(\DB::raw('MONTH(date_created)'), $months);
              }
              else {
                  $q->whereMonth('date_created', $selectedMonth);
              }
          }
      });

      $allIdeas = $query->with('session')->get();

      if (!$selectedDeptId) {
          return collect([[
              'name' => 'KESELURUHAN',
              'stats' => [
                  'Inovasi (Digital)' => $allIdeas->where('category', 'inovasi')->where('is_digital', 'digital')->count(),
                  'Inovasi (Bukan Digital)' => $allIdeas->where('category', 'inovasi')->where('is_digital', 'non-digital')->count(),
                  'Selain Inovasi (Digital)' => $allIdeas->where('category', 'selain_inovasi')->where('is_digital', 'digital')->count(),
                  'Selain Inovasi (Bukan Digital)' => $allIdeas->where('category', 'selain_inovasi')->where('is_digital', 'non-digital')->count(),
              ],
              'total' => $allIdeas->count()
          ]])->filter(fn($item) => $item['total'] > 0);
      }

      return \App\Models\Department::where('id', $selectedDeptId)->get()->map(function($dept) use ($allIdeas) {
          $deptIdeas = $allIdeas->filter(function($idea) use ($dept) {
              return $idea->session->department_id == $dept->id;
          });

          return [
              'name' => $dept->name,
              'stats' => [
                  'Inovasi (Digital)' => $deptIdeas->where('category', 'inovasi')->where('is_digital', 'digital')->count(),
                  'Inovasi (Bukan Digital)' => $deptIdeas->where('category', 'inovasi')->where('is_digital', 'non-digital')->count(),
                  'Selain Inovasi (Digital)' => $deptIdeas->where('category', 'selain_inovasi')->where('is_digital', 'digital')->count(),
                  'Selain Inovasi (Bukan Digital)' => $deptIdeas->where('category', 'selain_inovasi')->where('is_digital', 'non-digital')->count(),
              ],
              'total' => $deptIdeas->count()
          ];
      })->filter(fn($item) => $item['total'] > 0);
  },

'barChartData' => function() {
    $selectedMonth = request('month');
    $selectedYear = request('year', date('Y'));
    $selectedDeptId = request('dept_id');

    return \App\Models\Department::when($selectedDeptId, function($query) use ($selectedDeptId) {
            return $query->where('id', $selectedDeptId);
        })
        ->get()
        ->map(function($dept) use ($selectedMonth, $selectedYear) {

            $ideas = \App\Models\CoffeeBreakIdea::whereHas('session', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })
                ->whereYear('created_at', $selectedYear)
                ->when($selectedMonth, function($q) use ($selectedMonth) {
                    return $q->whereMonth('created_at', $selectedMonth);
                })
                ->get();

            return [
                'dept'           => $dept->code,
                'inovasi'        => $ideas->where('category', 'inovasi')->count(),
                'selain_inovasi' => $ideas->where('category', 'selain_inovasi')->count(),
                'total'          => $ideas->count()
            ];
        })
        ->filter(fn($item) => $item['total'] > 0)
        ->values();
},


    'departments' => fn() => \App\Models\Department::all(),

    'availableYears' => function() {
        $currentYear = date('Y');
        return range($currentYear, $currentYear - 2);
    }
]);

?>

<div class="p-8 max-w-7xl mx-auto min-h-screen bg-slate-50/50">
    {{-- Load Chart.js di sini --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Header --}}
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="" wire:navigate class="px-5 py-2.5 bg-white rounded-2xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition shadow-sm">
                &larr; Kembali
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Analisis Idea Coff-B</h2>
                <p class="text-sm text-slate-500 font-medium italic">Pecahan kategori idea mengikut setiap bahagian jabatan.</p>
            </div>
        </div>

        <button onclick="window.print()" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-200">
            Cetak Laporan
        </button>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-4 items-end bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 my-8">
        {{-- Filter Bahagian --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Pilih Bahagian</label>
            <select name="dept_id" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                 <option value="">Semua Bahagian</option>
                 @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('dept_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                 @endforeach
            </select>
        </div>

        {{-- Filter Bulan --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Pilih Bulan</label>
            <select name="month" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                <option value="">Keseluruhan Tahun</option>

                {{-- Separuh Tahun (BARU) --}}
                <optgroup label="Separuh Tahun (Half Year)">
                    <option value="h1" {{ request('month') == 'h1' ? 'selected' : '' }}>Separuh Pertama (Jan - Jun)</option>
                    <option value="h2" {{ request('month') == 'h2' ? 'selected' : '' }}>Separuh Kedua (Jul - Dis)</option>
                </optgroup>

                {{-- Suku Tahun --}}
                <optgroup label="Suku Tahun (Quarter)">
                    <option value="q1" ...>Suku Pertama (Q1)</option>
                    ...
                </optgroup>

                <optgroup label="Bulanan">
                  @foreach(range(1, 12) as $m)
                      @php $mVal = sprintf('%02d', $m); @endphp
                          <option value="{{ $mVal }}" {{ request('month') == $mVal ? 'selected' : '' }}>
                              {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                          </option>
                  @endforeach

               </optgroup>
          </select>
        </div>

        <div class="flex-1 min-w-[150px]">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Pilih Tahun</label>
            <select name="year" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
             <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-2xl text-sm font-bold hover:bg-black transition-all">
                Tapis
             </button>
             <a href="{{ url()->current() }}" class="bg-slate-100 text-slate-500 px-5 py-3 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all text-center">
                Reset
              </a>
        </div>
    </form>

    {{-- Grid Carta Pai --}}
    <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-8">
      @foreach($reportData as $index => $data)
          <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm min-h-[450px] flex flex-col">

              {{-- 1. Tajuk Bahagian --}}
              <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-widest mb-6 text-center border-b pb-4">
                  {{ $data['name'] }}
              </h4>

              {{-- 2. Ruang Carta --}}
              <div class="flex-1 relative flex items-center justify-center bg-slate-50/50 rounded-[2rem] p-4">
                  <div id="debug-{{ $index }}" class="absolute text-[10px] text-slate-400 font-bold uppercase animate-pulse">
                      Menunggu Carta...
                  </div>

                  {{-- Tingkatkan h-72 supaya ada ruang untuk legend kat bawah --}}
                  <div class="w-full h-72">
                      <canvas id="chart-{{ $index }}"></canvas>
                  </div>
              </div>

              <div class="mt-4 pt-4 border-t border-slate-50">
                  <div class="flex items-center justify-center gap-6"> {{-- Gunakan flex & justify-center --}}
                      {{-- Item Digital --}}
                      <div class="flex items-center gap-2">
                          <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">D</span>
                          <span class="text-[10px] text-slate-500 font-medium">Digital</span>
                      </div>

                      {{-- Item Bukan Digital --}}
                      <div class="flex items-center gap-2">
                          <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">BD</span>
                          <span class="text-[10px] text-slate-500 font-medium">Bukan Digital</span>
                      </div>
                  </div>
              </div>

              {{-- 3. Info Jumlah Idea kat bawah sekali --}}
              <div class="mt-4 text-center">
                  <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">
                      Jumlah: {{ $data['total'] }} Idea
                  </span>
              </div>

              {{-- 4. Letak Script "Chart Punya" kat sini --}}
              <script>
                  (function() {
                      const render = () => {
                          const canvas = document.getElementById('chart-{{ $index }}');
                          const debugText = document.getElementById('debug-{{ $index }}');
                          if (!canvas || typeof Chart === 'undefined') return;

                          const rawData = [
                              {{ (int)$data['stats']['Inovasi (Digital)'] }},
                              {{ (int)$data['stats']['Inovasi (Bukan Digital)'] }},
                              {{ (int)$data['stats']['Selain Inovasi (Digital)'] }},
                              {{ (int)$data['stats']['Selain Inovasi (Bukan Digital)'] }}
                          ];

                          if (rawData.every(val => val === 0)) {
                              if (debugText) debugText.innerText = 'Tiada Data';
                              return;
                          }

                          new Chart(canvas, {
                              type: 'pie',
                              data: {
                                  labels: ['Inovasi (D)', 'Inovasi (BD)', 'Selain Inovasi (D)', 'Selain Inovasi (BD)'],
                                  datasets: [{
                                      data: rawData,
                                      backgroundColor: ['#3b82f6', '#22d3ee', '#f97316', '#fbbf24'],
                                      borderWidth: 6,
                                      borderColor: '#ffffff',
                                      hoverOffset: 20
                                  }]
                              },
                              options: {
                                responsive: true,
                                    maintainAspectRatio: false,
                                    layout: { padding: { bottom: 10 } },
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'bottom',
                                            labels: {
                                              padding: 20,
                                              usePointStyle: true,
                                              pointStyle: 'circle',
                                              boxWidth: 10,
                                                  font: {
                                                      size: 14,
                                                      family: 'Poppins',
                                                      weight: '700'
                                                  },
                                               color: '#334155',
                                                // --- MODIFIKASI DI SINI ---
                                                generateLabels: function(chart) {
                                                    const data = chart.data;
                                                    if (data.labels.length && data.datasets.length) {
                                                        return data.labels.map((label, i) => {
                                                            const value = data.datasets[0].data[i];
                                                            return {
                                                                text: `${label}: ${value}`, // Papar "Nama: 10"
                                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                                strokeStyle: data.datasets[0].borderColor,
                                                                lineWidth: 1,
                                                                hidden: isNaN(data.datasets[0].data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                                                index: i
                                                            };
                                                        });
                                                    }
                                                    return [];
                                                }
                                                // --------------------------
                                            }
                                        },
                                        tooltip: {
                                            // ... kekalkan kod tooltip sedia ada anda
                                            backgroundColor: '#0f172a',
                                            padding: 12,
                                            cornerRadius: 12,
                                            callbacks: {
                                                label: function(context) {
                                                    let value = context.raw || 0;
                                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    let percentage = Math.round((value / total) * 100);
                                                    return ` ${context.label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                              }
                          });
                          if (debugText) debugText.style.display = 'none';
                      };
                      setTimeout(render, 500);
                  })();
              </script>
          </div>
      @endforeach
    </div>


    {{-- Section Bar Chart --}}
    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm mb-12 my-5">
        <div class="mb-8">
            <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Perbandingan Kategori Idea Mengikut Bahagian</h4>
            <p class="text-xs text-slate-400 font-medium italic">Analisis jumlah Inovasi berbanding Selain Inovasi bagi setiap bahagian.</p>
        </div>

        <div class="h-96 w-full">
            <canvas id="groupedBarChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm mt-8">
        <div class="mb-6 flex justify-between items-center px-2">
            <div>
                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Jadual Statistik Bahagian</h4>
                <p class="text-[10px] text-slate-400 font-medium italic">Data terperinci bagi tempoh yang dipilih</p>
            </div>
            <!--<div class="bg-slate-900 text-white px-4 py-2 rounded-2xl">
                <span class="text-[9px] block uppercase font-bold text-slate-400">Jumlah Keseluruhan</span>
                <span class="text-lg font-black leading-none">{{ collect($barChartData)->sum('total') }}</span>
            </div>-->
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-50">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Bahagian</th>
                        <th class="p-4 text-[10px] font-black text-blue-500 uppercase tracking-widest border-b border-slate-100 text-center">Idea Inovasi</th>
                        <th class="p-4 text-[10px] font-black text-orange-500 uppercase tracking-widest border-b border-slate-100 text-center">Selain Inovasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($barChartData as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4">
                                <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">{{ $row['dept'] }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] h-8 bg-blue-50 text-blue-600 rounded-lg text-sm font-black">
                                    {{ $row['inovasi'] }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] h-8 bg-orange-50 text-orange-600 rounded-lg text-sm font-black">
                                    {{ $row['selain_inovasi'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-slate-300 mb-2">Tiada data dijumpai untuk tempoh ini</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Footer untuk Total Per Column --}}
                @if(count($barChartData) > 0)
                <tfoot class="bg-slate-50/80 font-black">
                    <tr>
                        <td class="p-4 text-[10px] uppercase text-slate-500">Jumlah Keseluruhan</td>
                        <td class="p-4 text-center text-blue-600">{{ collect($barChartData)->sum('inovasi') }}</td>
                        <td class="p-4 text-center text-orange-600">{{ collect($barChartData)->sum('selain_inovasi') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('groupedBarChart');
            if (!ctx) return;

            const rawData = @json($barChartData);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: rawData.map(item => item.dept),
                    datasets: [
                        {
                            label: 'Inovasi',
                            data: rawData.map(item => item.inovasi),
                            backgroundColor: '#3b82f6', // Biru
                            borderRadius: 8,
                            barPercentage: 0.8,
                            categoryPercentage: 0.6
                        },
                        {
                            label: 'Selain Inovasi',
                            data: rawData.map(item => item.selain_inovasi),
                            backgroundColor: '#f97316', // Jingga
                            borderRadius: 8,
                            barPercentage: 0.8,
                            categoryPercentage: 0.6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { family: 'Poppins', size: 11, weight: '700' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 12,
                            titleFont: { family: 'Poppins', size: 13 },
                            bodyFont: { family: 'Poppins', size: 12 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { family: 'Poppins', weight: '600' },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Poppins', weight: '700', size: 10 },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        });
    </script>


    @if($reportData->isEmpty())
        <div class="py-20 flex flex-col items-center justify-center opacity-30">
            <div class="text-6xl mb-4">📊</div>
            <p class="font-black uppercase tracking-widest text-xs">Tiada data untuk dianalisis</p>
        </div>
    @endif

  </div>
