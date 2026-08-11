<?php

use function Livewire\Volt\{layout, state, mount};
use App\Models\Program;

layout('layouts.app');


state([
    'program' => null,
    'submissionSlug' => '',
]);

mount(function (Program $program, string $submission_slug) {
    $this->program = $program;
    $this->submissionSlug = $submission_slug;
});

?>

<div class="max-w-4xl mx-auto py-8 px-4">
    @if ($submissionSlug === 'submit-project')
        @livewire('pages.user.submissions.forms.submit-project', ['program' => $program])
    @elseif ($submissionSlug === 'confirm-attendance')
        @livewire('pages.user.submissions.forms.confirm-attendance', ['program' => $program])
    @endif
</div>
