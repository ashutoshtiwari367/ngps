<?php
$page_title = 'Students Report';
require_once __DIR__ . '/../../includes/header.php';

$class_filter = trim($_GET['class_id'] ?? '');
$gender_filter = trim($_GET['gender'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

try {
    $where = [];
    $params = [];

    if (!empty($class_filter)) {
        $where[] = "s.class_id = :class_id";
        $params['class_id'] = $class_filter;
    }
    if (!empty($gender_filter)) {
        $where[] = "s.gender = :gender";
        $params['gender'] = $gender_filter;
    }
    if (!empty($status_filter)) {
        $where[] = "s.status = :status";
        $params['status'] = $status_filter;
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT s.*, c.class_name 
            FROM students s 
            LEFT JOIN classes c ON s.class_id = c.id 
            $where_sql 
            ORDER BY s.class_id ASC, s.first_name ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

} catch (PDOException $e) {
    set_flash('error', 'Report error: ' . $e->getMessage());
    $students = [];
}
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Enrollment Report</h1>
            <p class="text-sm text-slate-500 mt-1">Exportable and printable student master report.</p>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-2 self-start sm:self-auto">
            <i class="fa-solid fa-print"></i>
            <span>Print Student Report</span>
        </button>
    </div>

    <!-- Filters Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <form method="GET" action="students.php" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Class</label>
                <select name="class_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($class_filter == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Gender</label>
                <select name="gender" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none">
                    <option value="">-- All Genders --</option>
                    <option value="Male" <?= ($gender_filter === 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($gender_filter === 'Female') ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none">
                    <option value="">-- All Status --</option>
                    <option value="Active" <?= ($status_filter === 'Active') ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($status_filter === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition-all">
                    Generate Report
                </button>
            </div>

        </form>
    </div>

    <!-- Printable Report Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 overflow-hidden">
        <div class="mb-4 pb-3 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Student Roster Summary</h2>
                <p class="text-xs text-slate-500">Total Enrolled: <?= count($students) ?> Students</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">Date: <?= date('d M Y') ?></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Adm No</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Father Name</th>
                        <th class="py-3 px-4">Mobile</th>
                        <th class="py-3 px-4">Gender</th>
                        <th class="py-3 px-4">DOB</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No records found matching filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-blue-700"><?= e($s['admission_no']) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                <td class="py-3 px-4"><?= e($s['class_name']) ?> (<?= e($s['section']) ?>)</td>
                                <td class="py-3 px-4"><?= e($s['father_name']) ?></td>
                                <td class="py-3 px-4 font-mono"><?= e($s['mobile']) ?></td>
                                <td class="py-3 px-4"><?= e($s['gender']) ?></td>
                                <td class="py-3 px-4"><?= e($s['dob']) ?></td>
                                <td class="py-3 px-4 font-semibold text-emerald-700"><?= e($s['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
