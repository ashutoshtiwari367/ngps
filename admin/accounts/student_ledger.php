<?php
$page_title = 'Student Financial Profile & Ledger';
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$student_id = intval($_GET['student_id'] ?? 0);

// Fetch all students for dropdown
$students = $pdo->query("SELECT s.id, s.admission_no, s.first_name, s.last_name, s.father_name, c.class_name, c.section 
    FROM students s 
    JOIN classes c ON s.class_id = c.id 
    ORDER BY s.first_name ASC")->fetchAll();

if ($student_id <= 0 && !empty($students)) {
    $student_id = $students[0]['id'];
}

// Fetch selected student details
$student = null;
$ledger = [];
$total_invoiced = 0;
$total_paid = 0;
$total_due = 0;

if ($student_id > 0) {
    $stmt = $pdo->prepare("SELECT s.*, c.class_name, c.section, c.room_number 
        FROM students s 
        JOIN classes c ON s.class_id = c.id 
        WHERE s.id = :id LIMIT 1");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();

    if ($student) {
        $l_stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = :sid ORDER BY payment_date DESC, id DESC");
        $l_stmt->execute(['sid' => $student_id]);
        $ledger = $l_stmt->fetchAll();

        foreach ($ledger as $l) {
            $total_invoiced += (float)$l['total_amount'];
            $total_paid += (float)$l['paid_amount'];
            $total_due += (float)$l['remaining_amount'];
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Financial Profile & Ledger</h1>
            <p class="text-xs text-slate-500 mt-0.5">Lifetime fee transaction statement & account ledger</p>
        </div>

        <!-- Student Selector Dropdown -->
        <div class="w-full sm:w-80">
            <form method="GET" action="student_ledger.php">
                <select name="student_id" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    <?php foreach ($students as $st): ?>
                        <option value="<?= $st['id'] ?>" <?= ($student_id == $st['id']) ? 'selected' : '' ?>>
                            <?= e($st['first_name'] . ' ' . $st['last_name']) ?> (<?= e($st['admission_no']) ?>) - Class <?= e($st['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($student): ?>
        <!-- Student Info Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white font-extrabold flex items-center justify-center text-xl shadow-md overflow-hidden border-2 border-amber-200">
                    <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $student['photo'])): ?>
                        <img src="<?= base_url('assets/uploads/' . e($student['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 font-extrabold text-[11px] rounded-full">
                        Class <?= e($student['class_name']) ?> (<?= e($student['section']) ?>)
                    </span>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
                    <p class="text-xs text-slate-500 mt-0.5">Admission No: <strong class="font-mono text-slate-800"><?= e($student['admission_no']) ?></strong> &bull; Father: <strong><?= e($student['father_name']) ?></strong></p>
                </div>
            </div>

            <div class="flex items-center space-x-3 w-full sm:w-auto">
                <a href="<?= base_url('admin/accounts/admission.php') ?>" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 transition-all flex items-center space-x-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Admission Slip</span>
                </a>
            </div>
        </div>

        <!-- Financial Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Invoiced</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block font-mono">₹<?= number_format($total_invoiced, 2) ?></span>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Paid</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block font-mono">₹<?= number_format($total_paid, 2) ?></span>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Current Balance Due</span>
                <span class="text-2xl font-extrabold <?= $total_due > 0 ? 'text-rose-600' : 'text-slate-400' ?> mt-1 block font-mono">₹<?= number_format($total_due, 2) ?></span>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base">Account Ledger Transactions</h3>
                <span class="text-xs text-slate-400"><?= count($ledger) ?> voucher entries found</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            <th class="p-4">Date</th>
                            <th class="p-4">Fee Period / Type</th>
                            <th class="p-4">Method</th>
                            <th class="p-4 text-right">Invoiced Amount</th>
                            <th class="p-4 text-right">Amount Paid</th>
                            <th class="p-4 text-right">Balance</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        <?php if (empty($ledger)): ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 font-medium">No ledger records found for this student.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ledger as $l): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-4 font-bold text-slate-900 font-mono"><?= date('d M Y', strtotime($l['payment_date'])) ?></td>
                                    <td class="p-4 font-semibold text-slate-800"><?= e($l['paid_months'] ?? 'Admission') ?></td>
                                    <td class="p-4 text-slate-500 font-medium"><?= e($l['payment_method']) ?></td>
                                    <td class="p-4 text-right font-mono font-bold text-slate-900">₹<?= number_format($l['total_amount'], 2) ?></td>
                                    <td class="p-4 text-right font-mono font-bold text-emerald-600">₹<?= number_format($l['paid_amount'], 2) ?></td>
                                    <td class="p-4 text-right font-mono font-bold <?= $l['remaining_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                                        ₹<?= number_format($l['remaining_amount'], 2) ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($l['payment_status'] === 'Paid'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                                        <?php elseif ($l['payment_status'] === 'Partial'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Partial</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="<?= base_url('admin/accounts/admission_receipt.php?id=' . $l['id']) ?>" target="_blank" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs rounded-lg border border-amber-200 inline-flex items-center space-x-1">
                                            <i class="fa-solid fa-print"></i>
                                            <span>Slip</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
