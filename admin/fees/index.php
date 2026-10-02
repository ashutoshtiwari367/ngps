<?php
$page_title = 'Fee Management';
require_once __DIR__ . '/../../includes/header.php';

$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$class_filter = trim($_GET['class_id'] ?? '');
$month_filter = trim($_GET['month'] ?? '');

$all_months = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

try {
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.admission_no LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!empty($status_filter)) {
        $where[] = "f.payment_status = :status";
        $params['status'] = $status_filter;
    }

    if (!empty($class_filter)) {
        $where[] = "f.class_id = :class_id";
        $params['class_id'] = $class_filter;
    }

    if (!empty($month_filter)) {
        $where[] = "f.paid_months LIKE :month";
        $params['month'] = "%$month_filter%";
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT f.*, s.first_name, s.last_name, s.admission_no, c.class_name, c.section 
            FROM fees f 
            JOIN students s ON f.student_id = s.id 
            JOIN classes c ON f.class_id = c.id 
            $where_sql 
            ORDER BY f.id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fees = $stmt->fetchAll();

    // Summary counters
    $totals = $pdo->query("SELECT SUM(paid_amount) as total_paid, SUM(remaining_amount) as total_due FROM fees")->fetch();
    $grand_paid = $totals['total_paid'] ?: 0;
    $grand_due = $totals['total_due'] ?: 0;

} catch (PDOException $e) {
    set_flash('error', 'Query failed: ' . $e->getMessage());
    $fees = [];
    $grand_paid = 0;
    $grand_due = 0;
}
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Fee Collection & Dues</h1>
            <p class="text-sm text-slate-500 mt-1">Track payments, filter by Class & Month, and print official receipts.</p>
        </div>
        <a href="add.php" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all self-start sm:self-auto">
            <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
            <span>Collect New Payment</span>
        </a>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Collected</span>
                <span class="text-2xl font-extrabold text-emerald-600 block mt-1">₹<?= number_format($grand_paid) ?></span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Outstanding Pending</span>
                <span class="text-2xl font-extrabold text-rose-600 block mt-1">₹<?= number_format($grand_due) ?></span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between sm:col-span-2 lg:col-span-1">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transactions</span>
                <span class="text-2xl font-extrabold text-slate-900 block mt-1"><?= count($fees) ?> Records</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar with Class & Month Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search student name or admission no..." 
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <!-- Class Filter -->
            <div>
                <select name="class_id" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($class_filter == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Month Filter -->
            <div>
                <select name="month" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="">-- All Months --</option>
                    <?php foreach ($all_months as $m): ?>
                        <option value="<?= $m ?>" <?= ($month_filter === $m) ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex space-x-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($status_filter) || !empty($class_filter) || !empty($month_filter)): ?>
                    <a href="index.php" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition-all flex items-center justify-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Fee Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Receipt ID</th>
                        <th class="py-3.5 px-4">Student</th>
                        <th class="py-3.5 px-4">Class</th>
                        <th class="py-3.5 px-4">Paid Months</th>
                        <th class="py-3.5 px-4">Total Fee</th>
                        <th class="py-3.5 px-4">Paid Amount</th>
                        <th class="py-3.5 px-4">Remaining</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($fees)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">No fee records found for selected month/class criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fees as $f): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-800">#FEE-<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block"><?= e($f['first_name'] . ' ' . $f['last_name']) ?></span>
                                    <span class="text-[11px] font-mono text-blue-600 font-semibold"><?= e($f['admission_no']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-slate-800"><?= e($f['class_name']) ?> (<?= e($f['section']) ?>)</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        <i class="fa-solid fa-calendar-days text-[10px] mr-1"></i> <?= e($f['paid_months'] ?: 'Annual Term') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-800">₹<?= number_format($f['total_amount'], 2) ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-emerald-700">₹<?= number_format($f['paid_amount'], 2) ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs font-bold <?= $f['remaining_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                                    ₹<?= number_format($f['remaining_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $f['payment_status'] === 'Paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($f['payment_status'] === 'Partial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') ?>">
                                        <?= e($f['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="receipt.php?id=<?= $f['id'] ?>" target="_blank" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition-colors inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-print text-[11px]"></i>
                                        <span>Print</span>
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
