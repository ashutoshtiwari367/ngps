<?php
$page_title = 'Fee Defaulters & Pending Dues';
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$class_filter = intval($_GET['class_id'] ?? 0);

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

// Query fees with remaining amount > 0
$sql = "SELECT f.*, s.admission_no, s.first_name, s.last_name, s.father_name, s.mobile, c.class_name, c.section 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    JOIN classes c ON f.class_id = c.id 
    WHERE f.remaining_amount > 0";

$params = [];
if ($class_filter > 0) {
    $sql .= " AND f.class_id = :cid";
    $params['cid'] = $class_filter;
}

$sql .= " ORDER BY f.remaining_amount DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$defaulters = $stmt->fetchAll();

$total_defaulter_amount = 0;
foreach ($defaulters as $d) {
    $total_defaulter_amount += (float)$d['remaining_amount'];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Fee Defaulters & Dues List</h1>
            <p class="text-xs text-slate-500 mt-0.5">Filter students with unpaid fee balances</p>
        </div>

        <!-- Class Filter -->
        <div class="w-full sm:w-64">
            <form method="GET" action="due_list.php">
                <select name="class_id" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($class_filter == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Overview Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Defaulter Count</span>
                <span class="text-3xl font-black text-rose-600 mt-1 block"><?= count($defaulters) ?> Students</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users-slash"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Outstanding Dues</span>
                <span class="text-3xl font-black text-rose-600 font-mono mt-1 block">₹<?= number_format($total_defaulter_amount, 2) ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- Defaulters Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Unpaid Balances List</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4">Student & Admission No</th>
                        <th class="p-4">Class</th>
                        <th class="p-4">Father / Mobile</th>
                        <th class="p-4 text-right">Fee Period</th>
                        <th class="p-4 text-right">Billed Amount</th>
                        <th class="p-4 text-right">Paid</th>
                        <th class="p-4 text-right">Balance Due</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php if (empty($defaulters)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 font-medium">No pending dues recorded. All students are up-to-date!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($defaulters as $d): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-4">
                                    <span class="font-extrabold text-slate-900 block"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></span>
                                    <span class="text-[11px] font-mono text-slate-400">Adm: <?= e($d['admission_no']) ?></span>
                                </td>
                                <td class="p-4 font-semibold text-slate-800">Class <?= e($d['class_name']) ?> (<?= e($d['section']) ?>)</td>
                                <td class="p-4">
                                    <span class="font-bold text-slate-800 block"><?= e($d['father_name']) ?></span>
                                    <span class="text-[11px] text-slate-500 font-mono"><?= e($d['mobile']) ?></span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        <?= e($d['paid_months'] ?? 'Admission') ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-slate-800">₹<?= number_format($d['total_amount'], 2) ?></td>
                                <td class="p-4 text-right font-mono font-bold text-emerald-600">₹<?= number_format($d['paid_amount'], 2) ?></td>
                                <td class="p-4 text-right font-mono font-extrabold text-rose-600">₹<?= number_format($d['remaining_amount'], 2) ?></td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="<?= base_url('admin/fees/add.php?student_id=' . $d['student_id']) ?>" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition-all flex items-center space-x-1">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                            <span>Collect</span>
                                        </a>
                                        <a href="<?= base_url('admin/accounts/student_ledger.php?student_id=' . $d['student_id']) ?>" title="View Ledger" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-all">
                                            <i class="fa-solid fa-book-journal-whills"></i>
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
