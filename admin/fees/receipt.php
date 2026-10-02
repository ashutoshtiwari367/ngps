<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$fee_id = intval($_GET['id'] ?? 0);

if (!$fee_id) {
    die("Invalid Fee Receipt ID.");
}

try {
    $stmt = $pdo->prepare("SELECT f.*, s.first_name, s.last_name, s.admission_no, s.father_name, s.mobile, c.class_name, c.section 
        FROM fees f 
        JOIN students s ON f.student_id = s.id 
        JOIN classes c ON f.class_id = c.id 
        WHERE f.id = :id LIMIT 1");
    $stmt->execute(['id' => $fee_id]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        die("Fee Receipt not found.");
    }
} catch (PDOException $e) {
    die("Error retrieving receipt: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt #FEE-<?= str_pad($receipt['id'], 4, '0', STR_PAD_LEFT) ?></title>
    
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .receipt-card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 p-4 md:p-8 flex flex-col items-center min-h-screen">

    <!-- Action Bar -->
    <div class="w-full max-w-2xl mb-6 flex items-center justify-between no-print">
        <a href="<?= base_url('admin/fees/index.php') ?>" class="px-4 py-2 bg-white text-slate-700 font-semibold text-xs rounded-xl shadow border border-slate-200 hover:bg-slate-50 transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Fee Records</span>
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
            <i class="fa-solid fa-print"></i>
            <span>Print Official Receipt</span>
        </button>
    </div>

    <!-- Receipt Card Printable -->
    <div class="receipt-card w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8 sm:p-10 space-y-6">
        
        <!-- Header / Logo & Receipt Meta -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 pb-6 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-2xl shadow-md">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight"><?= strtoupper(e(get_school_setting('school_name', 'NEXT GENERATION PUBLIC SCHOOL'))) ?></h1>
                    <p class="text-xs text-slate-500"><?= e(get_school_setting('school_address', 'Next Generation Campus, Knowledge Enclave')) ?> &bull; Ph: <?= e(get_school_setting('school_phone', '+91 98765 43210')) ?></p>
                </div>
            </div>

            <div class="text-left sm:text-right bg-slate-50 p-3 rounded-xl border border-slate-100 font-mono">
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Official Fee Receipt</span>
                <span class="text-base font-extrabold text-blue-700 block">#FEE-<?= str_pad($receipt['id'], 4, '0', STR_PAD_LEFT) ?></span>
                <span class="text-xs text-slate-600 block mt-0.5"><?= e($receipt['payment_date']) ?></span>
            </div>
        </div>

        <!-- Student & Payment Information Grid -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100 text-xs font-medium text-slate-700">
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Student Name</span>
                <span class="text-sm font-bold text-slate-900 block"><?= e($receipt['first_name'] . ' ' . $receipt['last_name']) ?></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Admission Number</span>
                <span class="text-sm font-mono font-bold text-blue-700 block"><?= e($receipt['admission_no']) ?></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Class & Section</span>
                <span class="font-bold text-slate-800"><?= e($receipt['class_name']) ?> (Section <?= e($receipt['section']) ?>)</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Father's Name</span>
                <span class="font-bold text-slate-800"><?= e($receipt['father_name'] ?: 'N/A') ?></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Fee Months Covered</span>
                <span class="font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 inline-block my-0.5"><?= e($receipt['paid_months'] ?: 'Annual Term') ?></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Payment Method</span>
                <span class="font-bold text-slate-800"><?= e($receipt['payment_method']) ?></span>
            </div>
        </div>

        <!-- Payment Particulars Breakdown Table -->
        <div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Description / Remarks</th>
                        <th class="py-2.5 px-3 text-right">Total Fee</th>
                        <th class="py-2.5 px-3 text-right">Amount Paid</th>
                        <th class="py-2.5 px-3 text-right">Balance Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    <tr>
                        <td class="py-3.5 px-3">
                            <span class="font-bold block text-sm"><?= e($receipt['remarks'] ?: 'Fee Collection') ?></span>
                            <span class="text-[11px] text-blue-600 font-semibold">Months: <?= e($receipt['paid_months'] ?: 'Annual Term') ?></span>
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono">₹<?= number_format($receipt['total_amount'], 2) ?></td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-emerald-700">₹<?= number_format($receipt['paid_amount'], 2) ?></td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-rose-600">₹<?= number_format($receipt['remaining_amount'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Totals & Status Stamp -->
        <div class="flex justify-between items-center pt-4 border-t border-slate-200">
            <div>
                <!-- Payment Status Stamp Badge -->
                <div class="inline-block border-2 px-4 py-1.5 rounded-xl font-extrabold tracking-widest text-xs uppercase transform -rotate-2 <?= $receipt['payment_status'] === 'Paid' ? 'border-emerald-500 text-emerald-600 bg-emerald-50' : ($receipt['payment_status'] === 'Partial' ? 'border-amber-500 text-amber-600 bg-amber-50' : 'border-rose-500 text-rose-600 bg-rose-50') ?>">
                    <?= e($receipt['payment_status']) ?> STAMP
                </div>
            </div>

            <div class="text-right space-y-1 font-mono text-xs">
                <div class="flex justify-between space-x-6 text-slate-500">
                    <span>Total Amount:</span>
                    <span>₹<?= number_format($receipt['total_amount'], 2) ?></span>
                </div>
                <div class="flex justify-between space-x-6 font-bold text-emerald-700 text-sm">
                    <span>Paid Received:</span>
                    <span>₹<?= number_format($receipt['paid_amount'], 2) ?></span>
                </div>
                <div class="flex justify-between space-x-6 font-bold text-rose-600">
                    <span>Balance Remaining:</span>
                    <span>₹<?= number_format($receipt['remaining_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Signatures & Footer Note -->
        <div class="pt-8 grid grid-cols-2 gap-8 text-center text-xs text-slate-500">
            <div>
                <div class="h-10 border-b border-slate-300 w-3/4 mx-auto mb-1"></div>
                <span>Payer Signature</span>
            </div>
            <div>
                <div class="h-10 border-b border-slate-300 w-3/4 mx-auto mb-1 flex items-end justify-center pb-1">
                    <span class="text-[10px] text-blue-600 font-bold tracking-wider uppercase">Authorized Seal</span>
                </div>
                <span>Accounts Officer Signature</span>
            </div>
        </div>

        <p class="text-center text-[10px] text-slate-400 pt-4 border-t border-slate-100">
            This is a computer generated fee receipt. No physical signature is required.
        </p>

    </div>

</body>
</html>
