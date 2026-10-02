<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

// Handle POST selection BEFORE html header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id'] ?? 0);
    $date = trim($_POST['attendance_date'] ?? date('Y-m-d'));
    
    if ($class_id > 0 && !empty($date)) {
        header("Location: " . base_url("admin/attendance/mark.php?class_id={$class_id}&date={$date}"));
        exit();
    }
}

$page_title = 'Attendance Management';
require_once __DIR__ . '/../../includes/header.php';

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
?>

<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Attendance</h1>
            <p class="text-sm text-slate-500 mt-1">Select class and date to record daily student attendance or view report.</p>
        </div>
        <a href="report.php" class="px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 font-semibold text-xs rounded-xl transition-all flex items-center space-x-1.5">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Attendance Reports</span>
        </a>
    </div>

    <!-- Selection Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        <form action="index.php" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Class & Section *</label>
                    <select name="class_id" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>) - Room <?= e($c['room_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Attendance Date *</label>
                    <input type="date" name="attendance_date" required value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Take / Update Attendance</span>
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
