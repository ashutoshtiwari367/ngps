<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$class_id = intval($_GET['class_id'] ?? 0);
$attendance_date = trim($_GET['date'] ?? date('Y-m-d'));

if (!$class_id) {
    set_flash('error', 'Please select a class.');
    header('Location: ' . base_url('admin/attendance/index.php'));
    exit();
}

try {
    // Fetch class info
    $class_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id LIMIT 1");
    $class_stmt->execute(['id' => $class_id]);
    $class = $class_stmt->fetch();

    if (!$class) {
        set_flash('error', 'Class not found.');
        header('Location: ' . base_url('admin/attendance/index.php'));
        exit();
    }

    // Fetch students in class
    $students_stmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.class_id = :class_id AND s.status = 'Active' ORDER BY s.admission_no ASC");
    $students_stmt->execute(['class_id' => $class_id]);
    $students = $students_stmt->fetchAll();

    // Fetch existing attendance records for date
    $att_stmt = $pdo->prepare("SELECT student_id, status, remarks FROM attendance WHERE class_id = :class_id AND attendance_date = :att_date");
    $att_stmt->execute(['class_id' => $class_id, 'att_date' => $attendance_date]);
    $existing_raw = $att_stmt->fetchAll();
    
    $existing = [];
    foreach ($existing_raw as $r) {
        $existing[$r['student_id']] = $r['status'];
    }

} catch (PDOException $e) {
    set_flash('error', 'Database query error: ' . $e->getMessage());
    $students = [];
    $existing = [];
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
            header("Location: " . base_url("admin/attendance/report.php?class_id={$class_id}"));
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            set_flash('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }
}

$page_title = 'Mark Attendance';
require_once __DIR__ . '/../../includes/header.php';
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
        <div class="flex items-center space-x-2">
            <button type="button" onclick="markAllPresent()" class="px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-semibold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-check-double"></i>
                <span>Mark All Present</span>
            </button>
            <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
                Change Selection
            </a>
        </div>
    </div>

    <!-- Attendance Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        <form action="mark.php?class_id=<?= $class_id ?>&date=<?= e($attendance_date) ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Admission No</th>
                            <th class="py-3 px-4 text-center">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="3" class="py-12 text-center text-slate-400">No active students found in this class.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $st): 
                                $current_status = $existing[$st['id']] ?? 'Present';
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></span>
                                        <span class="text-[11px] text-slate-400">Father: <?= e($st['father_name']) ?></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs font-bold text-blue-700"><?= e($st['admission_no']) ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 space-x-1">
                                            
                                            <!-- Present Toggle -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[<?= $st['id'] ?>]" value="Present" class="peer hidden radio-present" <?= $current_status === 'Present' ? 'checked' : '' ?>>
                                                <span class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all block peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 hover:text-slate-900">
                                                    <i class="fa-solid fa-check text-[10px] mr-1"></i> Present
                                                </span>
                                            </label>

                                            <!-- Absent Toggle -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[<?= $st['id'] ?>]" value="Absent" class="peer hidden radio-absent" <?= $current_status === 'Absent' ? 'checked' : '' ?>>
                                                <span class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all block peer-checked:bg-rose-600 peer-checked:text-white text-slate-600 hover:text-slate-900">
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
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Attendance Sheet</span>
                    </button>
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
