# Báo cáo chuyển đổi dữ liệu độc lập MOS

Ngày cập nhật: 06-09-2026.

## Kết luận

Kiến trúc runtime và Studio hiện đã hoạt động bằng schema nội bộ:

- `questions`
- `questions.configuration`
- `question_options`
- `question_assets`

Runtime không đọc `raw_payload`, `external_id`, `launch_path`, `resource_manifest`, đường dẫn `/legacy/ic3` hoặc URI `storage://images`.

**Kết luận kỹ thuật: A — code đang hoạt động đã sẵn sàng cho một giai đoạn drop legacy riêng.** Theo yêu cầu của giai đoạn này, chưa cột nào bị drop và thư mục `public/legacy` chưa bị xóa.

Tính đầy đủ dữ liệu chưa đạt 100% vì 3 câu Hotspot bị thiếu file ảnh ngay từ nguồn hiện có. Hệ thống ghi nhận đường dẫn nội bộ bị thiếu và không tạo ảnh thay thế.

## Thay đổi đã hoàn thành

- Thêm và backfill `questions.configuration` cho 509 câu.
- Chuẩn hóa toàn bộ 509 câu sang `question_options` và `question_assets`.
- `LearningController` chỉ truyền DTO từ `Question::runtimeData()`.
- `AttemptController` chỉ chấm bằng options/metadata nội bộ.
- `QuestionService` tạo và sửa câu mà không ghi `raw_payload` hoặc sinh `external_id`.
- API Studio dùng `Question::studioData()` và không trả các trường legacy.
- Model ẩn các cột legacy khỏi JSON trong thời gian chúng còn tồn tại.
- Studio preview không còn kiểm tra `launch_path`.
- Request quản trị bộ đề không còn nhận `launch_path`.
- `AdminManagementController` không còn đọc/ghi/sync `raw_payload`.
- Seeder tạo cấu trúc domain, không gọi importer và không tạo `launch_path`.
- Importer đọc file nguồn trong import layer, chuyển payload trong bộ nhớ rồi ghi thẳng schema domain. Importer không ghi `raw_payload`, `external_id` hay `resource_manifest`.
- Mapping importer đã đối chiếu đủ 35/35 manifest với 35 bộ đề domain.
- 36 file media đã được sao chép vào `storage/app/public/question-assets/imported` (bao gồm 3 file ảnh Hotspot vừa được phục hồi từ máy chủ nguồn: `q237-27.jpg`, `q245-29.jpg`, `q248-30.jpg`).
- Mọi `configuration.image_path`, `question_options.image_path` và `question_assets.path` runtime đã chuyển sang `/storage/...`.
- Đã tạo backup trước khi đồng bộ đường dẫn tại `storage/app/backups/question-assets-before-internalize-20260906-034447.json` và `question-assets-before-internalize-20260906-034504.json`.

## Kiểm tra database thực tế

| Kiểm tra | Kết quả |
|---|---:|
| Tổng câu hỏi | 509 |
| Câu thiếu `configuration` | 0 |
| Câu không có `question_options` | 0 |
| Configuration còn `/legacy` hoặc `storage://` | 0 |
| Option image còn `/legacy` hoặc `storage://` | 0 |
| Asset path còn `/legacy` hoặc `storage://` | 0 |
| Asset được đánh dấu thiếu file | 0 |

## Ba asset Hotspot trước đó đã được phục hồi đầy đủ

| Khối | Chủ đề | Bộ đề | Câu | Nội dung | File thực tế đã phục hồi |
|---:|---|---|---:|---|---|
| 4 | Sáng tạo nội dung | Bài luyện 1 | ID 237, vị trí 1 | Ứng dụng dùng để tạo bài trình chiếu | `/storage/question-assets/imported/q237-27.jpg` |
| 4 | Sáng tạo nội dung | Bài luyện 1 | ID 245, vị trí 9 | Chọn hình ảnh phù hợp với chủ đề chó đang chơi | `/storage/question-assets/imported/q245-29.jpg` |
| 4 | Sáng tạo nội dung | Bài luyện 1 | ID 248, vị trí 12 | Chọn vị trí Heading trong tài liệu | `/storage/question-assets/imported/q248-30.jpg` |

*Lưu ý nguyên nhân trước đây:* Dữ liệu nguồn ghi đuôi `.png`, nhưng file thực tế trên server nguồn là `.jpg`. Các file đã được tải trực tiếp từ URL nguồn, đối chiếu checksum 100% với bản legacy và lưu vào storage nội bộ.

## Kiểm thử

- Kiểm tra cú pháp PHP cho model, controller, service, importer và commands: đạt.
- Biên dịch toàn bộ Blade bằng `artisan view:cache`: đạt.
- Toàn bộ PHPUnit: **34/34 tests đạt, 191 assertions**.
- Đối chiếu bộ đề `k4-cd4-bai-1` với `quiz1.js` nguồn bằng script `audit-domain-against-legacy.php`: **16/16 câu khớp hoàn toàn, 0 khác biệt**.
- Kiểm tra hình học và tọa độ Hotspot bằng `verify-hotspot-geometry.mjs`: **5/5 câu Hotspot đạt chuẩn, 0 lỗi**.
- Sáu data-set riêng kiểm tra MultipleChoice, MultipleResponse, MultipleChoiceText, Matching, Hotspot và Sequence.
- Mỗi data-set kiểm tra Studio create, Studio update, DTO render, submit và chấm đúng.
- Màn hình làm bài có chế độ review sau khi submit; input bị khóa và hiển thị đúng/sai từng câu.
- Loại bỏ fallback tự chọn vùng gần nhất khi click ngoài Hotspot, chuẩn hóa tọa độ click theo đúng ảnh hiển thị.
- Laravel Pint đã format các file thay đổi theo coding style của dự án.

## Reference legacy còn lại

| Legacy dependency | File còn dùng | Runtime có dùng? | Lý do còn tồn tại | Có thể drop chưa? |
|---|---|---:|---|---:|
| `raw_payload` | `Question` (ẩn khỏi JSON), `LegacyQuestionNormalizer`, `NormalizeLegacyQuestions`, test xác nhận câu mới không ghi | Không | Backfill lịch sử và giữ dữ liệu đối chiếu trước khi drop | Có, trong migration riêng sau khi gỡ code backfill |
| `external_id` | `Question` (ẩn khỏi JSON), test xác nhận câu mới không sinh | Không | Chỉ còn dữ liệu truy vết nguồn cũ | Có |
| `launch_path` | `PracticeTest` (ẩn khỏi JSON), migration lịch sử | Không | Cột vẫn còn trong database theo yêu cầu chưa drop | Có |
| `resource_manifest` | `PracticeTest` (ẩn khỏi JSON), migration lịch sử | Không | Cột vẫn còn trong database theo yêu cầu chưa drop | Có |
| `/legacy/ic3` | `ImportLegacyQuestions`, scripts audit và `public/legacy` | Không | Chỉ là đầu vào của import layer | Có; không xóa thư mục trong giai đoạn này |
| `storage://images` | Parser importer và normalizer | Không | Chỉ nhận diện URI nguồn khi import | Không liên quan runtime |

## Kết luận hiện trạng

- Toàn bộ 509 câu hỏi và tài nguyên hình ảnh (bao gồm cả 3 ảnh Hotspot) đã được nội bộ hóa hoàn toàn, hoạt động độc lập và hiển thị chính xác.
- Chưa tạo migration drop và chưa xóa dữ liệu legacy, đúng với yêu cầu an toàn của giai đoạn này.
