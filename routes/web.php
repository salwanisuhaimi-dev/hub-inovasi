<?php

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use App\Livewire\Pages\User\SubmitProject;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Cookie;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Volt::route('/', 'pages.homepage')->name('home');
Volt::route('/archive', 'pages.archive')->name('archive');
Volt::route('/coff-b', 'pages.coff-b')->name('coff-b');
Volt::route('/quiz', 'pages.quiz')->name('quiz');
Volt::route('/entries', 'pages.entries')->name('entries');
Volt::route('/publication', 'pages.publication')->name('publication');
Volt::route('/pitch', 'pages.pitch')->name('pitch');

Volt::route('/testing', 'pages.testing')->name('testing');

Volt::route('/faq', 'pages.faq')->name('faq');
Volt::route('/info', 'pages.info')->name('info');
Volt::route('/overview/{competition:slug}', 'pages.overview')->name('overview');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Google Socialite)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', function () {

    if (request()->has('intended')) {
        session(['url.intended' => request()->query('intended')]);
    }
    return Socialite::driver('google')->redirect();
})->name('google.login');

Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')
              ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
              ->user();

        $user = User::updateOrCreate([
            'email' => $googleUser->email,
        ], [
            'name' => $googleUser->name,
            'google_id' => $googleUser->id,
            'password' => bcrypt(str()->random(24)),
        ]);

        Auth::login($user);

        $redirectTo = session()->pull('url.intended');

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    } catch (\Exception $e) {
        //return redirect('/login')->with('error', 'Gagal log masuk.');
        //dd($e->getMessage(), $e->getTraceAsString());
        dd([
            'Error Message' => $e->getMessage(),
            'Configured cURL Path' => ini_get('curl.cainfo'),
            'Configured OpenSSL Path' => ini_get('openssl.cafile'),
            'Does File Exist?' => file_exists(ini_get('curl.cainfo')) ? 'YES' : 'NO'
        ]);
    }
});

/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::view('profile', 'profile')->name('profile');

    Volt::route('/user/dashboard', 'pages.user.dashboard')
            ->name('user.dashboard');

    Route::middleware(['profile.complete'])->group(function () {

        Volt::route('/program/{program}/submit', 'pages.user.submit-project')
            ->name('project.submit');

        Volt::route('/my-submissions', 'pages.user.my-submissions')
            ->name('user.submissions');

        Volt::route('/edit-submission/{submission}', 'pages.user.edit-submissions')
            ->name('user.edit-submission');

        Volt::route('/coffb', 'pages.user.coffb-index')
            ->name('user.coffb');

        Volt::route('/coffb/{session}/ideas', 'pages.user.coffb-ideas-index')
            ->name('user.coffb.ideas');

        Volt::route('/coffb/{session}/ideas-print', 'pages.user.coffb-ideas-print')
            ->name('user.coffb.ideas-print');

        Volt::route('/pitches', 'pages.user.pitches-index')
            ->name('user.pitches');

    });

    Route::get('/re-route', function () {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    })->name('re-route');

});

/*
|--------------------------------------------------------------------------
| Admin Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Volt::route('/dashboard', 'pages.admin.dashboard')
        ->name('admin.dashboard');

    Volt::route('/users', 'pages.admin.users-index')
        ->name('admin.users');

    Volt::route('/submissions', 'pages.admin.submissions')
        ->name('admin.submissions');

    Volt::route('/upload', 'pages.admin.upload-inovasi')
        ->name('upload');

    Volt::route('/programs', 'pages.admin.program-index')
        ->name('admin.programs');

    Volt::route('/admin/program/{program}/submissions', 'pages.admin.program-submissions')
        ->name('admin.program.submissions');

    Volt::route('/quizzes', 'pages.admin.quiz-index')
        ->name('admin.quizzes');

    Volt::route('/archives', 'pages.admin.archive-index')
        ->name('admin.archives');

    Route::middleware(['profile.complete'])->group(function () {

        Volt::route('/coffb', 'pages.admin.coffb-index')
            ->name('admin.coffb');

        Volt::route('/coffb/{session}/ideas', 'pages.admin.coffb-ideas-index')
            ->name('admin.coffb.ideas');

        Volt::route('/coffb/report', 'pages.admin.report')
            ->name('admin.coffb.report');

        Volt::route('/publication', 'pages.admin.publication-index')
            ->name('admin.publication');

        Volt::route('/pitch', 'pages.admin.pitch-index')
            ->name('admin.pitch');



    });

    Volt::route('/manage-departments', 'pages.admin.settings.manage-departments')
        ->name('admin.settings.manage-departments');

    Volt::route('/manage-competitions', 'pages.admin.settings.manage-competitions')
        ->name('admin.settings.manage-competitions');

    Volt::route('/manage-programs', 'pages.admin.settings.manage-programs')
        ->name('admin.settings.manage-programs');

});

require __DIR__.'/auth.php';
