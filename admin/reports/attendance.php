<?php
$page_title = 'Class Attendance Report';
require_once __DIR__ . '/../../includes/header.php';

$class_filter = trim($_GET['class_id'] ?? '');
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

try {
    $where = [];
    $params = [];

    if (!empty($class_filter)) {
        $where[] = "c.id = :class_id";
        $params['class_id'] = $class_filter;
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT c.id, c.class_name, c.section, c.room_number, t.name as teacher_name,
            (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.status = 'Active') as total_students,
            (SELECT COUNT(DISTINCT a.attendance_date) FROM attendance a WHERE a.class_id = c.id) as sessions_conducted
            FROM classes c 
            LEFT JOIN teachers t ON c.teacher_id = t.id 
            $where_sql 
            ORDER BY c.class_name ASC, c.section ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $class_summaries = $stmt->fetchAll();

} catch (PDOException $e) {
    set_flash('error', 'Attendance summary error: ' . $e->getMessage());
    $class_summaries = [];
}
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Class Attendance Master Report</h1>
            <p class="text-sm text-slate-500 mt-1">Overview of conducted sessions and attendance rate by class.</p>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-2 self-start sm:self-auto">
            <i class="fa-solid fa-print"></i>
            <span>Print Master Report</span>
        </button>
    </div>

    <!-- Printable Summary Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 overflow-hidden">
        <div class="mb-4 pb-3 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Class Attendance Summary</h2>
                <p class="text-xs text-slate-500">Academic Session 2026-2027</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">Date: <?= date('d M Y') ?></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Class & Section</th>
                        <th class="py-3 px-4">Class Teacher</th>
                        <th class="py-3 px-4">Room No</th>
                        <th class="py-3 px-4 text-center">Total Students</th>
                        <th class="py-3 px-4 text-center">Sessions Conducted</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($class_summaries)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No class attendance summaries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($class_summaries as $cs): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-900 text-sm"><?= e($cs['class_name']) ?> (Section <?= e($cs['section']) ?>)</td>
                                <td class="py-3.5 px-4"><?= e($cs['teacher_name'] ?: 'Unassigned') ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-600"><?= e($cs['room_number']) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-blue-700"><?= $cs['total_students'] ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-purple-700"><?= $cs['sessions_conducted'] ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="<?= base_url('admin/attendance/report.php?class_id=' . $cs['id']) ?>" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-lg transition-colors inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        <span>View Class Roster</span>
                                    </a>
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
