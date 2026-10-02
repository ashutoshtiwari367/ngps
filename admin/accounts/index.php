<?php
$page_title = 'Accounts Department';
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

// 1. Calculate Revenue Statistics
$total_revenue = (float)$pdo->query("SELECT SUM(paid_amount) FROM fees")->fetchColumn();
$total_admission_fees = (float)$pdo->query("SELECT SUM(admission_fee) FROM fees")->fetchColumn();
$total_pending_dues = (float)$pdo->query("SELECT SUM(remaining_amount) FROM fees")->fetchColumn();
$today_collections = (float)$pdo->query("SELECT SUM(paid_amount) FROM fees WHERE DATE(payment_date) = CURDATE()")->fetchColumn();
$total_receipts_count = (int)$pdo->query("SELECT COUNT(*) FROM fees")->fetchColumn();

// 2. Fetch Recent Transactions
$stmt = $pdo->query("SELECT f.*, s.admission_no, s.first_name, s.last_name, s.father_name, s.photo, c.class_name, c.section 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    JOIN classes c ON f.class_id = c.id 
    ORDER BY f.id DESC LIMIT 10");
$recent_fees = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 via-orange-600 to-yellow-500 text-white font-black flex items-center justify-center text-2xl shadow-lg shadow-amber-500/20">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div>
                <span class="px-3 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-bold border border-amber-200">
                    Accounts & Revenue Management
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-0.5">Accounts Department</h1>
                <p class="text-xs text-slate-500">Comprehensive student admissions, fee structures, receipts & financial ledgers</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>New Student Admission Form</span>
            </a>
            <a href="<?= base_url('admin/fees/add.php') ?>" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-all flex items-center space-x-2">
                <i class="fa-solid fa-receipt"></i>
                <span>Collect Monthly Fee</span>
            </a>
        </div>
    </div>

    <!-- Revenue Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Revenue Collected -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Fee Collected</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900">₹<?= number_format($total_revenue, 2) ?></span>
                <span class="block text-[11px] text-emerald-600 font-semibold mt-0.5">Total Revenue Inflow</span>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today's Collections</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-coins text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-amber-700">₹<?= number_format($today_collections, 2) ?></span>
                <span class="block text-[11px] text-amber-600 font-semibold mt-0.5">Received Today</span>
            </div>
        </div>

        <!-- Admission Fee Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Admission Fees</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-id-card-clip text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900">₹<?= number_format($total_admission_fees, 2) ?></span>
                <span class="block text-[11px] text-blue-600 font-semibold mt-0.5">Registration & Enrollment</span>
            </div>
        </div>

        <!-- Total Pending Dues -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Outstanding Dues</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-rose-600">₹<?= number_format($total_pending_dues, 2) ?></span>
                <span class="block text-[11px] text-rose-600 font-semibold mt-0.5">Pending Student Balances</span>
            </div>
        </div>

    </div>

    <!-- Quick Action Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Admission Form Tile -->
        <a href="<?= base_url('admin/accounts/admission.php') ?>" class="p-5 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl shadow-lg shadow-amber-500/20 hover:scale-[1.01] transition-all space-y-3 group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <span class="text-xs font-bold bg-white/20 px-2.5 py-1 rounded-full uppercase tracking-wider">Single Form</span>
            </div>
            <div>
                <h3 class="font-extrabold text-lg">Full Student Admission & Fee Entry</h3>
                <p class="text-xs text-white/80 mt-1">Register student profile, calculate itemized fee structure, and issue registration slip.</p>
            </div>
            <div class="pt-2 text-xs font-bold flex items-center space-x-2 group-hover:translate-x-1 transition-transform">
                <span>Open Admission Form</span>
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <!-- Fee Collection Tile -->
        <a href="<?= base_url('admin/fees/add.php') ?>" class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm hover:border-amber-400 transition-all space-y-3 group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <span class="text-xs font-bold text-slate-400">Regular Term</span>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Collect Monthly / Term Fees</h3>
                <p class="text-xs text-slate-500 mt-1">Search active students, select payment months, and record payment transaction.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-emerald-600 flex items-center space-x-2 group-hover:translate-x-1 transition-transform">
                <span>Collect Payment &rarr;</span>
            </div>
        </a>

        <!-- Defaulters List Tile -->
        <a href="<?= base_url('admin/accounts/due_list.php') ?>" class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm hover:border-amber-400 transition-all space-y-3 group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Defaulters</span>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Pending Dues & Defaulters List</h3>
                <p class="text-xs text-slate-500 mt-1">Filter unpaid balances, send reminders, and track pending dues per class.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-rose-600 flex items-center space-x-2 group-hover:translate-x-1 transition-transform">
                <span>View Dues Report &rarr;</span>
            </div>
        </a>

    </div>

    <!-- Recent Fee Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Recent Accounts Transactions</h3>
                <p class="text-xs text-slate-500 mt-0.5">Latest admission and monthly fee receipts</p>
            </div>
            <a href="<?= base_url('admin/fees/index.php') ?>" class="text-xs font-bold text-amber-600 hover:underline">View All Fee Records &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4">Student Name & Admission No</th>
                        <th class="p-4">Class</th>
                        <th class="p-4">Fee Period / Type</th>
                        <th class="p-4 text-right">Total Amount</th>
                        <th class="p-4 text-right">Paid Amount</th>
                        <th class="p-4 text-right">Balance Due</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php if (empty($recent_fees)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 font-medium">No fee transactions recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_fees as $f): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 font-extrabold flex items-center justify-center text-xs overflow-hidden border border-slate-200">
                                            <?php if (!empty($f['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $f['photo'])): ?>
                                                <img src="<?= base_url('assets/uploads/' . e($f['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= strtoupper(substr($f['first_name'], 0, 1) . substr($f['last_name'], 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= base_url('admin/accounts/student_ledger.php?student_id=' . $f['student_id']) ?>" class="font-extrabold text-slate-900 hover:text-amber-600 transition-colors block">
                                                <?= e($f['first_name'] . ' ' . $f['last_name']) ?>
                                            </a>
                                            <span class="text-[11px] font-mono text-slate-400">Adm: <?= e($f['admission_no']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-semibold text-slate-800">Class <?= e($f['class_name']) ?> (<?= e($f['section']) ?>)</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        <?= e($f['paid_months'] ?? 'Admission') ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right font-bold text-slate-900">₹<?= number_format($f['total_amount'], 2) ?></td>
                                <td class="p-4 text-right font-bold text-emerald-600">₹<?= number_format($f['paid_amount'], 2) ?></td>
                                <td class="p-4 text-right font-bold <?= $f['remaining_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                                    ₹<?= number_format($f['remaining_amount'], 2) ?>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if ($f['payment_status'] === 'Paid'): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                                    <?php elseif ($f['payment_status'] === 'Partial'): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Partial</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="<?= base_url('admin/accounts/admission_receipt.php?id=' . $f['id']) ?>" target="_blank" title="Print Receipt" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs rounded-lg transition-all border border-amber-200 flex items-center space-x-1">
                                            <i class="fa-solid fa-print"></i>
                                            <span>Receipt</span>
                                        </a>
                                        <a href="<?= base_url('admin/accounts/student_ledger.php?student_id=' . $f['student_id']) ?>" title="Student Financial Ledger" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-all">
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
