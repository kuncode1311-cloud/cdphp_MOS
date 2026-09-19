{{-- Giao diện quản lý cũ. Route quản lý hiện chuyển sang dashboard hoặc studio trong AdminManagementController::index(). --}}
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Quản trị dữ liệu IC3</title>
<style>
:root{--navy:#172554;--blue:#4f5eed;--line:#dce5f5;--muted:#7180a4;--bg:#f3f7ff}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--navy);font:15px Segoe UI,Arial}.shell{min-height:100vh;display:grid;grid-template-columns:270px minmax(0,1fr)}.side{position:sticky;top:0;height:100vh;padding:28px 22px;background:linear-gradient(180deg,#30469d,#18215f);color:#fff;display:flex;flex-direction:column}.brand{font-size:28px;font-weight:900;margin:8px 8px 30px}.brand span{color:#55d7ff}.admin{display:flex;gap:12px;align-items:center;padding:16px;border:1px solid #ffffff2b;background:#ffffff0c;border-radius:20px;margin-bottom:28px}.avatar{width:52px;height:52px;border-radius:16px;display:grid;place-items:center;background:linear-gradient(135deg,#ffb43b,#ff667d);font-weight:900}.admin small{display:block;color:#bdc8ef;margin-top:4px}.label{padding:8px 12px;color:#91a1dd;font-size:11px;font-weight:900;letter-spacing:1px}.menu{display:grid;gap:7px}.menu a{padding:14px;border-radius:12px;color:#fff;text-decoration:none;font-weight:800}.menu a:hover,.menu a.active{background:linear-gradient(135deg,#427cff,#7950ee);box-shadow:0 8px 20px #11194e66}.logout{margin-top:auto}.logout button{width:100%;background:#ffffff10;border:1px solid #ffffff40}.main{min-width:0}.top{height:88px;padding:0 32px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10}.top h1{font-size:25px;margin:0}.top p{margin:4px 0 0;color:var(--muted)}.wrap{width:min(1460px,calc(100% - 48px));margin:24px auto}.alert{padding:13px 16px;border-radius:12px;margin-bottom:16px}.ok{background:#dcfce7;color:#166534}.err{background:#fee2e2;color:#991b1b}.card{background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 12px 35px #233d7310}.hero{padding:22px 24px;margin-bottom:18px}.hero-row{display:flex;align-items:center;justify-content:space-between;gap:18px}.hero h2{margin:0 0 5px;font-size:24px}.muted{color:var(--muted);font-size:13px}.badge{display:inline-flex;padding:6px 10px;border-radius:999px;background:#edf1ff;color:#4f46e5;font-weight:800;font-size:12px}.grades{display:flex;gap:10px;margin-top:18px}.grade{flex:1;min-width:130px;padding:16px;border:2px solid transparent;border-radius:15px;background:#f5f7ff;color:var(--navy);text-decoration:none}.grade strong{display:block;font-size:18px}.grade span{color:var(--muted);font-size:12px}.grade.active{border-color:#5571f6;background:linear-gradient(135deg,#ecf3ff,#f3edff)}.workspace{display:grid;grid-template-columns:265px 300px minmax(0,1fr);gap:16px;align-items:start}.panel{overflow:hidden}.panel-head{padding:17px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}.panel-head h3{margin:0;font-size:17px}.list{padding:8px}.item{display:block;padding:13px 12px;border-radius:12px;color:var(--navy);text-decoration:none;margin:3px 0;border:1px solid transparent}.item strong{display:block}.item small{color:var(--muted)}.item:hover,.item.active{background:#eef2ff;border-color:#d5ddff}.item.active{box-shadow:inset 4px 0 #5c5cf1}.empty{padding:24px;color:var(--muted);text-align:center}.content-head{padding:20px 22px;border-bottom:1px solid var(--line)}.content-head h2{margin:8px 0 5px;font-size:23px}.stats{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.stat{padding:7px 10px;background:#f4f7fd;border-radius:9px;font-size:12px}.questions{padding:10px 14px 18px}.question{display:grid;grid-template-columns:45px minmax(0,1fr) auto;gap:12px;align-items:center;padding:13px 8px;border-bottom:1px solid #edf1f8}.num{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:#edf1ff;color:#4f46e5;font-weight:900}.qtitle{font-weight:750;line-height:1.35}.qmeta{color:var(--muted);font-size:12px;margin-top:4px}.actions{display:flex;gap:6px;align-items:center}.btn,button{border:0;border-radius:9px;padding:9px 12px;background:#5048e5;color:#fff;text-decoration:none;font:inherit;font-weight:800;cursor:pointer}.btn.light{background:#edf1ff;color:#4338ca}.danger{background:#e92931}.addbox{border-top:1px solid var(--line);padding:16px 20px;background:#fbfcff}details summary{cursor:pointer;font-weight:850;color:#4338ca}.form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:14px}.form .full{grid-column:1/-1}input,select,textarea{width:100%;border:1px solid #cdd9ec;border-radius:10px;padding:10px 11px;font:inherit;background:#fff}textarea{min-height:92px}.manage-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.manage{padding:20px}.row{display:grid;grid-template-columns:1fr 1fr auto;gap:8px;padding:9px 0;border-top:1px solid #edf1f8}.count{padding:3px 8px;border-radius:99px;background:#e8edff;font-size:12px}form{margin:0}
@media(max-width:1100px){.workspace{grid-template-columns:220px minmax(0,1fr)}.workspace>.panel:nth-child(2){grid-column:1}.workspace>.panel:nth-child(3){grid-column:2;grid-row:1/span 2}.manage-grid{grid-template-columns:1fr}}@media(max-width:760px){.shell{display:block}.side{position:relative;height:auto;padding:10px}.brand,.admin,.label,.logout{display:none}.menu{display:flex;overflow:auto}.menu a{white-space:nowrap;padding:11px}.top{height:auto;padding:16px}.top p{display:none}.wrap{width:calc(100% - 22px);margin:12px auto}.hero{padding:16px}.grades{overflow:auto}.grade{flex:0 0 145px}.workspace{display:block}.panel{margin-bottom:12px}.question{grid-template-columns:38px minmax(0,1fr)}.question .actions{grid-column:2}.form,.manage-grid{grid-template-columns:1fr}.form .full{grid-column:auto}.row{grid-template-columns:1fr}}
</style></head><body>
@php $section=request('section','bank');$typeNames=['MultipleChoice'=>'Chọn một đáp án','MultipleResponse'=>'Chọn nhiều đáp án','Matching'=>'Ghép nối','MultipleChoiceText'=>'Chọn đáp án văn bản','Hotspot'=>'Nhấp vào hình','Sequence'=>'Sắp xếp thứ tự']; @endphp
<div class="shell"><aside class="side"><div class="brand"><span>IC3</span> QUEST</div><div class="admin"><span class="avatar">QT</span><div><b>{{ auth()->user()->name }}</b><small>Trung tâm điều hành</small></div></div><div class="label">ĐIỀU HÀNH</div><nav class="menu"><a href="{{ route('admin.dashboard') }}">🏠 Tổng quan</a><a class="{{ $section==='people'?'active':'' }}" href="{{ route('admin.management',['section'=>'people']) }}">🏫 Người dùng & lớp</a><a class="{{ $section==='bank'?'active':'' }}" href="{{ route('admin.questions.studio') }}">📚 Bộ đề & câu hỏi</a><a class="{{ $section==='settings'?'active':'' }}" href="{{ route('admin.management',['section'=>'settings']) }}">⚙️ Cấu trúc IC3</a><a href="{{ route('admin.dashboard') }}#ket-qua">📊 Kết quả học tập</a></nav><form class="logout" method="post" action="{{ route('logout') }}">@csrf<button>↪ Đăng xuất</button></form></aside>
<div class="main"><header class="top"><div><h1>{{ $section==='bank'?'Bộ đề & câu hỏi':($section==='people'?'Người dùng & lớp học':'Cấu trúc chương trình') }}</h1><p>{{ $section==='bank'?'Đi từ Khối → Chủ đề → Test → Câu hỏi':'Quản lý dữ liệu đúng nhóm, không trộn lẫn' }}</p></div><span class="badge">⭐ IC3 GS6</span></header><main class="wrap">
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif @if($errors->any())<div class="alert err">@foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach</div>@endif
@if($section==='bank')
<section class="card hero"><div class="hero-row"><div><span class="badge">THƯ VIỆN ĐỀ THI</span><h2>Chọn khối đang quản lý</h2><div class="muted">Chỉ hiển thị dữ liệu thuộc đúng cấp đang chọn.</div></div><div class="badge">{{ $tests->sum('questions_count') }} câu · {{ $tests->count() }} test</div></div><div class="grades">@foreach($levels as $level)<a class="grade {{ $selectedLevel?->id===$level->id?'active':'' }}" href="{{ route('admin.management',['grade'=>$level->grade]) }}"><strong>Khối {{ $level->grade }}</strong><span>{{ $level->topics_count }} chủ đề · {{ $level->topics->sum(fn($t)=>$t->tests->count()) }} test</span></a>@endforeach</div></section>
<div class="workspace"><section class="card panel"><div class="panel-head"><h3>1. Chủ đề</h3><span class="count">{{ $selectedLevel?->topics->count() }}</span></div><div class="list">@forelse($selectedLevel?->topics??[] as $topic)<a class="item {{ $selectedTopic?->id===$topic->id?'active':'' }}" href="{{ route('admin.management',['grade'=>$selectedLevel->grade,'topic'=>$topic->id]) }}"><strong>{{ $topic->name }}</strong><small>{{ $topic->tests->count() }} test</small></a>@empty<div class="empty">Chưa có chủ đề</div>@endforelse</div><div class="addbox"><details><summary>＋ Thêm chủ đề</summary><form class="form" method="post" action="{{ route('admin.topics.store') }}">@csrf<input type="hidden" name="level_id" value="{{ $selectedLevel?->id }}"><input class="full" name="name" placeholder="Tên chủ đề" required><input name="slug" placeholder="Slug tự động"><input name="icon" value="sparkles"><input name="position" type="number" value="{{ $selectedLevel?->topics->count() }}"><button>Thêm chủ đề</button></form></details></div></section>
<section class="card panel"><div class="panel-head"><h3>2. Test</h3><span class="count">{{ $selectedTopic?->tests->count() }}</span></div><div class="list">@forelse($selectedTopic?->tests??[] as $test)<a class="item {{ $selectedTest?->id===$test->id?'active':'' }}" href="{{ route('admin.management',['grade'=>$selectedLevel->grade,'topic'=>$selectedTopic->id,'test'=>$test->id]) }}"><strong>{{ $test->name }}</strong><small>{{ $test->question_count }} câu · {{ $test->difficulty }}</small></a>@empty<div class="empty">Chọn hoặc thêm chủ đề</div>@endforelse</div>@if($selectedTopic)<div class="addbox"><details><summary>＋ Thêm test</summary><form class="form" method="post" action="{{ route('admin.tests.store') }}">@csrf<input type="hidden" name="topic_id" value="{{ $selectedTopic->id }}"><input class="full" name="name" placeholder="Tên test" required><input name="slug" placeholder="Slug tự động"><select name="difficulty"><option>Cơ bản</option><option>Trung bình</option><option>Nâng cao</option></select><input type="hidden" name="is_published" value="1"><button class="full">Thêm test</button></form></details></div>@endif</section>
<section class="card panel">@if($selectedTest)<div class="content-head"><span class="badge">3. CÂU HỎI</span><h2>{{ $selectedTest->name }}</h2><div class="muted">Khối {{ $selectedLevel->grade }} › {{ $selectedTopic->name }}</div><div class="stats"><span class="stat"><b>{{ $selectedTest->questions->count() }}</b> câu</span>@foreach($selectedTest->questions->groupBy('type') as $type=>$items)<span class="stat">{{ $typeNames[$type]??$type }}: <b>{{ $items->count() }}</b></span>@endforeach</div></div><div class="questions">@forelse($selectedTest->questions->sortBy('position') as $q)<div class="question"><span class="num">{{ $q->position+1 }}</span><div><div class="qtitle">{{ $q->title ?: '(Câu tương tác không có tiêu đề)' }}</div><div class="qmeta">{{ $typeNames[$q->type]??$q->type }} · {{ $q->options->count() }} lựa chọn · {{ $q->assets->count() }} tài nguyên</div></div><div class="actions"><a class="btn light" href="{{ route('admin.questions.studio', ['grade'=>$selectedLevel->grade, 'topic'=>$selectedTopic->id, 'test'=>$selectedTest->id, 'q'=>$q->id]) }}">Biên tập Studio</a><form method="post" action="{{ route('admin.questions.destroy',$q) }}" onsubmit="return confirm('Xóa câu hỏi này?')">@csrf @method('delete')<button class="danger">Xóa</button></form></div></div>@empty<div class="empty">Test này chưa có câu hỏi.</div>@endforelse</div><div class="addbox"><details><summary>＋ Thêm câu hỏi vào {{ $selectedTest->name }}</summary><form class="form" method="post" action="{{ route('admin.questions.store') }}">@csrf<input type="hidden" name="practice_test_id" value="{{ $selectedTest->id }}"><input class="full" name="title" placeholder="Nội dung câu hỏi"><select name="type">@foreach($typeNames as $key=>$name)<option value="{{ $key }}">{{ $name }}</option>@endforeach</select><input name="position" type="number" value="{{ $selectedTest->questions->count() }}"><input type="hidden" name="is_published" value="1"><button class="full">Thêm câu hỏi</button></form></details></div>@else<div class="empty">Hãy chọn một test để xem bộ câu hỏi.</div>@endif</section></div>
@elseif($section==='people')
<div class="manage-grid">
    <section class="card manage">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="margin:0;">Người dùng <span class="count">{{ $users->count() }}</span></h2>
        </div>
        <details style="margin-bottom:16px;">
            <summary>＋ Tạo tài khoản (Học sinh / Giáo viên / Admin)</summary>
            <form class="form" method="post" action="{{ route('admin.users.store') }}">
                @csrf
                <input name="name" placeholder="Họ và tên" required>
                <input name="email" type="email" placeholder="Email đăng nhập" required>
                <input name="student_code" placeholder="Mã học sinh (nếu có)">
                <input name="password" placeholder="Mật khẩu khởi tạo" required>
                <select name="role">
                    <option value="student">👨‍🎓 Học sinh</option>
                    <option value="teacher">👩‍🏫 Giáo viên</option>
                    <option value="admin">👨‍💼 Quản trị viên</option>
                </select>
                <select name="classroom_id">
                    <option value="">-- Thuộc lớp (nếu là Học sinh) --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Khối {{ $c->grade }})</option>
                    @endforeach
                </select>
                <button class="full">Tạo tài khoản</button>
            </form>
        </details>
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($users as $user)
            <form class="row" method="post" action="{{ route('admin.users.update', $user) }}" style="grid-template-columns:1fr 1fr 110px auto;gap:6px;align-items:center;">
                @csrf
                @method('put')
                <input name="name" value="{{ $user->name }}" title="Họ và tên">
                <input name="email" value="{{ $user->email }}" title="Email">
                <select name="role" style="font-size:12px;padding:6px 4px;">
                    <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Học sinh</option>
                    <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <div class="actions">
                    <input type="hidden" name="student_code" value="{{ $user->student_code }}">
                    <input type="hidden" name="classroom_id" value="{{ $user->classroom_id }}">
                    <button style="padding:6px 10px;font-size:12px;">Lưu</button>
                </div>
            </form>
            @endforeach
        </div>
    </section>

    <section class="card manage">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="margin:0;">Lớp học & Giáo viên phụ trách <span class="count">{{ $classrooms->count() }}</span></h2>
        </div>
        <details style="margin-bottom:16px;">
            <summary>＋ Tạo lớp học mới</summary>
            <form class="form" method="post" action="{{ route('admin.classes.store') }}">
                @csrf
                <input name="name" placeholder="Tên lớp (ví dụ: Lớp 3A1)" required>
                <input name="grade" type="number" min="3" max="5" placeholder="Khối (3, 4, 5)" required>
                <input name="school_year" placeholder="Niên khóa (2026-2027)" value="2026-2027" required>
                <select name="teacher_id">
                    <option value="">-- Phân công Giáo viên --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->role === 'admin' ? 'Admin' : 'Giáo viên' }})</option>
                    @endforeach
                </select>
                <button class="full">Tạo lớp học</button>
            </form>
        </details>
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($classrooms as $class)
            <form class="row" method="post" action="{{ route('admin.classrooms.update', $class) }}" style="grid-template-columns:100px 70px 1fr auto;gap:6px;align-items:center;">
                @csrf
                @method('put')
                <input name="name" value="{{ $class->name }}" title="Tên lớp">
                <input name="grade" type="number" min="3" max="5" value="{{ $class->grade }}" title="Khối">
                <select name="teacher_id" style="font-size:12px;padding:6px 4px;" title="Giáo viên phụ trách">
                    <option value="">-- Chưa gán GV --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $class->teacher_id == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->role === 'admin' ? 'Admin' : 'GV' }})
                        </option>
                    @endforeach
                </select>
                <div class="actions">
                    <input type="hidden" name="school_year" value="{{ $class->school_year }}">
                    <span class="muted" style="font-size:11px;">{{ $class->students_count }} HS</span>
                    <button style="padding:6px 10px;font-size:12px;">Lưu</button>
                </div>
            </form>
            @endforeach
        </div>
    </section>
