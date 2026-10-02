<?php
$page_title = 'Teacher Management';
require_once __DIR__ . '/../../includes/header.php';

$search = trim($_GET['search'] ?? '');

try {
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(name LIKE :search OR teacher_id LIKE :search OR username LIKE :search OR subject LIKE :search OR mobile LIKE :search)";
        $params['search'] = "%$search%";
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $stmt = $pdo->prepare("SELECT * FROM teachers $where_sql ORDER BY id DESC");
    $stmt->execute($params);
    $teachers = $stmt->fetchAll();
} catch (PDOException $e) {
    set_flash('error', 'Query error: ' . $e->getMessage());
    $teachers = [];
}
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Faculty & Teacher Accounts</h1>
            <p class="text-sm text-slate-500 mt-1">Manage school teaching staff, assigned subjects, and portal login credentials.</p>
        </div>
        <a href="add.php" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all self-start sm:self-auto">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Teacher</span>
        </a>
    </div>

    <!-- Search Bar Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="index.php" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search teacher by name, ID, username, subject..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all">
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Teacher ID</th>
                        <th class="py-3.5 px-4">Name</th>
                        <th class="py-3.5 px-4">Portal Username</th>
                        <th class="py-3.5 px-4">Subject</th>
                        <th class="py-3.5 px-4">Qualification</th>
                        <th class="py-3.5 px-4">Mobile</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">No teacher records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-indigo-700 bg-indigo-50/50 rounded-lg px-2 inline-block my-2"><?= e($t['teacher_id']) ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= e($t['name']) ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-blue-700">
                                    <?php if (!empty($t['username'])): ?>
                                        <span class="bg-blue-50 px-2 py-0.5 rounded border border-blue-100"><i class="fa-solid fa-key text-[10px] text-blue-500 mr-1"></i><?= e($t['username']) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic font-normal text-xs">No Portal Access</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        <?= e($t['subject']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600"><?= e($t['qualification']) ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600"><?= e($t['mobile']) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?= $t['status'] === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                        <?= e($t['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="edit.php?id=<?= $t['id'] ?>" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Teacher">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $t['id'] ?>&csrf=<?= csrf_token() ?>" 
                                           onclick="return confirm('Are you sure you want to delete teacher <?= e($t['name']) ?>?');" 
                                           class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Teacher">
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
