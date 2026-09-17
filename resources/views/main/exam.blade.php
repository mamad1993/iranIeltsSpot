
    <div class="container">

        <h2 class="mb-3">
            Exam #{{ $exam->id }}
        </h2>

        {{-- Passages --}}
        @foreach($exam->passages as $pIndex => $passage)
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Passage {{ $pIndex + 1 }}</strong>
                    <span class="text-muted"> (id: {{ $passage->id }})</span>
                </div>

                <div class="card-body">
                    {{-- Paragraphs --}}
                    <h5 class="mb-2">Paragraphs</h5>

                    @forelse($passage->paragraphs as $para)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Paragraph {{ $para->number }}</strong>
                                    <span class="text-muted"> (id: {{ $para->id }})</span>
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="mb-2">
                                    <div class="text-muted">English:</div>
                                    <div style="white-space: pre-wrap;">{{ $para->english }}</div>
                                </div>

                                <div>
                                    <div class="text-muted">Persian:</div>
                                    <div style="white-space: pre-wrap; direction: rtl;">{{ $para->persian }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No paragraphs.</div>
                    @endforelse

                    <hr>

                    {{-- Groups --}}
                    <h5 class="mb-2">Question Groups</h5>

                    @forelse($passage->groups as $gIndex => $group)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div>
                                        <strong>Group {{ $gIndex + 1 }}:</strong>
                                        {{ $group->title }}
                                        <span class="text-muted"> (id: {{ $group->id }})</span>
                                    </div>

                                    <div class="text-muted">
                                        type: <code>{{ $group->type }}</code>
                                    </div>

                                    @if(!empty($group->instructions))
                                        <div class="mt-2">
                                            <div class="text-muted">Instructions:</div>
                                            <div style="white-space: pre-wrap;">{{ $group->instructions }}</div>
                                        </div>
                                    @endif
                                </div>

                                {{-- quick debug counts --}}
                                <div class="text-end">
                                    <div class="small text-muted">
                                        group options: {{ $group->options->count() }}
                                    </div>
                                    <div class="small text-muted">
                                        questions: {{ $group->questions->count() }}
                                    </div>
                                </div>
                            </div>

                            {{-- Group-level options (for Matching types only) --}}
                            @if($group->options->count())
                                <div class="mt-3">
                                    <div class="text-muted">Group Options (Matching):</div>
                                    <ul class="mb-0">
                                        @foreach($group->options as $opt)
                                            <li>
                                                <strong>{{ $opt->key ?? '-' }}</strong>
                                                <span class="text-muted">(id: {{ $opt->id }})</span>
                                                : {{ $opt->text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Questions --}}
                            <div class="mt-3">
                                <div class="text-muted mb-1">Questions:</div>

                                @forelse($group->questions as $qIndex => $question)
                                    <div class="border rounded p-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Q{{ $question->number }}</strong>
                                                <span class="text-muted">(id: {{ $question->id }})</span>
                                            </div>
                                            <div class="small text-muted">
                                                question options: {{ $question->options->count() }}
                                            </div>
                                        </div>

                                        @if(!empty($question->text))
                                            <div class="mt-2" style="white-space: pre-wrap;">
                                                {{ $question->text }}
                                            </div>
                                        @endif

                                        {{-- answers (cast to array?) --}}
                                        @php
                                            $answers = $question->answers;
                                            // اگر answers تو دیتابیس json هست و cast نشده، ممکنه string باشه:
                                            if (is_string($answers)) {
                                                $decoded = json_decode($answers, true);
                                                if (json_last_error() === JSON_ERROR_NONE) $answers = $decoded;
                                            }
                                        @endphp

                                        @if(!empty($answers))
                                            <div class="mt-2">
                                                <span class="text-muted">Answers:</span>
                                                <code>
                                                    {{ is_array($answers) ? json_encode($answers, JSON_UNESCAPED_UNICODE) : $answers }}
                                                </code>
                                            </div>
                                        @endif

                                        {{-- Per-question options (MCQ / MC X FROM N) --}}
                                        @if($question->options->count())
                                            <div class="mt-2">
                                                <div class="text-muted">Question Options (MCQ):</div>
                                                <ol class="mb-0">
                                                    @foreach($question->options as $opt)
                                                        <li>
                                                            <strong>{{ $opt->key ?? '-' }}</strong>
                                                            <span class="text-muted">(id: {{ $opt->id }})</span>
                                                            : {{ $opt->text }}
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endif

                                    </div>
                                @empty
                                    <div class="text-muted">No questions in this group.</div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No groups.</div>
                    @endforelse

                </div>
            </div>
        @endforeach

    </div>


