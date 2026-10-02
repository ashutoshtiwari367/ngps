<?php
$page_title = 'Class Students';
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$teacher_id_pk = $_SESSION['teacher_id_pk'] ?? 0;

try {
    $class_stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
    $class_stmt->execute(['teacher_id' => $teacher_id_pk]);
    $assigned_class = $class_stmt->fetch();

    $students = [];
    if ($assigned_class) {
        $stmt = $pdo->prepare("SELECT s.*, c.class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE s.class_id = :class_id ORDER BY s.first_name ASC");
        $stmt->execute(['class_id' => $assigned_class['id']]);
        $students = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    set_flash('error', 'Error fetching class students: ' . $e->getMessage());
    $students = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Class Roster</h1>
            <p class="text-sm text-slate-500 mt-1">
                Students enrolled in <span class="font-bold text-slate-800"><?= e($assigned_class['class_name'] ?? 'Class') ?> (Section <?= e($assigned_class['section'] ?? '') ?>)</span>
            </p>
        </div>
        <?php if ($assigned_class): ?>
            <a href="<?= base_url("teacher/attendance/mark.php?class_id={$assigned_class['id']}&date=" . date('Y-m-d')) ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-1.5 self-start sm:self-auto">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Mark Attendance</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Photo</th>
                        <th class="py-3.5 px-4">Admission No</th>
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4">Father Name</th>
                        <th class="py-3.5 px-4">Mobile</th>
                        <th class="py-3.5 px-4">Gender</th>
                        <th class="py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">No students enrolled in your assigned class.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs overflow-hidden border border-blue-200 shadow-sm">
                                        <?php if (!empty($s['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $s['photo'])): ?>
                                            <img src="<?= base_url('assets/uploads/' . e($s['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-xs font-bold text-blue-700"><?= e($s['admission_no']) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                <td class="py-3 px-4 text-xs text-slate-600"><?= e($s['father_name']) ?></td>
                                <td class="py-3 px-4 text-xs font-mono text-slate-600"><?= e($s['mobile']) ?></td>
                                <td class="py-3 px-4 text-xs"><?= e($s['gender']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <?= e($s['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
