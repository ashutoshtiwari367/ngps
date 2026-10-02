<?php
$page_title = 'Fee Report';
require_once __DIR__ . '/../../includes/header.php';

$status_filter = trim($_GET['status'] ?? '');
$class_filter = trim($_GET['class_id'] ?? '');

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

try {
    $where = [];
    $params = [];

    if (!empty($status_filter)) {
        $where[] = "f.payment_status = :status";
        $params['status'] = $status_filter;
    }
    if (!empty($class_filter)) {
        $where[] = "f.class_id = :class_id";
        $params['class_id'] = $class_filter;
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT f.*, s.first_name, s.last_name, s.admission_no, s.mobile, c.class_name, c.section 
            FROM fees f 
            JOIN students s ON f.student_id = s.id 
            JOIN classes c ON f.class_id = c.id 
            $where_sql 
            ORDER BY f.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fees = $stmt->fetchAll();

    $tot_paid = 0;
    $tot_due = 0;
    foreach ($fees as $fee) {
        $tot_paid += $fee['paid_amount'];
        $tot_due += $fee['remaining_amount'];
    }

} catch (PDOException $e) {
    set_flash('error', 'Fee report error: ' . $e->getMessage());
    $fees = [];
    $tot_paid = $tot_due = 0;
}
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Financial Fee Audit Report</h1>
            <p class="text-sm text-slate-500 mt-1">Review fee collections, pending balances, and payment statuses.</p>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-2 self-start sm:self-auto">
            <i class="fa-solid fa-print"></i>
            <span>Print Financial Report</span>
        </button>
    </div>

    <!-- Filters Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <form method="GET" action="fees.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
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
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Payment Status</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none">
                    <option value="">-- All Statuses --</option>
                    <option value="Paid" <?= ($status_filter === 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Partial" <?= ($status_filter === 'Partial') ? 'selected' : '' ?>>Partial</option>
                    <option value="Pending" <?= ($status_filter === 'Pending') ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow transition-all">
                    Filter Financials
                </button>
            </div>

        </form>
    </div>

    <!-- Printable Report Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 overflow-hidden">
        <div class="mb-4 pb-3 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Fee Ledger Summary</h2>
                <p class="text-xs text-slate-500">
                    Total Paid: <span class="font-mono font-bold text-emerald-700">₹<?= number_format($tot_paid) ?></span> &bull; 
                    Total Outstanding Pending: <span class="font-mono font-bold text-rose-600">₹<?= number_format($tot_due) ?></span>
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">Date: <?= date('d M Y') ?></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Receipt ID</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Total Fee</th>
                        <th class="py-3 px-4">Paid Amount</th>
                        <th class="py-3 px-4">Remaining Due</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($fees)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No fee ledger records found matching filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fees as $f): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#FEE-<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= e($f['first_name'] . ' ' . $f['last_name']) ?></td>
                                <td class="py-3 px-4"><?= e($f['class_name']) ?> (<?= e($f['section']) ?>)</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">₹<?= number_format($f['total_amount'], 2) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-700">₹<?= number_format($f['paid_amount'], 2) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-rose-600">₹<?= number_format($f['remaining_amount'], 2) ?></td>
                                <td class="py-3 px-4 font-mono"><?= e($f['payment_date']) ?></td>
                                <td class="py-3 px-4 font-semibold"><?= e($f['payment_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
