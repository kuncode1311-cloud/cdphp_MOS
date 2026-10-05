{{-- Phân tích năng lực theo bộ lọc thời gian: chỉ dùng dữ liệu thật từ test_attempts --}}
    @php
        $passScore = config('learning.pass_score', 700);
        $weakness = $strengthsAndWeaknesses['weakness'] ?? null;
        $strength = $strengthsAndWeaknesses['strength'] ?? null;
    @endphp
    <div style="background: #ffffff; border-radius: 20px; padding: 20px 24px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 18px;">
        <div style="margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🧠</span>
                <h3 style="margin: 0; font-size: 15.5px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                    PHÂN TÍCH NĂNG LỰC & ĐỒNG HÀNH CÙNG CON
                </h3>
            </div>
            <small style="color: #64748b; font-weight: 650; font-size: 12px; display: block; margin-top: 2px;">
                Đánh giá chuyên sâu từng chủ đề IC3, phát huy thế mạnh nổi bật và hỗ trợ nội dung con cần ôn luyện
            </small>
        </div>

        <!-- 3 Cột Ngang Cực Kỳ Đẹp & Cân Đối -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
            
            @if($weakness)
            <!-- Khung 1: Điểm con cần quan tâm ôn thêm (Hồng phấn cảnh báo) -->
            <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #b91c1c; font-size: 13.5px; font-weight: 900;">
                            <span style="display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; color: #ffffff; font-size: 11px;">!</span>
                            <span>Cần ôn thêm: {{ $weakness['name'] }}</span>
                        </div>
                        <b style="color: #b91c1c; font-size: 15px; font-weight: 900;">{{ $weakness['avgScore'] }}/1000</b>
                    </div>

                    <div style="font-size: 12px; color: #475569; font-weight: 650; display: flex; flex-direction: column; gap: 4px; line-height: 1.4;">
                        <div>• Điểm thấp nhất trong các chủ đề con đã học</div>
                        <div>• Đúng {{ $weakness['totalCorrect'] }}/{{ $weakness['totalQuestions'] }} câu ({{ round($weakness['accuracyRate']) }}%), còn thiếu <b>{{ max(0, $passScore - $weakness['avgScore']) }} điểm</b> để đạt mốc 700</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 14px;">
                    <a href="{{ route('programs') }}" style="flex: 1; text-align: center; background: #2563eb; color: #ffffff; font-size: 11.5px; font-weight: 800; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                        <span>👁</span> Xem bài học
                    </a>
                    <a href="{{ route('programs') }}" style="flex: 1; text-align: center; background: #4f46e5; color: #ffffff; font-size: 11.5px; font-weight: 800; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);">
                        <span>🎮</span> Luyện tập lại
                    </a>
                </div>
            </div>

            @else
            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 16px; padding: 16px 18px; color: #64748b; font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; text-align: center;">
                Cần có bài làm ở ít nhất 2 chủ đề khác nhau trong {{ $timeRangeLabel }} để so sánh nội dung nên ôn thêm.
            </div>
            @endif

            @if($strength)
            <!-- Khung 2: Thế mạnh xuất sắc của con (Xanh lá vinh danh) -->
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #15803d; font-size: 13.5px; font-weight: 900;">
                            <span>🏆</span>
                            <span>Thế mạnh nổi bật: {{ $strength['name'] }}</span>
                        </div>
                        <b style="color: #15803d; font-size: 15px; font-weight: 900;">{{ $strength['avgScore'] }}/1000</b>
                    </div>
                    <div style="font-size: 12px; color: #334155; font-weight: 650; line-height: 1.45; display: flex; flex-direction: column; gap: 4px;">
                        <div>• Con đạt độ chính xác xuất sắc <b>{{ round($strength['accuracyRate']) }}%</b> ({{ $strength['totalCorrect'] }}/{{ $strength['totalQuestions'] }} câu đúng).</div>
                        <div>@if($strength['avgScore'] >= $passScore)• Điểm số vượt mốc chuẩn {{ $passScore }}đ IC3. Ba mẹ hãy khen ngợi con nhé!@else• Con đang tiến bộ, cần thêm {{ $passScore - $strength['avgScore'] }} điểm để chạm mốc chuẩn {{ $passScore }}đ. Ba mẹ hãy động viên con nhé!@endif</div>
                    </div>
                </div>
                <div style="margin-top: 14px; background: #ffffff; border: 1px solid #dcfce7; border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: #166534; font-weight: 750; text-align: center;">
                    🎉 Chủ đề đạt điểm cao nhất trong {{ $timeRangeLabel }}
                </div>
            </div>

            @else
            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 16px; padding: 16px 18px; color: #64748b; font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; text-align: center;">
                Con chưa có bài làm nào trong {{ $timeRangeLabel }}. Hãy nhắc con làm một bài luyện nhé!
            </div>
            @endif

            <!-- Khung 3: Lời khuyên đồng hành của giáo viên/hệ thống -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 900; color: #0f172a; font-size: 13.5px; margin-bottom: 8px;">
                        <span>💡</span>
                        <span>Gợi ý đồng hành cùng con</span>
                    </div>
                    <p style="margin: 0; font-size: 12px; color: #475569; font-weight: 650; line-height: 1.5;">
                        {{ $overviewInsight['conclusion'] ?? 'Duy trì thói quen 15 phút mỗi ngày sẽ giúp con tự tin đạt chứng chỉ Tin học Quốc tế IC3.' }}
                    </p>
                </div>
                <div style="margin-top: 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: #2563eb; font-weight: 750; text-align: center;">
                    ⭐ Chuẩn bị kiến thức vững vàng cho kỳ thi IC3 Spark
                </div>
            </div>

        </div>
    </div>
