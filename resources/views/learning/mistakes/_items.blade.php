{{-- Danh sách câu sai (dùng cho lần tải đầu và các lần bấm "Xem thêm") --}}
            @foreach($mistakes as $mistake)
            @php
                $q = $mistake->question;
                $test = $mistake->practiceTest ?: $q->practiceTest;
                $topic = $test?->topic;
                $level = $topic?->level ?: $test?->level;
                $isResolved = $mistake->status === 'resolved';
                $isDanger = !$isResolved && $mistake->wrong_count >= 3;
                $isWarning = !$isResolved && $mistake->wrong_count == 2;
            @endphp
            <article class="mistake-item-row {{ $isResolved ? 'is-resolved' : ($isDanger ? 'is-danger' : ($isWarning ? 'is-warning' : 'is-normal')) }}">
                
                <!-- Cột Nội Dung: Metadata Ngắn Gọn + Chữ Câu Hỏi Đậm Nét Rõ Ràng Nhất -->
                <div class="mistake-content-col">
                    <div class="mistake-meta-bar">
                        @if($level)
                        <span class="meta-pill-grade">Khối {{ $level->grade }}</span>
                        @endif
                        
                        @if($topic)
                        <span class="meta-topic-text">{{ $topic->name }}</span>
                        @endif

                        @if(!$isResolved)
                            @if($isDanger)
                            <span class="risk-tag risk-tag-danger">🚨 Đã sai {{ $mistake->wrong_count }} lần</span>
                            @elseif($isWarning)
                            <span class="risk-tag risk-tag-warning">⚠️ Đã sai 2 lần</span>
                            @else
                            <span class="risk-tag risk-tag-normal">✏️ Sai 1 lần</span>
                            @endif
                        @else
                            <span class="risk-tag risk-tag-resolved">✓ Đã vượt qua</span>
                        @endif

                        <span class="meta-time-text">
                            🕒 {{ $mistake->last_wrong_at ? $mistake->last_wrong_at->diffForHumans() : 'Gần đây' }}
                        </span>
                    </div>

                    <!-- Tiêu đề câu hỏi to, sắc nét, tương phản tối đa, dễ nhìn nhất -->
                    <h2 class="q-title-text">{{ $q->title ?: '(Câu hỏi tương tác thực hành)' }}</h2>
                </div>

                <!-- Cột Thao Tác: 1 Nút Duy Nhất Cực Kỳ Dễ Bấm -->
                <div class="mistake-action-col">
                    @if(!$isResolved)
                    <a class="btn-revenge-main" href="{{ route('mistakes.launch', ['question_id' => $mistake->question_id]) }}">
                        <span>🔥</span> Phục thù
                    </a>
                    @else
                    <div class="resolved-action-group">
                        <span class="badge-resolved-success">
                            <i>✓</i> Đã giải quyết
                        </span>
                        <a class="btn-replay-link" href="{{ route('mistakes.launch', ['question_id' => $mistake->question_id]) }}">
                            Luyện lại
                        </a>
                    </div>
                    @endif
                </div>

            </article>
            @endforeach
