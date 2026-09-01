<?php

use App\Models\Quiz;
use App\Models\Program;
use App\Models\Submission;
use App\Models\QuizSubmission;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'program' => null,
    'programId' => null,
    'isPlayModalOpen' => false,
    'quizQuestions' => [],
    'quizTimeLimit' => 0,
    'quizType' => 'program',
]);

mount(function (Program $program = null, $programId = null) {
    $this->program = $program;
    $this->programId = $programId ?? $program?->id;

    if ($this->programId) {
        $this->quizQuestions = Quiz::query()
            ->where('quiz_type', 'program')
            ->where('program_id', $this->programId)
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->toArray();

        $this->quizTimeLimit = ($this->program && $this->program->time_limit)
            ? ($this->program->time_limit * 60)
            : (15 * 60);
    }
});

$startQuiz = function () {
    if (!auth()->check()) {
        $intendedUrl = urlencode(url()->current());
        return redirect()->to(route('login') . "?intended={$intendedUrl}");
    }

    if (count($this->quizQuestions) > 0) {
        $this->isPlayModalOpen = true;
    } else {
        session()->flash('error', 'Tiada soalan kuiz yang didaftarkan untuk program ini.');
    }
};

$submitScore = function ($score, $totalQuestions, $correctAnswers, $timeTaken) {
    $user = Auth::user();

    $creditsEarned = 3;

    $newSubmission = Submission::create([
        'program_id' => $this->programId,
        'user_id' => Auth::id(),
    ]);

    QuizSubmission::create([
        'submission_id' => $newSubmission->id,
        'total_questions' => $totalQuestions,
        'correct_answers' => $correctAnswers,
        'score' => $score,
        'time_taken' => $timeTaken,
    ]);

    if ($user) {
        $user->increment('credits', $creditsEarned);
    }

    $this->isPlayModalOpen = false;
    session()->flash('success', 'Rekod pencapaian kuiz anda telah berjaya disimpan!');
};
?>

