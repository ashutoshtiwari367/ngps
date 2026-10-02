<?php
$page_title = 'Student Management';
require_once __DIR__ . '/../../includes/header.php';

$search = trim($_GET['search'] ?? '');
$class_filter = trim($_GET['class_id'] ?? '');

try {
    // Build query with optional filters
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.admission_no LIKE :search OR s.mobile LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!empty($class_filter)) {
        $where[] = "s.class_id = :class_id";
        $params['class_id'] = $class_filter;
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT s.*, c.class_name 
            FROM students s 
            LEFT JOIN classes c ON s.class_id = c.id 
            $where_sql 
            ORDER BY s.id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Fetch classes for dropdown filter
    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

} catch (PDOException $e) {
    set_flash('error', 'Database query failed: ' . $e->getMessage());
    $students = [];
    $classes = [];
}
?>

<div class="space-y-6">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Directory</h1>
            <p class="text-sm text-slate-500 mt-1">Manage student admissions, profiles, and academic records.</p>
        </div>
        <a href="<?= base_url('admin/accounts/admission.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-amber-500/20 transition-all self-start sm:self-auto">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>New Student Admission</span>
        </a>
    </div>

    <!-- Filter & Search Bar Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Search Input -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, admission no, mobile..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <!-- Filter by Class -->
            <div class="flex space-x-2">
                <select name="class_id" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($class_filter == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['class_name']) ?> (Sec <?= e($c['section']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($class_filter)): ?>
                    <a href="index.php" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition-all flex items-center justify-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Students Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Photo</th>
                        <th class="py-3.5 px-4">Admission No</th>
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4">Class & Section</th>
                        <th class="py-3.5 px-4">Father Name</th>
                        <th class="py-3.5 px-4">Mobile</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 block text-slate-300"></i>
                                <span>No student records found matching the criteria.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs overflow-hidden border border-blue-200 shadow-sm">
                                        <?php if (!empty($s['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $s['photo'])): ?>
                                            <img src="<?= base_url('assets/uploads/' . e($s['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-xs font-bold text-blue-700 bg-blue-50/50 rounded-lg px-2 inline-block my-2"><?= e($s['admission_no']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span>
                                    <span class="text-[11px] text-slate-400">DOB: <?= e($s['dob']) ?></span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    <?= e($s['class_name'] ?? 'N/A') ?> <span class="text-xs text-slate-500 font-normal">(Sec <?= e($s['section']) ?>)</span>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600"><?= e($s['father_name']) ?></td>
                                <td class="py-3 px-4 text-xs text-slate-600 font-mono"><?= e($s['mobile']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <?= e($s['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="<?= base_url('admin/students/view.php?id=' . $s['id']) ?>" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Profile">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        <a href="<?= base_url('admin/students/edit.php?id=' . $s['id']) ?>" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Student">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <a href="<?= base_url('admin/students/delete.php?id=' . $s['id'] . '&csrf=' . csrf_token()) ?>" 
                                           onclick="return confirm('Are you sure you want to delete student <?= e($s['first_name'] . ' ' . $s['last_name']) ?>?');" 
                                           class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Student">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </a>
                                    </div>
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
