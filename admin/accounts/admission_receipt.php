<?php
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$fee_id = intval($_GET['id'] ?? 0);

if ($fee_id <= 0) {
    die("Invalid fee receipt ID.");
}

// Fetch Fee voucher details along with Student and Class info
$stmt = $pdo->prepare("SELECT f.*, 
    s.admission_no, s.first_name, s.last_name, s.father_name, s.mother_name, s.dob, s.gender, s.mobile, s.email, s.address, s.admission_date, s.photo,
    c.class_name, c.section, c.room_number 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    JOIN classes c ON f.class_id = c.id 
    WHERE f.id = :id LIMIT 1");
$stmt->execute(['id' => $fee_id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    die("Admission fee receipt record not found.");
}

$school_name    = get_school_setting('school_name', 'Next Generation Public School');
$school_phone   = get_school_setting('school_phone', '+91 98765 43210');
$school_email   = get_school_setting('school_email', 'accounts@rdmakids.edu');
$school_address = get_school_setting('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001');

$voucher_no = "ADM-REC-" . str_pad($receipt['id'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Slip - <?= e($receipt['admission_no']) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8">

    <!-- Action Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Accounts</span>
        </a>

        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all">
                + New Admission
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Admission Slip</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Slip Container -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-xl print-shadow-none space-y-6">
        
        <!-- School Header -->
        <div class="flex flex-col sm:flex-row items-center justify-between border-b-2 border-amber-500 pb-6 gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white flex items-center justify-center font-black text-2xl shadow-md">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase"><?= e($school_name) ?></h1>
                    <p class="text-xs text-slate-500 max-w-md mt-0.5"><?= e($school_address) ?></p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">Phone: <?= e($school_phone) ?> &bull; Email: <?= e($school_email) ?></p>
                </div>
            </div>

            <div class="text-right sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-900 font-extrabold text-xs rounded-full uppercase tracking-wider mb-1">
                    Official Admission Slip
                </span>
                <p class="text-sm font-extrabold font-mono text-slate-900">Voucher #: <?= e($voucher_no) ?></p>
                <p class="text-xs text-slate-500 font-medium">Date: <?= date('d M Y', strtotime($receipt['payment_date'])) ?></p>
            </div>
        </div>

        <!-- Student & Parent Details Grid -->
        <div class="bg-slate-50/80 p-5 rounded-xl border border-slate-200/80">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-xl bg-white border border-slate-300 overflow-hidden flex items-center justify-center font-extrabold text-xl text-slate-400 shrink-0">
                        <?php if (!empty($receipt['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $receipt['photo'])): ?>
                            <img src="<?= base_url('assets/uploads/' . e($receipt['photo'])) ?>" alt="Student Photo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($receipt['first_name'], 0, 1) . substr($receipt['last_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">
                            Class <?= e($receipt['class_name']) ?> (Section <?= e($receipt['section']) ?>)
                        </span>
                        <h2 class="text-xl font-extrabold text-slate-900 mt-1"><?= e($receipt['first_name'] . ' ' . $receipt['last_name']) ?></h2>
                        <p class="text-xs text-slate-600 font-mono mt-0.5">Admission No: <strong><?= e($receipt['admission_no']) ?></strong></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-6">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Father / Guardian</span>
                        <span class="font-bold text-slate-800"><?= e($receipt['father_name']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Mother Name</span>
                        <span class="font-bold text-slate-800"><?= e($receipt['mother_name'] ?: 'N/A') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Mobile Contact</span>
                        <span class="font-bold text-slate-800 font-mono"><?= e($receipt['mobile']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Admission Date</span>
                        <span class="font-bold text-slate-800"><?= date('d M Y', strtotime($receipt['admission_date'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Itemized Fee Structure Table -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Itemized Fee Structure Breakdown</h3>
            <table class="w-full text-left border-collapse border border-slate-200 rounded-xl overflow-hidden">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold text-slate-600 uppercase tracking-wider">
                        <th class="p-3 border-b border-slate-200">#</th>
                        <th class="p-3 border-b border-slate-200">Fee Component / Head</th>
                        <th class="p-3 border-b border-slate-200 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-medium text-slate-800">
                    <tr>
                        <td class="p-3">1</td>
                        <td class="p-3 font-semibold">One-time Admission & Registration Fee</td>
                        <td class="p-3 text-right font-mono">₹<?= number_format($receipt['admission_fee'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="p-3">2</td>
                        <td class="p-3 font-semibold">Tuition Fee (First Quarter)</td>
                        <td class="p-3 text-right font-mono">₹<?= number_format($receipt['tuition_fee'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="p-3">3</td>
                        <td class="p-3 font-semibold">Annual & Development Fund</td>
                        <td class="p-3 text-right font-mono">₹<?= number_format($receipt['development_fee'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="p-3">4</td>
                        <td class="p-3 font-semibold">Computer & Activity Charges</td>
                        <td class="p-3 text-right font-mono">₹<?= number_format($receipt['computer_fee'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="p-3">5</td>
                        <td class="p-3 font-semibold">Refundable Security Deposit / Caution Money</td>
                        <td class="p-3 text-right font-mono">₹<?= number_format($receipt['security_deposit'], 2) ?></td>
                    </tr>
                    <?php if ($receipt['transport_fee'] > 0): ?>
                        <tr>
                            <td class="p-3">6</td>
                            <td class="p-3 font-semibold">School Transport Fee</td>
                            <td class="p-3 text-right font-mono">₹<?= number_format($receipt['transport_fee'], 2) ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($receipt['discount_amount'] > 0): ?>
                        <tr class="bg-rose-50/50 text-rose-700">
                            <td class="p-3">&bull;</td>
                            <td class="p-3 font-bold">Special Concession / Scholarship Discount (-)</td>
                            <td class="p-3 text-right font-mono font-bold">- ₹<?= number_format($receipt['discount_amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold border-t border-slate-300 text-xs">
                        <td colspan="2" class="p-3 text-right uppercase tracking-wider text-slate-500">Net Admission Fee Total:</td>
                        <td class="p-3 text-right font-mono text-sm font-extrabold text-slate-900">₹<?= number_format($receipt['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Inflow Summary Box -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                <span class="text-emerald-800 font-bold block uppercase text-[10px]">Amount Received</span>
                <span class="text-xl font-extrabold text-emerald-700 font-mono mt-0.5 block">₹<?= number_format($receipt['paid_amount'], 2) ?></span>
                <span class="text-[10px] text-emerald-600 font-medium">Mode: <?= e($receipt['payment_method']) ?></span>
            </div>

            <div class="bg-rose-50 p-4 rounded-xl border border-rose-200">
                <span class="text-rose-800 font-bold block uppercase text-[10px]">Balance Due</span>
                <span class="text-xl font-extrabold text-rose-700 font-mono mt-0.5 block">₹<?= number_format($receipt['remaining_amount'], 2) ?></span>
                <span class="text-[10px] text-rose-600 font-medium">Status: <?= e($receipt['payment_status']) ?></span>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <span class="text-slate-500 font-bold block uppercase text-[10px]">Transaction Note</span>
                <span class="text-xs font-semibold text-slate-800 mt-1 block truncate"><?= e($receipt['remarks'] ?: 'Admission Cleared') ?></span>
                <span class="text-[10px] text-slate-400 font-mono block mt-1">Verified by Accounts</span>
            </div>
        </div>

        <!-- Terms & Signature Footer -->
        <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-end justify-between gap-6">
            <div class="text-[10px] text-slate-400 space-y-1 max-w-sm">
                <p>1. Admission fees once deposited are subject to school bylaws.</p>
                <p>2. Please preserve this admission slip for identity card & tuition reference.</p>
            </div>

            <div class="text-center">
                <div class="w-40 border-b border-slate-800 mb-1"></div>
                <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider block">Authorized Signatory</span>
                <span class="text-[10px] text-slate-400 font-medium block">Accounts Department Stamp</span>
            </div>
        </div>

    </div>

</body>
</html>