</div>
@else
<section class="card manage">
    <h2>Cấu trúc chương trình & Khối lớp IC3</h2>
    <p class="muted">Khu vực kỹ thuật quản trị chương trình và các khối/cấp độ đào tạo.</p>
    <div class="manage-grid">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 style="margin:0;">Chương trình đào tạo</h3>
                <span class="count">{{ $programs->count() }}</span>
            </div>
            <details style="margin-bottom:14px;">
                <summary>＋ Thêm chương trình mới</summary>
                <form class="form" method="post" action="{{ route('admin.programs.store') }}" style="margin-top:10px;">
                    @csrf
                    <input class="full" name="name" placeholder="Tên chương trình (VD: IC3 GS6 Spark)" required>
                    <input name="slug" placeholder="Slug (tự động nếu để trống)">
                    <input name="accent" placeholder="Mã màu (VD: #4f46e5)">
                    <button class="full">Tạo chương trình</button>
                </form>
            </details>
            @foreach($programs as $item)
            <form class="row" method="post" action="{{ route('admin.programs.update',$item) }}">
                @csrf
                @method('put')
                <input name="name" value="{{ $item->name }}">
                <input name="slug" value="{{ $item->slug }}">
                <div>
                    <input type="hidden" name="description" value="{{ $item->description }}">
                    <input type="hidden" name="accent" value="{{ $item->accent }}">
                    <button>Lưu</button>
                </div>
            </form>
            @endforeach
        </div>
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 style="margin:0;">Khối lớp / Cấp độ (Levels)</h3>
                <span class="count">{{ $levels->count() }}</span>
            </div>
            <details style="margin-bottom:14px;">
                <summary>＋ Thêm khối lớp mới</summary>
                <form class="form" method="post" action="{{ route('admin.levels.store') }}" style="margin-top:10px;">
                    @csrf
                    <select class="full" name="program_id" required>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <input class="full" name="name" placeholder="Tên khối (VD: IC3 GS6 Spark Level 1 — Khối 1)" required>
                    <input name="grade" type="number" min="1" max="12" placeholder="Số khối (1 - 12)" required>
                    <input name="position" type="number" placeholder="Thứ tự (1, 2...)" value="{{ $levels->count() + 1 }}">
                    <button class="full">Tạo khối lớp</button>
                </form>
            </details>
            @foreach($levels as $item)
            <form class="row" method="post" action="{{ route('admin.levels.update',$item) }}">
                @csrf
                @method('put')
                <input name="name" value="{{ $item->name }}" title="Tên khối">
                <input name="grade" type="number" value="{{ $item->grade }}" title="Số khối">
                <div>
                    <input type="hidden" name="program_id" value="{{ $item->program_id }}">
                    <input type="hidden" name="slug" value="{{ $item->slug }}">
                    <input type="hidden" name="position" value="{{ $item->position }}">
                    <button>Lưu</button>
                </div>
            </form>
            @endforeach
        </div>
    </div>
</section>
@endif
</main></div></div></body></html>
