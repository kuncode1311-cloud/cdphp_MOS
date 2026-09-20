{{-- Phần bảng lịch sử dùng lại khi tải trang và khi lọc bằng AJAX; attempts là các lượt làm cần hiển thị. --}}
@if($attempts->isEmpty())
    <div style="text-align: center; padding: 32px 16px; background: #f8fafc; border-radius: 14px; border: 2px dashed #cbd5e1;">
        <div style="font-size: 32px; margin-bottom: 6px;">📝</div>
        <h4 style="margin: 0 0 4px; font-size: 14px; color: #1e293b; font-weight: 850;">Chưa có lượt làm bài nào trong khoảng thời gian này</h4>
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 600;">Hãy chuyển bộ lọc sang "Tất cả thời gian" để xem toàn bộ lịch sử rèn luyện của con nhé!</p>
    </div>
@else
    {{-- Khung bảng Excel chuẩn mực: Viền bao ngoài sắc nét, kẻ ô rõ ràng, hàng so le (Zebra stripe), tương phản cao dễ đọc --}}
    <div style="overflow-x: auto; border: 2px solid #cbd5e1; border-radius: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); background: #ffffff;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px; min-width: 840px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            <thead>
                <tr style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 2.5px solid #94a3b8; color: #0f172a; font-size: 11.5px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 12px 8px; width: 44px; text-align: center; border-right: 1px solid #cbd5e1;">#</th>
                    <th style="padding: 12px 12px; text-align: left; border-right: 1px solid #cbd5e1;">Bài luyện</th>
                    <th style="padding: 12px 12px; text-align: left; border-right: 1px solid #cbd5e1;">Chủ đề</th>
                    <th style="padding: 12px 10px; width: 105px; text-align: center; border-right: 1px solid #cbd5e1;">Điểm số</th>
                    <th style="padding: 12px 10px; width: 85px; text-align: center; border-right: 1px solid #cbd5e1;">Mốc đạt</th>
                    <th style="padding: 12px 10px; width: 115px; text-align: center; border-right: 1px solid #cbd5e1;">Đúng / Tổng</th>
                    <th style="padding: 12px 10px; width: 125px; text-align: center; border-right: 1px solid #cbd5e1;">Thời gian</th>
                    <th style="padding: 12px 10px; width: 115px; text-align: center; border-right: 1px solid #cbd5e1;">Kết quả</th>
                    <th style="padding: 12px 10px; width: 110px; text-align: center;">Ngày làm</th>
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
                        $rowBg = ($index % 2 === 0) ? '#ffffff' : '#f8fafc';
                    @endphp
                    <tr class="history-table-row"
                        data-name="{{ mb_strtolower($att->practiceTest->name ?? ('Bài luyện ' . ($index + 1))) }}"
                        data-topic="{{ mb_strtolower($att->practiceTest?->topic?->name ?? 'Tổng hợp') }}"
                        data-topic-pos="{{ $att->practiceTest?->topic?->position ?? 0 }}"
                        data-score="{{ $att->score }}"
                        data-passed="{{ $isPassed ? '1' : '0' }}"
                        data-time="{{ $att->completed_at ? $att->completed_at->timestamp : 0 }}"
                        style="background: {{ $rowBg }}; border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#e0f2fe'"
                        onmouseout="this.style.background='{{ $rowBg }}'">
                        
                        <!-- Col 1: # (STT) -->
                        <td style="padding: 10px 6px; text-align: center; border-right: 1px solid #e2e8f0;">
                            <span class="row-index-badge" style="display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; padding: 0 4px; border-radius: 6px; background: #e2e8f0; color: #334155; font-weight: 850; font-size: 11.5px; border: 1px solid #cbd5e1;">
                                {{ $index + 1 }}
                            </span>
                        </td>

                        <!-- Col 2: Bài luyện -->
                        <td style="padding: 11px 12px; font-weight: 800; color: #0f172a; border-right: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $isPassed ? '#10b981' : '#ef4444' }}; flex-shrink: 0; box-shadow: 0 0 0 2px {{ $isPassed ? '#bbf7d0' : '#fecdd3' }};"></span>
                                <span>{{ $att->practiceTest->name ?? ('Bài luyện ' . ($index + 1)) }}</span>
                            </div>
                        </td>

                        <!-- Col 3: Chủ đề -->
                        <td style="padding: 11px 12px; border-right: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                @if($att->practiceTest?->topic?->position)
                                    <span style="display: inline-flex; align-items: center; justify-content: center; background: #e0f2fe; color: #0284c7; border: 1.2px solid #7dd3fc; padding: 2px 7px; border-radius: 6px; font-weight: 850; font-size: 11px; white-space: nowrap;">
                                        Chủ đề {{ $att->practiceTest->topic->position }}
                                    </span>
                                @endif
                                <span style="color: #1e293b; font-weight: 700; font-size: 11.5px;">{{ $att->practiceTest?->topic?->name ?? 'Tổng hợp' }}</span>
                            </div>
                        </td>

                        <!-- Col 4: Điểm số -->
                        <td style="padding: 10px; text-align: center; border-right: 1px solid #e2e8f0;">
                            <div style="display: inline-flex; align-items: baseline; gap: 2px;">
                                <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 15px; font-weight: 700; color: {{ $isPassed ? '#15803d' : '#dc2626' }};">
                                    {{ $att->score }}
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">
                                    /{{ $maxScore }}
                                </span>
                            </div>
                        </td>

                        <!-- Col 5: Mốc đạt -->
                        <td style="padding: 10px; text-align: center; border-right: 1px solid #e2e8f0;">
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-size: 11.5px; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1;">
                                {{ $passScore }}
                            </span>
                        </td>

                        <!-- Col 6: Đúng / Tổng -->
                        <td style="padding: 10px; text-align: center; border-right: 1px solid #e2e8f0;">
                            <span style="font-weight: 850; color: #0f172a; font-size: 12px;">{{ $att->correct_answers }}/{{ $att->total_questions }}</span>
                            <span style="font-size: 11px; font-weight: 750; color: {{ $accuracy >= 70 ? '#16a34a' : '#dc2626' }}; margin-left: 2px;">
                                ({{ $accuracy }}%)
                            </span>
                        </td>

                        <!-- Col 7: Thời gian -->
                        <td style="padding: 10px; text-align: center; color: #334155; font-weight: 700; font-size: 11.5px; border-right: 1px solid #e2e8f0;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="font-size: 12px;">⏱</span>
                                @if($att->duration_seconds < 60)
                                    <span>{{ $att->duration_seconds }} giây</span>
                                @else
                                    <span>{{ floor($att->duration_seconds / 60) }}p {{ $att->duration_seconds % 60 }}s</span>
                                @endif
                            </span>
                        </td>

                        <!-- Col 8: Kết quả -->
                        <td style="padding: 10px; text-align: center; border-right: 1px solid #e2e8f0;">
                            @if($isPassed)
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 850; color: #15803d; background: #dcfce7; border: 1.5px solid #86efac; box-shadow: 0 1px 3px rgba(22, 163, 74, 0.12); white-space: nowrap;">
                                    <span>✓</span>
                                    <span>Đạt chuẩn</span>
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 850; color: #b91c1c; background: #fee2e2; border: 1.5px solid #fca5a5; box-shadow: 0 1px 3px rgba(220, 38, 38, 0.12); white-space: nowrap;">
                                    <span>✕</span>
                                    <span>Chưa đạt</span>
                                </span>
                            @endif
                        </td>

                        <!-- Col 9: Ngày làm -->
                        <td style="padding: 8px 10px; text-align: center;">
                            <div style="color: #0f172a; font-size: 11.5px; font-weight: 750;">
                                {{ $att->completed_at ? $att->completed_at->setTimezone($tz)->format('d/m/Y') : 'N/A' }}
                            </div>
                            <div style="color: #64748b; font-size: 10.5px; font-weight: 650; margin-top: 1px;">
                                {{ $att->completed_at ? $att->completed_at->setTimezone($tz)->format('H:i') : '' }}
                            </div>
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
