<?php
$page_title = 'My Fees & Payments';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch Fee records
$stmt = $pdo->prepare("SELECT f.*, c.class_name, c.section 
    FROM fees f 
    LEFT JOIN classes c ON f.class_id = c.id 
    WHERE f.student_id = :sid 
    ORDER BY f.id DESC");
$stmt->execute(['sid' => $student_id_pk]);
$fees = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Fee Account & Statements</h1>
            <p class="text-xs text-slate-500 mt-1">Review tuition fees, payments, and print receipts</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Fee Cards List -->
    <div class="space-y-4">
        <?php if (empty($fees)): ?>
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 font-medium text-xs">
                No fee invoices or records issued yet.
            </div>
        <?php else: ?>
            <?php foreach ($fees as $fee): ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Billing Period / Month</span>
                            <h3 class="text-lg font-extrabold text-slate-900"><?= e($fee['month'] ?? 'Current Term') ?></h3>
                        </div>
                        <div>
                            <?php if ($fee['payment_status'] === 'Paid'): ?>
                                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center">
                                    <i class="fa-solid fa-circle-check mr-1.5 text-xs"></i> Paid in Full
                                </span>
                            <?php elseif ($fee['payment_status'] === 'Partial'): ?>
                                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center">
                                    <i class="fa-solid fa-clock mr-1.5 text-xs"></i> Partial Payment
                                </span>
                            <?php else: ?>
                                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center">
                                    <i class="fa-solid fa-circle-exclamation mr-1.5 text-xs"></i> Unpaid / Pending
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[11px]">Total Fee Billed</span>
                            <span class="text-lg font-extrabold text-slate-900 mt-0.5 block">₹<?= number_format($fee['total_amount'], 2) ?></span>
                        </div>
                        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-100">
                            <span class="text-emerald-700 font-semibold block text-[11px]">Amount Paid</span>
                            <span class="text-lg font-extrabold text-emerald-700 mt-0.5 block">₹<?= number_format($fee['paid_amount'], 2) ?></span>
                        </div>
                        <div class="bg-rose-50/50 p-3.5 rounded-xl border border-rose-100">
                            <span class="text-rose-700 font-semibold block text-[11px]">Remaining Balance</span>
                            <span class="text-lg font-extrabold text-rose-700 mt-0.5 block">₹<?= number_format($fee['remaining_amount'], 2) ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-slate-500">
                            Payment Date: <strong class="text-slate-800"><?= !empty($fee['payment_date']) ? date('d M Y', strtotime($fee['payment_date'])) : 'Pending' ?></strong>
                        </span>

                        <a href="<?= base_url('admin/fees/receipt.php?id=' . $fee['id']) ?>" target="_blank" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl transition-all inline-flex items-center space-x-1.5 border border-indigo-200">
                            <i class="fa-solid fa-print"></i>
                            <span>Print Receipt</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
