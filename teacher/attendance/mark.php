<?php
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$teacher_id_pk = $_SESSION['teacher_id_pk'] ?? 0;
$attendance_date = trim($_GET['date'] ?? date('Y-m-d'));

// Validate date is not in the future
if ($attendance_date > date('Y-m-d')) {
    set_flash('error', 'Future date attendance cannot be taken.');
    header('Location: ' . base_url('teacher/attendance/index.php'));
    exit();
}

try {
    // Strictly fetch ONLY the class assigned to THIS teacher
    $class_stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
    $class_stmt->execute(['teacher_id' => $teacher_id_pk]);
    $class = $class_stmt->fetch();

    if (!$class) {
        set_flash('error', 'You are not assigned as Class Teacher for any section. Contact the Administrator.');
        header('Location: ' . base_url('teacher/dashboard.php'));
        exit();
    }

    // If URL has class_id param, strictly verify it matches assigned class
    if (isset($_GET['class_id'])) {
        $requested_class_id = intval($_GET['class_id']);
        if ($requested_class_id !== (int)$class['id']) {
            set_flash('error', 'Access Denied: You can only take attendance for your assigned class section.');
            header('Location: ' . base_url('teacher/attendance/index.php'));
            exit();
        }
    }

    $class_id = $class['id'];

    // Fetch students in assigned class
    $students_stmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.class_id = :class_id AND s.status = 'Active' ORDER BY s.admission_no ASC");
    $students_stmt->execute(['class_id' => $class_id]);
    $students = $students_stmt->fetchAll();

    // Fetch existing attendance records for that date
    $att_stmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE class_id = :class_id AND attendance_date = :att_date");
    $att_stmt->execute(['class_id' => $class_id, 'att_date' => $attendance_date]);
    $existing_raw = $att_stmt->fetchAll();
    
    $existing = [];
    foreach ($existing_raw as $r) {
        $existing[$r['student_id']] = $r['status'];
    }

} catch (PDOException $e) {
    set_flash('error', 'Database query error: ' . $e->getMessage());
    header('Location: ' . base_url('teacher/dashboard.php'));
    exit();
}

// Handle Form Submission BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash('error', 'Invalid security token.');
    } else {
        $attendance_data = $_POST['attendance'] ?? [];
        
        try {
            $pdo->beginTransaction();
            $upsert_stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, attendance_date, status) 
                VALUES (:student_id, :class_id, :attendance_date, :status) 
                ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = CURRENT_TIMESTAMP");

            foreach ($students as $st) {
                $status = isset($attendance_data[$st['id']]) ? $attendance_data[$st['id']] : 'Present';
                $upsert_stmt->execute([
                    'student_id'      => $st['id'],
                    'class_id'        => $class_id,
                    'attendance_date' => $attendance_date,
                    'status'          => $status
                ]);
            }
            $pdo->commit();

            set_flash('success', 'Attendance for ' . e($class['class_name']) . ' (Section ' . e($class['section']) . ') on ' . $attendance_date . ' saved successfully!');
            header("Location: " . base_url("teacher/attendance/report.php"));
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            set_flash('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }
}

$page_title = 'Mark Attendance';
require_once __DIR__ . '/../../includes/header.php';

// Display flash if any
$flash = get_flash();
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Mark Attendance</h1>
            <p class="text-sm text-slate-500 mt-1">
                Class: <span class="font-bold text-slate-800"><?= e($class['class_name']) ?> (Section <?= e($class['section']) ?>)</span> &bull; 
                Date: <span class="font-bold text-blue-600 font-mono"><?= e($attendance_date) ?></span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="markAllPresent()" class="px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-semibold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-check-double"></i>
                <span>Mark All Present</span>
            </button>
            <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
                Change Date
            </a>
        </div>
    </div>

    <!-- Class Assignment Badge -->
    <div class="flex items-center space-x-3 bg-indigo-50/70 border border-indigo-100 rounded-2xl px-5 py-3">
        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-lock text-sm"></i>
        </div>
        <div class="text-xs">
            <span class="font-bold text-indigo-900">Your Assigned Section: </span>
            <span class="font-bold text-indigo-700"><?= e($class['class_name']) ?> (Section <?= e($class['section']) ?>) &bull; <?= e($class['room_number']) ?></span>
            <span class="text-indigo-500 ml-2 block sm:inline">— Aap sirf is class ka attendance le sakte hain.</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="p-4 rounded-xl text-sm font-medium flex items-center space-x-2 <?= $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' ?>">
            <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500' ?>"></i>
            <span><?= e($flash['text']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Attendance Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-6 md:p-8">
        <form action="mark.php?date=<?= e($attendance_date) ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Admission No</th>
                            <th class="py-3 px-4 text-center">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400">No active students found in this class.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $idx => $st): 
                                $current_status = $existing[$st['id']] ?? 'Present';
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-xs text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs font-bold text-blue-700"><?= e($st['admission_no']) ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 space-x-1">
                                            
                                            <!-- Present Toggle -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[<?= $st['id'] ?>]" value="Present" class="peer hidden radio-present" <?= $current_status === 'Present' ? 'checked' : '' ?>>
                                                <span class="px-3 sm:px-4 py-1.5 rounded-lg text-xs font-bold transition-all block peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 hover:text-slate-900">
                                                    <i class="fa-solid fa-check text-[10px] mr-1"></i> Present
                                                </span>
                                            </label>

                                            <!-- Absent Toggle -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[<?= $st['id'] ?>]" value="Absent" class="peer hidden radio-absent" <?= $current_status === 'Absent' ? 'checked' : '' ?>>
                                                <span class="px-3 sm:px-4 py-1.5 rounded-lg text-xs font-bold transition-all block peer-checked:bg-rose-600 peer-checked:text-white text-slate-600 hover:text-slate-900">
                                                    <i class="fa-solid fa-xmark text-[10px] mr-1"></i> Absent
                                                </span>
                                            </label>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($students)): ?>
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs text-slate-400 font-medium"><i class="fa-solid fa-users mr-1"></i><?= count($students) ?> students in class</span>
                    <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                        <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                        <button type="submit" class="px-6 sm:px-8 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Save Attendance Sheet</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

</div>

<script>
function markAllPresent() {
    const presentRadios = document.querySelectorAll('.radio-present');
    presentRadios.forEach(radio => {
        radio.checked = true;
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
