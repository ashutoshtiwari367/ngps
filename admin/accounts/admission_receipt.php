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
$school_email   = get_school_setting('school_email', 'accounts@nextgenps.edu.in');
$school_address = get_school_setting('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001');
$school_session = get_school_setting('academic_session', '2026-2027');

$voucher_no = "ADM-REC-" . str_pad($receipt['id'], 5, '0', STR_PAD_LEFT);
$amount_in_words = number_to_words_indian($receipt['paid_amount']);
$clean_student_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $receipt['first_name'] . '_' . $receipt['last_name']);
$pdf_filename = "Admission_Slip_{$voucher_no}_{$clean_student_name}.pdf";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Slip - <?= e($receipt['admission_no']) ?> - <?= e($receipt['first_name']) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= get_school_logo() ?>">
    <link rel="apple-touch-icon" href="<?= get_school_logo() ?>">
    
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- html2pdf.js for 1-Click Instant High-Resolution PDF Download -->
    <script src="<?= base_url('assets/js/html2pdf.bundle.min.js') ?>"></script>
    <script>
        if (typeof html2pdf === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');
        }
    </script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-shadow-none { box-shadow: none !important; }
            #admission-slip-printable {
                border: 1.5px solid #0f172a !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                max-width: 196mm !important;
                margin: 0 auto !important;
            }
            @page {
                size: A4 portrait;
                margin: 4mm 6mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8">

    <!-- Action Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 no-print bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
        <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Accounts</span>
        </a>

        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all">
                + New Admission
            </a>
            
            <!-- Download PDF Button -->
            <button type="button" id="btn-download-pdf" onclick="downloadAdmissionSlipPDF()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-circle-down"></i>
                <span id="btn-download-text">Download PDF Slip</span>
            </button>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Admission Slip</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Slip Container -->
    <div id="admission-slip-printable" class="max-w-[196mm] mx-auto bg-white p-4 sm:p-5 rounded-xl border-2 border-slate-900 shadow-xl print-shadow-none space-y-2 relative overflow-hidden">
        
        <!-- Light School Logo Watermark Layer (Centered & Subtle) -->
        <div class="watermark-bg pointer-events-none absolute inset-0 flex items-center justify-center select-none z-0">
            <img src="<?= get_school_logo() ?>" alt="Watermark" class="w-60 h-60 object-contain opacity-[0.055] grayscale">
        </div>

        <!-- School Header with Dual Corner Logos -->
        <div class="relative z-10 border-b-2 border-slate-900 pb-2">
            <div class="flex items-center justify-between gap-2">
                <!-- Left Corner Logo -->
                <img src="<?= get_school_logo() ?>" alt="Logo Left" class="w-13 h-13 object-contain rounded-full border border-amber-300 p-0.5 shrink-0 shadow-xs bg-white">
                
                <!-- Center School Identity -->
                <div class="text-center flex-1 px-2">
                    <h1 class="text-base sm:text-lg font-black tracking-tight text-slate-900 uppercase leading-tight"><?= e($school_name) ?></h1>
                    <p class="text-[9px] font-bold text-amber-700 tracking-widest uppercase">विद्या ददाति विनयं</p>
                    <p class="text-[9px] text-slate-600 font-medium leading-tight mt-0.5"><?= e($school_address) ?></p>
                    <p class="text-[8.5px] text-slate-500 font-mono mt-0.5">Phone: <?= e($school_phone) ?> &bull; Email: <?= e($school_email) ?></p>
                </div>

                <!-- Right Corner Logo -->
                <img src="<?= get_school_logo() ?>" alt="Logo Right" class="w-13 h-13 object-contain rounded-full border border-amber-300 p-0.5 shrink-0 shadow-xs bg-white">
            </div>

            <!-- Subbar: Voucher #, Date, Session, Official Badge -->
            <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] font-semibold text-slate-700">
                <div class="flex items-center space-x-2">
                    <span class="font-mono font-bold text-slate-900">Voucher #: <strong class="text-amber-800"><?= e($voucher_no) ?></strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Date: <strong><?= date('d M Y', strtotime($receipt['payment_date'])) ?></strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span>Session: <strong><?= e($school_session) ?></strong></span>
                    <span class="px-2 py-0.2 bg-amber-100 text-amber-900 border border-amber-300 rounded font-black text-[8.5px] uppercase tracking-wider">
                        Official Admission Slip
                    </span>
                </div>
            </div>
        </div>

        <!-- Student & Parent Details Grid (Compact, Small Font) -->
        <div class="relative z-10 bg-slate-50/90 p-2.5 rounded-lg border border-slate-300 text-[9.5px]">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-14 h-14 rounded-lg bg-white border border-slate-300 overflow-hidden flex items-center justify-center font-black text-sm text-slate-400 shrink-0 shadow-xs">
                        <?php if (!empty($receipt['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $receipt['photo'])): ?>
                            <img src="<?= base_url('assets/uploads/' . e($receipt['photo'])) ?>" alt="Student Photo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($receipt['first_name'], 0, 1) . substr($receipt['last_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="px-2 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold rounded-full inline-block">
                            Class <?= e($receipt['class_name']) ?> (Sec <?= e($receipt['section']) ?>)
                        </span>
                        <h2 class="text-sm font-black text-slate-900 leading-tight mt-0.5"><?= e($receipt['first_name'] . ' ' . $receipt['last_name']) ?></h2>
                        <p class="text-[9px] text-slate-600 font-mono">Admission No: <strong class="text-blue-700"><?= e($receipt['admission_no']) ?></strong></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[9px] border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-4 leading-tight">
                    <div>
                        <span class="text-slate-400 block text-[8px] font-bold uppercase">Father / Guardian</span>
                        <span class="font-bold text-slate-800"><?= e($receipt['father_name']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[8px] font-bold uppercase">Mother Name</span>
                        <span class="font-bold text-slate-800"><?= e($receipt['mother_name'] ?: 'N/A') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[8px] font-bold uppercase">Mobile Contact</span>
                        <span class="font-bold text-slate-800 font-mono"><?= e($receipt['mobile']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[8px] font-bold uppercase">Admission Date</span>
                        <span class="font-bold text-slate-800"><?= date('d M Y', strtotime($receipt['admission_date'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Itemized Fee Structure Table (Compact, Small Font) -->
        <div class="relative z-10 my-1">
            <table class="w-full text-left border-collapse border border-slate-300 rounded overflow-hidden text-[9px]">
                <thead>
                    <tr class="bg-slate-100 text-[8.5px] font-bold text-slate-700 uppercase tracking-wider">
                        <th class="py-1 px-2 border-b border-slate-300">#</th>
                        <th class="py-1 px-2 border-b border-slate-300">Fee Component / Particulars</th>
                        <th class="py-1 px-2 border-b border-slate-300 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                    <?php 
                    $heads_list = [
                        'Registration Fee'         => (float)($receipt['registration_fee'] ?? 0),
                        'Admission Fee'            => (float)($receipt['admission_fee'] ?? 0),
                        'Annual Fee'               => (float)($receipt['annual_fee'] ?? 0),
                        'Tuition Fee'              => (float)($receipt['tuition_fee'] ?? 0),
                        'Library Fee'              => (float)($receipt['library_fee'] ?? 0),
                        'Computer Fee'             => (float)($receipt['computer_fee'] ?? 0),
                        'Activity Fee'             => (float)($receipt['activity_fee'] ?? 0),
                        'Magazine Fee'             => (float)($receipt['magazine_fee'] ?? 0),
                        'Examination Fee'          => (float)($receipt['exam_fee'] ?? 0),
                        'Penalty Fee (Late/Other)' => (float)($receipt['penalty_fee'] ?? 0),
                        'Other Fee'                => (float)($receipt['other_fee'] ?? 0),
                    ];

                    // Filter to heads that have amounts > 0 or show all standard if none specifically set
                    $has_any = false;
                    foreach ($heads_list as $amt) { if ($amt > 0) { $has_any = true; break; } }
                    
                    $i = 1;
                    foreach ($heads_list as $head_name => $head_amount):
                        if ($has_any && $head_amount <= 0) continue; // Only show billed heads
                    ?>
                        <tr>
                            <td class="py-0.5 px-2"><?= $i++ ?></td>
                            <td class="py-0.5 px-2 font-semibold text-slate-900"><?= e($head_name) ?></td>
                            <td class="py-0.5 px-2 text-right font-mono">₹<?= number_format($head_amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ((float)($receipt['discount_amount'] ?? 0) > 0): ?>
                        <tr class="bg-rose-50/50 text-rose-700 font-bold">
                            <td class="py-0.5 px-2">&bull;</td>
                            <td class="py-0.5 px-2">Special Scholarship / Concession Discount (-)</td>
                            <td class="py-0.5 px-2 text-right font-mono font-bold">- ₹<?= number_format($receipt['discount_amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold border-t-2 border-slate-700 text-[9.5px]">
                        <td colspan="2" class="py-1 px-2 text-right uppercase tracking-wider text-slate-700">Net Admission Fee Total:</td>
                        <td class="py-1 px-2 text-right font-mono font-black text-slate-900 text-[10.5px]">₹<?= number_format($receipt['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Inflow Summary Box (Compact) -->
        <div class="relative z-10 grid grid-cols-3 gap-2 text-[9px]">
            <div class="bg-emerald-50/80 p-2 rounded border border-emerald-200">
                <span class="text-emerald-800 font-bold block uppercase text-[8px]">Amount Received</span>
                <span class="text-sm font-black text-emerald-700 font-mono block">₹<?= number_format($receipt['paid_amount'], 2) ?></span>
                <span class="text-[8px] text-emerald-600 font-medium">Mode: <?= e($receipt['payment_method']) ?></span>
            </div>

            <div class="bg-rose-50/80 p-2 rounded border border-rose-200">
                <span class="text-rose-800 font-bold block uppercase text-[8px]">Balance Due</span>
                <span class="text-sm font-black text-rose-700 font-mono block">₹<?= number_format($receipt['remaining_amount'], 2) ?></span>
                <span class="text-[8px] text-rose-600 font-medium">Status: <?= e($receipt['payment_status']) ?></span>
            </div>

            <div class="bg-slate-50/80 p-2 rounded border border-slate-200">
                <span class="text-slate-500 font-bold block uppercase text-[8px]">Transaction Remarks</span>
                <span class="text-[9px] font-semibold text-slate-800 truncate block"><?= e($receipt['remarks'] ?: 'Admission Cleared') ?></span>
                <span class="text-[8px] text-slate-400 font-mono block">Verified by Accounts</span>
            </div>
        </div>

        <!-- Amount In Words Banner (Compact) -->
        <div class="relative z-10 flex items-center justify-between bg-amber-50/80 border border-amber-200 px-2.5 py-1 rounded text-[9px]">
            <div>
                <span class="text-amber-800 font-bold uppercase text-[8px] block">Amount Received in Words:</span>
                <span class="font-extrabold text-slate-900 italic"><?= e($amount_in_words) ?></span>
            </div>
            <div class="border-2 border-emerald-600 text-emerald-700 px-2 py-0.2 rounded text-[8px] font-black tracking-widest uppercase">
                ✓ VERIFIED ADMISSION
            </div>
        </div>

        <!-- Terms & Signature Footer (Compact, Never Splits Page) -->
        <div class="relative z-10 pt-2 border-t border-slate-300 flex items-end justify-between gap-4 text-[8px] leading-tight">
            <div class="text-slate-400 space-y-0.5 max-w-xs">
                <p>1. Admission fees once deposited are subject to school bylaws.</p>
                <p>2. Please preserve this admission slip for identity card & tuition reference.</p>
            </div>

            <div class="text-center">
                <div class="w-32 border-b border-slate-800 mx-auto mb-0.5"></div>
                <span class="text-[9px] font-extrabold text-slate-800 uppercase tracking-wider block">Authorized Signatory</span>
                <span class="text-[8px] text-slate-400 font-medium block">Accounts Department Stamp</span>
            </div>
        </div>

    </div>

    <script>
        function downloadAdmissionSlipPDF() {
            const btnText = document.getElementById('btn-download-text');
            const origText = btnText.innerHTML;
            btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generating PDF...';

            const element = document.getElementById('admission-slip-printable');

            const opt = {
                margin:       [4, 6, 4, 6],
                filename:     '<?= $pdf_filename ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2.2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btnText.innerHTML = origText;
            }).catch((err) => {
                console.error("PDF generation error:", err);
                btnText.innerHTML = origText;
                alert("PDF generation failed: " + err.message);
            });
        }
    </script>

</body>
</html>