<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-sm border border-gray-200">
    {{-- TAJUK & HEADER PROGRAM --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Penilaian Kuiz</h2>
            <div class="mt-2 flex items-center gap-2">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">
                    {{ $program->title ?? 'Program General' }}
                </span>
            </div>
        </div>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('user.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition duration-200 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- NOTIFIKASI FLASH --}}
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-center justify-between">
            <div><span class="font-bold">Ralat:</span> {{ session('error') }}</div>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg flex items-center justify-between">
            <div><span class="font-bold">Berjaya:</span> {{ session('success') }}</div>
        </div>
    @endif

    {{-- PAPARAN UTAMA SEBELUM MULA KUIZ --}}
    <div class="p-6 bg-gray-50 border border-gray-200 rounded-2xl mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Maklumat Kuiz Program</h3>
        <p class="text-sm text-gray-600 mb-4 leading-relaxed">
            Sila jawab semua soalan yang disediakan dalam had masa yang ditetapkan. Keputusan anda akan direkodkan ke dalam akaun sebaik sahaja kuiz diselesaikan.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-xs text-gray-400 font-bold block uppercase mb-1">Jumlah Soalan</span>
                <span class="text-lg font-black text-blue-600">{{ count($quizQuestions) }} Soalan</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-xs text-gray-400 font-bold block uppercase mb-1">Had Masa</span>
                <span class="text-lg font-black text-indigo-600">{{ floor($quizTimeLimit / 60) }} Minit</span>
            </div>
        </div>

        <button wire:click="startQuiz"
                @if(count($quizQuestions) === 0) disabled @endif
                class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-100 transition duration-200">
            Mula Kuiz Sekarang 🚀
        </button>
    </div>

    {{-- MODAL KUIZ INTERAKTIF --}}
    <div x-data="{
            questions: @entangle('quizQuestions'),
            currentIndex: 0,
            selectedAnswer: null,
            score: 0,
            correctCount: 0,
            shuffledOptions: ['a', 'b', 'c', 'd'],

            totalDuration: @entangle('quizTimeLimit'),
            countdown: 0,
            timeTaken: 0,
            globalInterval: null,
            quizFinished: false,

            init() {
                this.shuffleCurrentOptions();
                this.$watch('$wire.isPlayModalOpen', (value) => {
                    if (value) {
                        this.resetQuiz();
                        this.startTimer();
                    } else {
                        clearInterval(this.globalInterval);
                    }
                });
            },
            resetQuiz() {
                this.currentIndex = 0;
                this.selectedAnswer = null;
                this.score = 0;
                this.correctCount = 0;
                this.timeTaken = 0;
                this.countdown = this.$wire.quizTimeLimit;
                this.quizFinished = false;
                this.shuffleCurrentOptions();
            },
            shuffleCurrentOptions() {
                let keys = ['a', 'b', 'c', 'd'];
                for (let i = keys.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [keys[i], keys[j]] = [keys[j], keys[i]];
                }
                this.shuffledOptions = keys;
            },
            startTimer() {
                clearInterval(this.globalInterval);
                this.globalInterval = setInterval(() => {
                    if (!this.quizFinished) {
                        if (this.countdown > 0) {
                            this.countdown--;
                            this.timeTaken++;
                        } else {
                            clearInterval(this.globalInterval);
                            this.quizFinished = true;
                        }
                    }
                }, 1000);
            },
            formatTime(seconds) {
                let minutes = Math.floor(seconds / 60);
                let remainingSeconds = seconds % 60;
                return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            },
            selectOption(optKey) {
                if (this.selectedAnswer !== null) return;

                let currentQ = this.questions[this.currentIndex];
                if (!currentQ) return;

                this.selectedAnswer = String(optKey).trim().toUpperCase();
                let correctAnswer = String(currentQ.correct_answer || '').trim().toUpperCase();

                if (this.selectedAnswer === correctAnswer) {
                    this.score += 10;
                    this.correctCount++;
                }
            },
            nextQuestion() {
                this.selectedAnswer = null;
                if (this.currentIndex < this.questions.length - 1) {
                    this.currentIndex++;
                    this.shuffleCurrentOptions();
                } else {
                    clearInterval(this.globalInterval);
                    this.quizFinished = true;
                }
            }
         }"
         x-show="$wire.isPlayModalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-md"
         role="dialog"
         aria-modal="true">

        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl p-8 flex flex-col relative min-h-[500px]">

            <template x-if="!quizFinished && questions && questions.length > 0">
                <div class="flex-1 flex flex-col">
                    {{-- Header / Pemasa --}}
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-xs font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full uppercase tracking-wider">
                            Soalan <span x-text="currentIndex + 1"></span> daripada <span x-text="questions.length"></span>
                        </span>

                        <div class="flex items-center gap-2 text-base font-black" :class="countdown <= 60 ? 'text-red-500 animate-pulse' : 'text-gray-700'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="formatTime(countdown)"></span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-gray-100 h-2 rounded-full mb-8 overflow-hidden">
                        <div class="bg-blue-600 h-2 transition-all duration-500" :style="`width: ${((currentIndex + 1) / questions.length) * 100}%`"></div>
                    </div>

                    {{-- Soalan --}}
                    <div class="mb-8 flex-1">
                        <h2 class="text-xl font-black text-gray-900 leading-relaxed" x-text="questions[currentIndex]?.question"></h2>
                    </div>

                    {{-- Pilihan Jawapan Rawak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                        <template x-for="(optKey, idx) in shuffledOptions" :key="optKey">
                            <button @click="selectOption(optKey)"
                                    :disabled="selectedAnswer !== null"
                                    class="p-5 text-left border-2 rounded-2xl font-bold text-sm transition-all duration-200 flex items-center justify-between"
                                    :class="{
                                        'border-gray-200 hover:border-blue-500 hover:bg-blue-50/30 text-gray-700': selectedAnswer === null,

                                        'border-green-500 bg-green-50 text-green-700': selectedAnswer !== null && optKey.toUpperCase() === String(questions[currentIndex]?.correct_answer || '').trim().toUpperCase(),

                                        'border-red-500 bg-red-50 text-red-700': selectedAnswer !== null && selectedAnswer === optKey.toUpperCase() && optKey.toUpperCase() !== String(questions[currentIndex]?.correct_answer || '').trim().toUpperCase(),

                                        'border-gray-100 opacity-50 text-gray-400': selectedAnswer !== null && optKey.toUpperCase() !== String(questions[currentIndex]?.correct_answer || '').trim().toUpperCase() && selectedAnswer !== optKey.toUpperCase()
                                    }">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs flex items-center justify-center uppercase font-black"
                                          x-text="['A', 'B', 'C', 'D'][idx]"></span>
                                    <span x-text="questions[currentIndex] ? questions[currentIndex]['option_' + optKey] : ''"></span>
                                </div>

                                <template x-if="selectedAnswer !== null && optKey.toUpperCase() === String(questions[currentIndex]?.correct_answer || '').trim().toUpperCase()">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                            </button>
                        </template>
                    </div>

                    {{-- Fakta Menarik / Extras --}}
                    <div x-show="selectedAnswer !== null && questions[currentIndex]?.extras" class="p-4 bg-amber-50 rounded-2xl border border-amber-200/60 mb-6">
                        <span class="text-xs font-black text-amber-700 uppercase tracking-wider block mb-1">💡 Fakta Menarik:</span>
                        <p class="text-xs text-amber-800 font-medium leading-relaxed" x-text="questions[currentIndex]?.extras"></p>
                    </div>

                    {{-- Butang Seterusnya --}}
                    <div class="flex justify-end mt-auto">
                        <button @click="nextQuestion()"
                                :disabled="selectedAnswer === null"
                                class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-sm hover:bg-blue-700 transition disabled:opacity-50 shadow-lg shadow-blue-100">
                            <span x-text="currentIndex === questions.length - 1 ? 'Hantar Jawapan' : 'Soalan Seterusnya'"></span>
                        </button>
                    </div>
                </div>
            </template>

            {{-- Keputusan Kuiz --}}
            <template x-if="quizFinished">
                <div class="flex flex-col items-center justify-center text-center my-auto">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-6" :class="countdown === 0 ? 'bg-red-100' : 'bg-green-100'">
                        <span x-text="countdown === 0 ? '⏰' : '🎉'"></span>
                    </div>

                    <h2 class="text-2xl font-black text-gray-900 mb-2" x-text="countdown === 0 ? 'Masa Telah Tamat!' : 'Tahniah! Kuiz Selesai'"></h2>
                    <p class="text-sm text-gray-500 mb-6" x-text="countdown === 0 ? 'Masa yang diperuntukkan telah habis.' : 'Anda berjaya menamatkan kuiz sebelum had masa.'"></p>

                    <div class="flex gap-4 mb-8">
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Betul</span>
                            <span class="text-2xl font-black text-blue-600" x-text="correctCount + ' / ' + questions.length"></span>
                        </div>
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Masa Digunakan</span>
                            <span class="text-2xl font-black text-indigo-600" x-text="formatTime(timeTaken)"></span>
                        </div>
                    </div>

                    <button @click="$wire.submitScore(score, questions.length, correctCount, timeTaken)"
                            class="px-8 py-3.5 bg-gray-900 text-white font-black text-sm rounded-xl hover:bg-gray-800 transition w-full max-w-xs shadow-xl shadow-gray-200">
                        Hantar
                    </button>
                </div>
            </template>

        </div>
    </div>
</div>
