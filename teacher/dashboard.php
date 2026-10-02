<?php
$page_title = 'Teacher Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$teacher_id_pk = $_SESSION['teacher_id_pk'] ?? 0;

try {
    // Fetch assigned class for teacher
    $class_stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
    $class_stmt->execute(['teacher_id' => $teacher_id_pk]);
    $assigned_class = $class_stmt->fetch();

    $student_count = 0;
    $today_present = 0;
    $today_marked = 0;
    $attendance_pct = 0;
    $class_students = [];

    if ($assigned_class) {
        // Fetch student count in class
        $cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE class_id = :class_id AND status = 'Active'");
        $cnt_stmt->execute(['class_id' => $assigned_class['id']]);
        $student_count = $cnt_stmt->fetchColumn() ?: 0;

        // Today's attendance for assigned class
        $att_stmt = $pdo->prepare("SELECT 
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as present_cnt,
            COUNT(*) as total_cnt
            FROM attendance 
            WHERE class_id = :class_id AND attendance_date = CURDATE()");
        $att_stmt->execute(['class_id' => $assigned_class['id']]);
        $att_row = $att_stmt->fetch();

        $today_present = $att_row['present_cnt'] ?: 0;
        $today_marked = $att_row['total_cnt'] ?: 0;
        $attendance_pct = ($today_marked > 0) ? round(($today_present / $today_marked) * 100) : 0;

        // Fetch students list
        $stu_stmt = $pdo->prepare("SELECT * FROM students WHERE class_id = :class_id AND status = 'Active' ORDER BY first_name ASC LIMIT 5");
        $stu_stmt->execute(['class_id' => $assigned_class['id']]);
        $class_students = $stu_stmt->fetchAll();
    }

} catch (PDOException $e) {
    set_flash('error', 'Dashboard query error: ' . $e->getMessage());
    $assigned_class = false;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold border border-indigo-100 mb-2">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher Portal</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Welcome, <?= e($_SESSION['full_name']) ?>!</h1>
            <p class="text-sm text-slate-500 mt-1">Manage your assigned classroom students, attendance, and academic progress.</p>
        </div>
        
        <?php if ($assigned_class): ?>
            <div class="flex items-center space-x-3">
                <a href="<?= base_url("teacher/attendance/mark.php?class_id={$assigned_class['id']}&date=" . date('Y-m-d')) ?>" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    <i class="fa-solid fa-calendar-check text-xs"></i>
                    <span>Take Today's Attendance</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$assigned_class): ?>
        <div class="p-8 bg-amber-50 border border-amber-200 rounded-2xl text-center space-y-3">
            <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-500"></i>
            <h3 class="text-lg font-bold text-amber-900">No Assigned Class Found</h3>
            <p class="text-xs text-amber-800 max-w-md mx-auto">You have not been assigned as a Class Teacher to any section yet. Please contact the administrator to assign your class.</p>
        </div>
    <?php else: ?>

        <!-- Assigned Class Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Class Info Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Assigned Section</span>
                <span class="text-2xl font-extrabold text-slate-900 block mt-2"><?= e($assigned_class['class_name']) ?> (Sec <?= e($assigned_class['section']) ?>)</span>
                <span class="text-xs font-semibold text-blue-600 mt-1 block">Room: <?= e($assigned_class['room_number']) ?></span>
            </div>

            <!-- Total Enrolled Students -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Class Enrolled Students</span>
                <span class="text-2xl font-extrabold text-blue-600 block mt-2"><?= $student_count ?> Students</span>
                <span class="text-xs font-semibold text-slate-500 mt-1 block">Active Roster</span>
            </div>

            <!-- Today's Attendance Rate -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Today's Attendance</span>
                <span class="text-2xl font-extrabold text-purple-600 block mt-2"><?= $attendance_pct ?>%</span>
                <span class="text-xs font-semibold text-purple-700 mt-1 block"><?= $today_present ?> Present Today</span>
            </div>

            <!-- Session -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Academic Session</span>
                <span class="text-2xl font-extrabold text-slate-800 block mt-2"><?= e($assigned_class['academic_session']) ?></span>
                <span class="text-xs font-semibold text-emerald-600 mt-1 block">Active Term</span>
            </div>

        </div>

        <!-- Class Roster Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Class Roster (<?= e($assigned_class['class_name']) ?> Section <?= e($assigned_class['section']) ?>)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Students enrolled under your class supervision</p>
                </div>
                <a href="<?= base_url('teacher/students/index.php') ?>" class="text-xs font-bold text-blue-600 hover:underline">View All Students &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Adm No</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Father Name</th>
                            <th class="py-3 px-4">Mobile</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (empty($class_students)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">No students enrolled in this class yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($class_students as $st): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-blue-700"><?= e($st['admission_no']) ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></td>
                                    <td class="py-3 px-4"><?= e($st['father_name']) ?></td>
                                    <td class="py-3 px-4 font-mono"><?= e($st['mobile']) ?></td>
                                    <td class="py-3 px-4 font-semibold text-emerald-700"><?= e($st['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
