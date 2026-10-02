<?php
$page_title = 'Student Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch student full record with class info
$stmt = $pdo->prepare("SELECT s.*, c.class_name, c.section, c.room_number 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE s.id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id_pk]);
$student = $stmt->fetch();

if (!$student) {
    die("Student profile missing.");
}

// 1. Attendance Statistics for this student
$att_stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN status = 'Present' THEN 1 END) as present_count,
    COUNT(*) as total_count 
    FROM attendance 
    WHERE student_id = :sid");
$att_stmt->execute(['sid' => $student_id_pk]);
$att_stats = $att_stmt->fetch();
$total_present = $att_stats['present_count'] ?: 0;
$total_marked = $att_stats['total_count'] ?: 0;
$attendance_pct = ($total_marked > 0) ? round(($total_present / $total_marked) * 100) : 100;

// 2. Fee Collection Summary for this student
$fee_stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
$fee_stmt->execute(['sid' => $student_id_pk]);
$fee_record = $fee_stmt->fetch();
$paid_amount = $fee_record ? (float)$fee_record['paid_amount'] : 0.00;
$remaining_amount = $fee_record ? (float)$fee_record['remaining_amount'] : 0.00;
$fee_status = $fee_record ? $fee_record['payment_status'] : 'Paid';

// 3. Active Homework for student's class
$hw_stmt = $pdo->prepare("SELECT h.*, sub.subject_name, t.name as teacher_name 
    FROM homework h 
    LEFT JOIN subjects sub ON h.subject_id = sub.id 
    LEFT JOIN teachers t ON h.teacher_id = t.id 
    WHERE h.class_id = :cid 
    ORDER BY h.due_date ASC LIMIT 3");
$hw_stmt->execute(['cid' => $student['class_id']]);
$recent_homework = $hw_stmt->fetchAll();

// 4. Student Notices
$notices_stmt = $pdo->prepare("SELECT * FROM notices WHERE target_role IN ('All', 'Student') ORDER BY id DESC LIMIT 3");
$notices_stmt->execute();
$student_notices = $notices_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <!-- Student Welcome Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold flex items-center justify-center text-xl shadow-lg shadow-emerald-500/20 overflow-hidden border-2 border-emerald-200">
                <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../assets/uploads/' . $student['photo'])): ?>
                    <img src="<?= base_url('assets/uploads/' . e($student['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                <?php else: ?>
                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="inline-flex items-center space-x-1.5 px-3 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold border border-emerald-200 mb-1">
                    <i class="fa-solid fa-graduation-cap text-emerald-600"></i>
                    <span>Class <?= e($student['class_name']) ?> (Section <?= e($student['section']) ?>)</span>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Welcome, <?= e($student['first_name'] . ' ' . $student['last_name']) ?>!</h1>
                <p class="text-xs text-slate-500 mt-0.5">Admission No: <strong class="font-mono text-slate-800"><?= e($student['admission_no']) ?></strong> &bull; Parent/Father: <strong><?= e($student['father_name']) ?></strong></p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="<?= base_url('student/results.php') ?>" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-square-poll-vertical"></i>
                <span>View Exam Results</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Attendance Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Attendance Score</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-check text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= $attendance_pct ?>%</span>
                <span class="block text-[11px] text-purple-700 font-medium mt-0.5"><?= $total_present ?> Present out of <?= $total_marked ?> Sessions</span>
            </div>
        </div>

        <!-- Fee Payment Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fee Account Status</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold <?= $remaining_amount > 0 ? 'text-amber-600' : 'text-emerald-600' ?>">₹<?= number_format($remaining_amount) ?></span>
                <span class="block text-[11px] font-semibold mt-0.5 <?= $remaining_amount > 0 ? 'text-amber-700' : 'text-emerald-700' ?>">
                    Status: <?= e($fee_status) ?>
                </span>
            </div>
        </div>

        <!-- Class Room Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Classroom Location</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-school text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= e($student['room_number'] ?? 'Room 101') ?></span>
                <span class="block text-[11px] text-blue-600 font-medium mt-0.5">Primary Classroom</span>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Enrollment Status</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-teal-700"><?= e($student['status']) ?></span>
                <span class="block text-[11px] text-teal-600 font-medium mt-0.5">Registered Student</span>
            </div>
        </div>

    </div>

    <!-- Main Content Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Columns: Active Homework & Assignments -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Active Homework Assignments</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tasks published by your class subject teachers</p>
                </div>
                <a href="<?= base_url('student/homework.php') ?>" class="text-xs font-bold text-emerald-600 hover:underline">View All Homework &rarr;</a>
            </div>

            <div class="p-5 space-y-4">
                <?php if (empty($recent_homework)): ?>
                    <div class="p-6 text-center text-xs text-slate-400">No active homework tasks assigned.</div>
                <?php else: ?>
                    <?php foreach ($recent_homework as $hw): ?>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2 hover:border-emerald-300 transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    <?= e($hw['subject_name'] ?? 'Subject') ?> &bull; Teacher: <?= e($hw['teacher_name'] ?? 'Faculty') ?>
                                </span>
                                <span class="text-xs font-extrabold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                                    Due: <?= date('d M Y', strtotime($hw['due_date'])) ?>
                                </span>
                            </div>
                            <h4 class="font-extrabold text-slate-900 text-sm"><?= e($hw['title']) ?></h4>
                            <p class="text-slate-600 text-xs leading-relaxed line-clamp-2"><?= e($hw['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right 1 Column: School Notices -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base">Latest Notices</h3>
                <a href="<?= base_url('student/notices.php') ?>" class="text-xs font-bold text-emerald-600 hover:underline">All Bulletins &rarr;</a>
            </div>

            <div class="p-5 space-y-4">
                <?php if (empty($student_notices)): ?>
                    <div class="p-6 text-center text-xs text-slate-400">No recent announcements.</div>
                <?php else: ?>
                    <?php foreach ($student_notices as $nt): ?>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
                                <span><i class="fa-regular fa-clock mr-1"></i><?= date('d M Y', strtotime($nt['created_at'])) ?></span>
                                <?php if ($nt['is_important']): ?>
                                    <span class="text-rose-600 font-bold uppercase"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Important</span>
                                <?php endif; ?>
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs"><?= e($nt['title']) ?></h4>
                            <p class="text-slate-600 text-xs leading-relaxed line-clamp-2"><?= e($nt['content']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
