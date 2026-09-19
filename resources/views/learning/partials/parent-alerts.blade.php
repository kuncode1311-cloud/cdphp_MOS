{{-- Phần nhận xét học tập, dùng mốc điểm trong config/learning.php. --}}
<!-- KHỐI 1: CON ĐANG HỌC THẾ NÀO? (ĐIỂM MẠNH & NỘI DUNG CẦN ÔN) -->
<div style="margin-bottom: 22px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 19px;">💡</span>
            <h3 style="margin: 0; font-size: 15px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                Con đang học thế nào?
            </h3>
        </div>
        <span style="font-size: 12px; font-weight: 800; color: #64748b;">
            Mốc đạt chuẩn IC3: {{ config('learning.pass_score', 700) }} / {{ config('learning.max_score', 1000) }} điểm
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
        
        <!-- CARD 1: KẾT QUẢ TỐT NHẤT HIỆN TẠI -->
        @php
            $passScore = config('learning.pass_score', 700);
            $str = $strengthsAndWeaknesses['strength'] ?? null;
            $strPassed = $str && ($str['avgScore'] >= $passScore);
        @endphp
        <div style="background: {{ $strPassed ? '#f0fdf4' : '#f8fafc' }}; border: 2px solid {{ $strPassed ? '#86efac' : '#cbd5e1' }}; border-radius: 18px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 11.5px; font-weight: 900; color: {{ $strPassed ? '#15803d' : '#475569' }}; text-transform: uppercase; letter-spacing: 0.5px;">
                    ⭐ {{ $strengthsAndWeaknesses['strengthLabel'] }}
                </span>
                @if($str)
                    <span style="background: {{ $strPassed ? '#dcfce7' : '#e2e8f0' }}; color: {{ $strPassed ? '#166534' : '#334155' }}; font-size: 11px; font-weight: 900; padding: 2px 8px; border-radius: 6px;">
                        Đúng {{ $str['accuracyRate'] }}%
                    </span>
                @endif
            </div>

            @if($str)
                <div style="font-size: 16px; font-weight: 1000; color: #0f172a; margin-bottom: 6px;">
                    {{ $str['name'] }}
                </div>
                <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px;">
                    <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 22px; font-weight: 700; color: {{ $strPassed ? '#15803d' : '#1e293b' }};">
                        {{ $str['avgScore'] }}
                    </span>
                    <span style="font-size: 12.5px; font-weight: 800; color: #64748b;">/ 1000 điểm</span>
                    <span style="font-size: 12px; font-weight: 750; color: #475569; margin-left: 6px;">
                        ({{ $str['totalCorrect'] }}/{{ $str['totalQuestions'] }} câu đúng · {{ $str['count'] }} lượt làm)
                    </span>
                </div>

                <div style="font-size: 12.5px; font-weight: 800; color: {{ $strPassed ? '#166534' : '#b45309' }}; line-height: 1.4;">
                    @if($strPassed)
                        ✓ Con đã vượt mốc đạt {{ $passScore }} điểm. Phụ huynh hãy khen ngợi để con thêm tự tin nhé!
                    @else
                        👉 Còn thiếu {{ max(0, $passScore - $str['avgScore']) }} điểm để đạt mốc {{ $passScore }}. Đây là chủ đề con làm tốt nhất hiện tại.
                    @endif
                </div>
            @else
                <p style="margin: 4px 0 0; font-size: 13px; color: #94a3b8; font-weight: 700;">
                    Chưa có đủ dữ liệu bài làm trong khoảng thời gian này.
                </p>
            @endif
        </div>

        <!-- CARD 2: NỘI DUNG NÊN ÔN THÊM -->
        @php
            $weak = $strengthsAndWeaknesses['weakness'] ?? null;
        @endphp
        <div style="background: #fffbeb; border: 2px solid #fde047; border-radius: 18px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 11.5px; font-weight: 900; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">
                    🌱 {{ $strengthsAndWeaknesses['weaknessLabel'] }}
                </span>
                @if($weak)
                    <span style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 900; padding: 2px 8px; border-radius: 6px;">
                        Đúng {{ $weak['accuracyRate'] }}%
                    </span>
                @endif
            </div>

            @if($weak)
                <div style="font-size: 16px; font-weight: 1000; color: #0f172a; margin-bottom: 6px;">
                    {{ $weak['name'] }}
                </div>
                <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px;">
                    <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 22px; font-weight: 700; color: #c2410c;">
                        {{ $weak['avgScore'] }}
                    </span>
                    <span style="font-size: 12.5px; font-weight: 800; color: #64748b;">/ 1000 điểm</span>
                    <span style="font-size: 12px; font-weight: 750; color: #475569; margin-left: 6px;">
                        ({{ $weak['totalCorrect'] }}/{{ $weak['totalQuestions'] }} câu đúng · {{ $weak['count'] }} lượt làm)
                    </span>
                </div>

                <div style="font-size: 12.5px; font-weight: 800; color: #9a3412; line-height: 1.4;">
                    👉 Còn thiếu {{ max(0, $passScore - $weak['avgScore']) }} điểm để đạt mốc {{ $passScore }}. Đây là kết quả thấp nhất trong khoảng thời gian đang xem.
                </div>
            @else
                <p style="margin: 4px 0 0; font-size: 13px; color: #94a3b8; font-weight: 700;">
                    Chưa ghi nhận nội dung nào cần hỗ trợ thêm.
                </p>
            @endif
        </div>

    </div>
