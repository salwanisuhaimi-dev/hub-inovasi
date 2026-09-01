<?php

use function Livewire\Volt\{layout, state, mount};
use App\Models\Program;

layout('layouts.app');

state([
    'program' => null,
    'submissionSlug' => '',
    'submissionId' => null,
]);

mount(function (Program $program, string $submission_slug, $submission = null) {
    $this->program = $program;
    $this->submissionSlug = $submission_slug;
    $this->submissionId = $submission;
});

?>

<div class="max-w-4xl mx-auto py-8 px-4">
    @if ($submissionSlug === 'submit-project')
        @livewire('pages.user.submissions.forms.submit-project', ['program' => $program, 'submissionId' => $submissionId])
    @elseif ($submissionSlug === 'confirm-attendance')
        @livewire('pages.user.submissions.forms.confirm-attendance', ['program' => $program, 'submissionId' => $submissionId])
    @elseif ($submissionSlug === 'take-quiz')
        @livewire('pages.user.submissions.forms.take-quiz', ['program' => $program, 'submissionId' => $submissionId])
    @else
        @livewire('pages.user.submissions.forms.submit-form', ['program' => $program, 'submissionId' => $submissionId])
    @endif
</div>
