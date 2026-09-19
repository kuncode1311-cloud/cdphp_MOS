{{-- Phần bảng lịch sử dùng lại khi tải trang và khi lọc bằng AJAX; attempts là các lượt làm cần hiển thị. --}}
@if($attempts->isEmpty())
    <div style="text-align: center; padding: 28px 14px; background: #f8fafc; border-radius: 14px; border: 1.5px dashed #cbd5e1;">
        <div style="font-size: 28px; margin-bottom: 4px;">📝</div>
        <h4 style="margin: 0 0 2px; font-size: 13.5px; color: #334155; font-weight: 800;">Chưa có lượt làm bài nào trong khoảng thời gian này</h4>
        <p style="margin: 0; font-size: 11.5px; color: #64748b; font-weight: 600;">Hãy chuyển bộ lọc sang "Tất cả thời gian" để xem lịch sử nhé!</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="color: #64748b; font-size: 11px; font-weight: 800; text-align: left; border-bottom: 1.5px solid #f1f5f9;">
                    <th style="padding: 8px 10px; width: 32px; text-align: center;">#</th>
                    <th style="padding: 8px 10px;">Bài luyện</th>
                    <th style="padding: 8px 10px;">Chủ đề</th>
                    <th style="padding: 8px 10px; text-align: center;">Điểm số</th>
                    <th style="padding: 8px 10px; text-align: center;">Mốc đạt</th>
                    <th style="padding: 8px 10px; text-align: center;">Đúng / Tổng</th>
                    <th style="padding: 8px 10px; text-align: center;">Thời gian</th>
                    <th style="padding: 8px 10px; text-align: center;">Kết quả</th>
                    <th style="padding: 8px 10px; text-align: center;">Ngày làm</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attempts as $index => $att)
                    @php
                        $passScore = \App\Services\ParentLearningAnalyticsService::resolvePassScore($att->practiceTest);
                        $maxScore = \App\Services\ParentLearningAnalyticsService::resolveMaxScore($att->practiceTest);
                        $isPassed = $att->score >= $passScore;
                        $accuracy = $att->total_questions > 0 ? round(($att->correct_answers / $att->total_questions) * 100) : 0;
                        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
                    @endphp
                    <tr class="history-table-row"
                        data-name="{{ mb_strtolower($att->practiceTest->name ?? ('Bài luyện ' . ($index + 1))) }}"
                        data-topic="{{ mb_strtolower($att->practiceTest?->topic?->name ?? 'Tổng hợp') }}"
                        data-topic-pos="{{ $att->practiceTest?->topic?->position ?? 0 }}"
                        data-score="{{ $att->score }}"
                        data-passed="{{ $isPassed ? '1' : '0' }}"
                        data-time="{{ $att->completed_at ? $att->completed_at->timestamp : 0 }}"
                        style="border-bottom: 1px solid #f8fafc; transition: background 0.12s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        
                        <!-- Col 1: # -->
                        <td style="padding: 10px; text-align: center; color: #0f172a; font-weight: 800;">
                            {{ $index + 1 }}
                        </td>

                        <!-- Col 2: Bài luyện -->
                        <td style="padding: 11px 12px; font-weight: 800; color: #0f172a;">
                            <div style="display: flex; align-items: center; gap: 7px;">
                                <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: {{ $isPassed ? '#10b981' : '#f59e0b' }}; flex-shrink: 0;"></span>
                                <span>{{ $att->practiceTest->name ?? ('Bài luyện ' . ($index + 1)) }}</span>
                            </div>
                        </td>

                        <!-- Col 3: Chủ đề (Ghi rõ Chủ đề mấy) -->
                        <td style="padding: 11px 12px; color: #334155; font-weight: 700;">
                            @if($att->practiceTest?->topic?->position)
                                <span style="display: inline-flex; align-items: center; justify-content: center; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; padding: 2.5px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; margin-right: 6px;">
                                    Chủ đề {{ $att->practiceTest->topic->position }}
                                </span>
                            @endif
                            <span>{{ $att->practiceTest?->topic?->name ?? 'Tổng hợp' }}</span>
                        </td>

                        <!-- Col 4: Điểm số -->
                        <td style="padding: 10px; text-align: center; font-weight: 900; color: {{ $isPassed ? '#16a34a' : '#ea580c' }};">
                            {{ $att->score }}/{{ $maxScore }}
                        </td>

                        <!-- Col 5: Mốc đạt -->
                        <td style="padding: 10px; text-align: center; font-weight: 700; color: #64748b;">
                            {{ $passScore }}
                        </td>

                        <!-- Col 6: Đúng / Tổng -->
                        <td style="padding: 10px; text-align: center; font-weight: 650; color: #334155;">
                            {{ $att->correct_answers }}/{{ $att->total_questions }} ({{ $accuracy }}%)
                        </td>

                        <!-- Col 7: Thời gian -->
                        <td style="padding: 10px; text-align: center; color: #475569; font-weight: 650;">
                            @if($att->duration_seconds < 60)
                                {{ $att->duration_seconds }} giây
                            @else
                                {{ floor($att->duration_seconds / 60) }} phút {{ $att->duration_seconds % 60 }} giây
                            @endif
                        </td>

                        <!-- Col 8: Kết quả -->
                        <td style="padding: 10px; text-align: center;">
                            @if($isPassed)
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0;">
                                    Đạt chuẩn
                                </span>
                            @else
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; color: #dc2626; background: #fff1f2; border: 1px solid #fecdd3;">
                                    Chưa đạt
                                </span>
                            @endif
                        </td>

                        <!-- Col 9: Ngày làm -->
                        <td style="padding: 10px; text-align: center; color: #64748b; font-size: 11px; font-weight: 600;">
                            {{ $att->completed_at ? $att->completed_at->setTimezone($tz)->format('d/m/Y') : 'N/A' }}<br>
                            <span style="color: #94a3b8; font-size: 10px;">{{ $att->completed_at ? $att->completed_at->setTimezone($tz)->format('H:i') : '' }}</span>
                        </td>

                    </tr>
                @endforeach
                <tr id="tableNoFilterMatchRow" style="display: none;">
                    <td colspan="9" style="text-align: center; padding: 32px 15px; color: #64748b; font-weight: 650; font-size: 13px; background: #f8fafc;">
                        <div style="font-size: 24px; margin-bottom: 6px;">🔍</div>
                        <b style="color: #0f172a; font-size: 14px;">Không tìm thấy bài làm nào phù hợp</b>
                        <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">Hãy thử đổi từ khóa tìm kiếm hoặc chọn lại bộ lọc chủ đề / trạng thái nhé!</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