</div>

<!-- KHỐI 2: VIỆC CẦN QUAN TÂM (THAY CHO BẢNG CẢNH BÁO BÁO ĐỘNG) -->
<div style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 19px;">📌</span>
            <h3 style="margin: 0; font-size: 15px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                Việc cần quan tâm
            </h3>
        </div>
        <span style="font-size: 12px; font-weight: 800; color: #64748b;">
            {{ count($careItems) }} nội dung nên cùng con củng cố
        </span>
    </div>

    @if(count($careItems) > 0)
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($careItems as $item)
                @php
                    $isLevel3 = $item['level'] === 3;
                    $isLevel2 = $item['level'] === 2;
                    $cardBg = $isLevel3 ? '#fef2f2' : ($isLevel2 ? '#fff7ed' : '#fefce8');
                    $cardBorder = $isLevel3 ? '#fecaca' : ($isLevel2 ? '#fed7aa' : '#fef08a');
                @endphp
                <div style="background: {{ $cardBg }}; border: 1.5px solid {{ $cardBorder }}; border-radius: 16px; padding: 14px 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 8px;">
                        
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="background: #ffffff; color: {{ $item['color'] }}; border: 1.5px solid {{ $cardBorder }}; font-size: 11px; font-weight: 900; padding: 2px 8px; border-radius: 8px;">
                                {{ $item['badge'] }}
                            </span>
                            <b style="font-size: 14.5px; color: #0f172a;">{{ $item['testName'] }}</b>
                            <span style="font-size: 12px; font-weight: 750; color: #64748b;">
                                ({{ $item['topicName'] }})
                            </span>
                        </div>

                        <!-- Điểm số và khoảng cách tới mốc đạt chuẩn -->
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 19px; font-weight: 700; color: {{ $item['color'] }};">
                                {{ $item['latestScore'] }}
                            </span>
                            <span style="font-size: 12px; font-weight: 800; color: #64748b;">/ {{ $item['maxScore'] }} điểm</span>
                            <span style="font-size: 11.5px; font-weight: 850; color: #b45309; margin-left: 4px;">
                                (Còn thiếu {{ $item['missingPoints'] }}đ để đạt mốc {{ $item['passScore'] }})
                            </span>
                        </div>
                    </div>

                    <!-- Lý do & Thông số -->
                    <div style="font-size: 13px; color: #334155; font-weight: 750; line-height: 1.5; margin-bottom: 8px;">
                        {{ $item['why'] }}
                    </div>

                    <!-- Hành động cụ thể -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 8px; border-top: 1px dashed rgba(0,0,0,0.1);">
                        <span style="font-size: 12px; font-weight: 800; color: #475569;">
                            👉 <b>Hành động gợi ý:</b> Phụ huynh nhắc con ôn lại từ vựng/kiến thức chủ đề và làm lại bài.
                        </span>
                        @if($item['actionUrl'])
                            <a href="{{ $item['actionUrl'] }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #ffffff; border: 1.5px solid {{ $cardBorder }}; border-radius: 8px; font-size: 12px; font-weight: 900; color: #0f172a; text-decoration: none; box-shadow: 0 1px 4px rgba(0,0,0,0.04); transition: transform 0.15s;">
                                <span>🚀 {{ $item['actionText'] }}</span>
                                <b>→</b>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 16px; padding: 18px; text-align: center;">
            <div style="font-size: 26px; margin-bottom: 4px;">🎉</div>
            <b style="color: #166534; font-size: 14.5px; display: block;">Rất tuyệt vời!</b>
            <span style="color: #15803d; font-size: 12.5px; font-weight: 750;">
                Tất cả các bài con đã hoàn thành trong khoảng thời gian này đều đã đạt mốc {{ config('learning.pass_score', 700) }} điểm trở lên.
            </span>
        </div>
    @endif

    <!-- Nhận xét về thời gian làm bài (dữ liệu khách quan, không suy diễn) -->
    @if(!empty($timingObservations))
        <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px;">
            @foreach($timingObservations as $obs)
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 9px 14px; font-size: 12px; color: #475569; font-weight: 750; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 15px;">⏱️</span>
                    <div>
                        <b>Lưu ý thời gian ({{ $obs['testName'] }}):</b> {{ $obs['note'] }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
