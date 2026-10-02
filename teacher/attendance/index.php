<?php
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$teacher_id_pk = $_SESSION['teacher_id_pk'] ?? 0;

$class_stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
$class_stmt->execute(['teacher_id' => $teacher_id_pk]);
$assigned_class = $class_stmt->fetch();

if (!$assigned_class) {
    set_flash('error', 'You are not assigned to any class section.');
    header('Location: ' . base_url('teacher/dashboard.php'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['attendance_date'] ?? date('Y-m-d'));
    header("Location: " . base_url("teacher/attendance/mark.php?class_id={$assigned_class['id']}&date={$date}"));
    exit();
}

$page_title = 'Take Attendance';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-2xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daily Class Attendance</h1>
            <p class="text-sm text-slate-500 mt-1">Class: <span class="font-bold text-slate-800"><?= e($assigned_class['class_name']) ?> (Section <?= e($assigned_class['section']) ?>)</span></p>
        </div>
        <a href="report.php" class="px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 font-semibold text-xs rounded-xl transition-all flex items-center space-x-1">
            <i class="fa-solid fa-chart-pie"></i>
            <span>View Class Report</span>
        </a>
    </div>

    <!-- Date Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        <form action="index.php" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Attendance Date *</label>
                <input type="date" name="attendance_date" required value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Take / Update Class Attendance</span>
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
