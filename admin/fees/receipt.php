<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$fee_id = intval($_GET['id'] ?? 0);

if (!$fee_id) {
    die("Invalid Fee Receipt ID.");
}

try {
    $stmt = $pdo->prepare("SELECT f.*, s.first_name, s.last_name, s.admission_no, s.father_name, s.mother_name, s.mobile, c.class_name, c.section 
        FROM fees f 
        JOIN students s ON f.student_id = s.id 
        JOIN classes c ON f.class_id = c.id 
        WHERE f.id = :id LIMIT 1");
    $stmt->execute(['id' => $fee_id]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        die("Fee Receipt not found.");
    }

    // Security check: If student logged in, ensure they only view their own receipt
    if (is_student() && get_logged_student_id() != $receipt['student_id']) {
        die("<div style='font-family:sans-serif;padding:2rem;color:#b91c1c;background:#fee2e2;border-radius:12px;margin:2rem auto;max-width:500px;'>Access Denied: You can only view your own receipts.</div>");
    }

} catch (PDOException $e) {
    die("Error retrieving receipt: " . htmlspecialchars($e->getMessage()));
}

$school_name    = get_school_setting('school_name', 'Next Generation Public School');
$school_phone   = get_school_setting('school_phone', '+91 98765 43210');
$school_email   = get_school_setting('school_email', 'accounts@nextgenps.edu.in');
$school_address = get_school_setting('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001');
$school_session = get_school_setting('academic_session', '2026-2027');

$receipt_no = "FEE-" . str_pad($receipt['id'], 4, '0', STR_PAD_LEFT);
$amount_in_words = number_to_words_indian($receipt['paid_amount']);
$clean_student_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $receipt['first_name'] . '_' . $receipt['last_name']);
$pdf_filename = "Fee_Receipt_{$receipt_no}_{$clean_student_name}.pdf";

// Reusable function to render a single Half-A4 Fee Slip
function render_half_a4_slip($receipt, $school_name, $school_address, $school_phone, $school_session, $receipt_no, $amount_in_words, $copy_label = "STUDENT COPY") {
?>
    <div class="half-a4-slip border-2 border-slate-900 bg-white p-4 rounded-xl text-slate-800 relative flex flex-col justify-between overflow-hidden shadow-sm" style="box-sizing: border-box; max-width: 185mm; margin: 0 auto;">
        
        <!-- Visible Subtle School Logo Watermark Layer (Centered) -->
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; z-index: 1; user-select: none;">
            <img src="<?= get_school_logo() ?>" alt="School Watermark" style="width: 220px; height: 220px; object-fit: contain; opacity: 0.12; filter: grayscale(15%); display: block;">
        </div>

        <!-- Top Header with Dual Corner Logos -->
        <div class="relative z-10 border-b-2 border-slate-900 pb-2">
            <div class="flex items-center justify-between gap-3">
                <!-- Left Corner Logo -->
                <img src="<?= get_school_logo() ?>" alt="School Logo" class="w-9 h-9 object-contain rounded-full border border-blue-200 shrink-0 p-0.5 bg-white shadow-xs">
                
                <!-- Center School Identity -->
                <div class="text-center flex-1 px-1">
                    <h1 class="text-sm sm:text-base font-black text-slate-900 tracking-tight uppercase leading-tight"><?= e($school_name) ?></h1>
                    <p class="text-[9px] font-bold text-amber-700 tracking-widest uppercase">विद्या ददाति विनयं</p>
                    <p class="text-[9px] text-slate-600 font-medium leading-tight mt-0.5"><?= e($school_address) ?> &bull; Ph: <?= e($school_phone) ?></p>
                </div>
                
                <!-- Right Corner Logo -->
                <img src="<?= get_school_logo() ?>" alt="School Logo" class="w-9 h-9 object-contain rounded-full border border-blue-200 shrink-0 p-0.5 bg-white shadow-xs">
            </div>

            <!-- Subbar: Receipt #, Date, Session, Copy Badge -->
            <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-slate-200 text-[10px] font-semibold text-slate-700">
                <div class="flex items-center space-x-2">
                    <span class="font-mono font-bold text-slate-900">Receipt #: <strong class="text-blue-800"><?= e($receipt_no) ?></strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Date: <strong><?= date('d M Y', strtotime($receipt['payment_date'])) ?></strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span>Session: <strong><?= e($school_session) ?></strong></span>
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-900 border border-blue-300 rounded font-black text-[9px] uppercase tracking-wider">
                        <?= e($copy_label) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Student & Payment Information Grid (4-Column Layout, Legible Font, Transparent Panels) -->
        <div class="relative z-10 grid grid-cols-4 gap-2 p-2.5 rounded-lg border border-slate-300 text-[10px] my-1.5 font-medium leading-tight" style="background: rgba(248, 250, 252, 0.7);">
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Student Name</span>
                <span class="font-bold text-slate-900 text-[11px] truncate block"><?= e($receipt['first_name'] . ' ' . $receipt['last_name']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Admission No</span>
                <span class="font-mono font-bold text-blue-700 text-[11px] block"><?= e($receipt['admission_no']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Class & Section</span>
                <span class="font-bold text-slate-900 text-[11px] block"><?= e($receipt['class_name']) ?> - Sec <?= e($receipt['section']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Father's Name</span>
                <span class="font-bold text-slate-800 text-[11px] truncate block"><?= e($receipt['father_name'] ?: 'N/A') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Billing Months</span>
                <span class="font-bold text-blue-800 text-[10px] block truncate"><?= e($receipt['paid_months'] ?: 'Annual Term') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Payment Mode</span>
                <span class="font-bold text-slate-800 text-[10.5px] block"><?= e($receipt['payment_method']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Mobile Contact</span>
                <span class="font-mono text-slate-800 text-[10.5px] block"><?= e($receipt['mobile'] ?: 'N/A') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-[8.5px] uppercase font-bold">Payment Status</span>
                <span class="inline-block px-2 py-0.5 rounded font-extrabold text-[9px] uppercase <?= $receipt['payment_status'] === 'Paid' ? 'bg-emerald-100 text-emerald-800' : ($receipt['payment_status'] === 'Partial' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                    <?= e($receipt['payment_status']) ?>
                </span>
            </div>
        </div>

        <!-- Particulars & Amount Breakdown Table (Legible Font & Transparent Rows for Watermark) -->
        <div class="relative z-10 my-1">
            <table class="w-full text-left border-collapse text-[10.5px]">
                <thead>
                    <tr class="border-b-2 border-slate-700 text-[9.5px] font-bold text-slate-700 uppercase tracking-wider" style="background: rgba(241, 245, 249, 0.75);">
                        <th class="py-1.5 px-2">#</th>
                        <th class="py-1.5 px-2">Particulars / Head</th>
                        <th class="py-1.5 px-2 text-right">Total Billed</th>
                        <th class="py-1.5 px-2 text-right">Amount Paid</th>
                        <th class="py-1.5 px-2 text-right">Remaining Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    <tr style="background: rgba(255, 255, 255, 0.5);">
                        <td class="py-1.5 px-2">1</td>
                        <td class="py-1.5 px-2">
                            <strong class="text-slate-900"><?= e($receipt['remarks'] ?: 'Tuition & Academic Fee Collection') ?></strong>
                            <span class="text-slate-500 text-[9px] block">Period: <?= e($receipt['paid_months'] ?: 'Current Term') ?></span>
                        </td>
                        <td class="py-1.5 px-2 text-right font-mono">₹<?= number_format($receipt['total_amount'], 2) ?></td>
                        <td class="py-1.5 px-2 text-right font-mono font-bold text-emerald-700">₹<?= number_format($receipt['paid_amount'], 2) ?></td>
                        <td class="py-1.5 px-2 text-right font-mono font-bold <?= $receipt['remaining_amount'] > 0 ? 'text-rose-600' : 'text-slate-500' ?>">₹<?= number_format($receipt['remaining_amount'], 2) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700 font-bold text-[10.5px]" style="background: rgba(248, 250, 252, 0.8);">
                        <td colspan="2" class="py-1.5 px-2 text-right uppercase text-slate-700">Net Totals:</td>
                        <td class="py-1.5 px-2 text-right font-mono">₹<?= number_format($receipt['total_amount'], 2) ?></td>
                        <td class="py-1.5 px-2 text-right font-mono text-emerald-800 text-[11.5px] font-black">₹<?= number_format($receipt['paid_amount'], 2) ?></td>
                        <td class="py-1.5 px-2 text-right font-mono text-rose-700 text-[10.5px]">₹<?= number_format($receipt['remaining_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Amount In Words & Verified Stamp Banner -->
        <div class="relative z-10 flex items-center justify-between border border-slate-200 px-3 py-1.5 rounded my-1 text-[10px]" style="background: rgba(248, 250, 252, 0.7);">
            <div>
                <span class="text-slate-500 font-bold uppercase text-[8.5px] block">Amount in Words:</span>
                <span class="font-extrabold text-blue-900 italic"><?= e($amount_in_words) ?></span>
            </div>
            <div>
                <div class="border-2 border-emerald-600 text-emerald-700 px-2.5 py-0.5 rounded text-[9px] font-black tracking-widest uppercase inline-block">
                    ✓ <?= strtoupper($receipt['payment_status']) ?> STAMP
                </div>
            </div>
        </div>

        <!-- Signatures & Footer Note -->
        <div class="relative z-10 pt-2 border-t border-slate-300 grid grid-cols-3 gap-2 text-center text-[9px] text-slate-600 leading-tight">
            <div>
                <div class="h-4 border-b border-dashed border-slate-400 w-3/4 mx-auto mb-1"></div>
                <span>Parent / Depositor</span>
            </div>
            <div>
                <div class="h-4 border-b border-dashed border-slate-400 w-3/4 mx-auto mb-1"></div>
                <span>Accounts Clerk</span>
            </div>
            <div>
                <div class="h-4 border-b border-dashed border-slate-400 w-3/4 mx-auto mb-1 flex items-end justify-center">
                    <span class="text-[8px] text-blue-600 font-bold uppercase">Official Seal</span>
                </div>
                <span>Authorized Signatory</span>
            </div>
        </div>

        <div class="relative z-10 text-center text-[8px] text-slate-400 mt-1">
            * Computer generated official fee receipt. Valid subject to realization of Cheque / UPI / Bank Transfer.
        </div>

    </div>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt - <?= e($receipt_no) ?> - <?= e($receipt['first_name']) ?></title>
    
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
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- html2pdf.js for 1-Click Instant High-Resolution PDF Download -->
    <script src="<?= base_url('assets/js/html2pdf.bundle.min.js') ?>"></script>
    <script>
        // Fallback to CDN if local bundle fails
        if (typeof html2pdf === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');
        }
    </script>

    <style>
        /* Exact Half-A4 Page Styling */
        /* Standard A4: 210mm x 297mm. Half A4: 210mm x 148.5mm */
        .half-a4-slip {
            width: 100%;
            max-width: 185mm;
            box-sizing: border-box;
            margin: 0 auto;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            overflow: hidden;
        }

        .perforation-divider {
            border-top: 1.5px dashed #94a3b8;
            margin: 10px auto;
            max-width: 185mm;
            position: relative;
            text-align: center;
        }
        .perforation-divider span {
            position: relative;
            top: -9px;
            background: white;
            padding: 0 10px;
            font-size: 9px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media print {
            .no-print { display: none !important; }
            * { 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            html, body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            .printable-wrapper {
                width: 100% !important;
                max-width: 185mm !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
            .half-a4-slip {
                border: 2px solid #0f172a !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                max-width: 185mm !important;
                width: 100% !important;
                box-sizing: border-box !important;
                margin: 0 auto !important;
            }
            /* Set print page size strictly with safe margin */
            @page {
                size: A4 portrait;
                margin: 6mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 p-4 md:p-6 flex flex-col items-center min-h-screen">

    <!-- Action Bar (Never Printed) -->
    <div class="w-full max-w-4xl mb-5 flex flex-wrap items-center justify-between gap-3 no-print bg-white p-4 rounded-2xl border border-slate-200/90 shadow-md">
        <div class="flex items-center space-x-2">
            <a href="<?= is_student() ? base_url('student/fees.php') : base_url('admin/fees/index.php') ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
            
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                <button type="button" id="btn-mode-single" onclick="setViewMode('single')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm">
                    <i class="fa-solid fa-file text-[11px] mr-1"></i> Half A4 Slip
                </button>
                <button type="button" id="btn-mode-dual" onclick="setViewMode('dual')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all">
                    <i class="fa-solid fa-copy text-[11px] mr-1"></i> Dual Copy (School + Student)
                </button>
            </div>
        </div>

        <div class="flex items-center space-x-2.5">
            <!-- Download PDF Button -->
            <button type="button" id="btn-download-pdf" onclick="downloadReceiptPDF()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/25 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-circle-down"></i>
                <span id="btn-download-text">Download PDF Slip</span>
            </button>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/25 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Slip</span>
            </button>
        </div>
    </div>

    <!-- Printable Receipts Container -->
    <div id="receipt-printable-container" class="printable-wrapper w-full max-w-4xl bg-white p-4 sm:p-6 rounded-2xl shadow-xl border border-slate-200">
        
        <!-- SINGLE SLIP VIEW (HALF A4 PAGE) -->
        <div id="view-single">
            <?php render_half_a4_slip($receipt, $school_name, $school_address, $school_phone, $school_session, $receipt_no, $amount_in_words, "OFFICIAL FEE RECEIPT"); ?>
        </div>

        <!-- DUAL COPY VIEW (SCHOOL COPY + STUDENT COPY ON FULL A4) -->
        <div id="view-dual" class="hidden space-y-4">
            <!-- Copy 1: Student Copy -->
            <?php render_half_a4_slip($receipt, $school_name, $school_address, $school_phone, $school_session, $receipt_no, $amount_in_words, "STUDENT / PARENT COPY"); ?>
            
            <!-- Tear / Perforation Line -->
            <div class="perforation-divider">
                <span><i class="fa-solid fa-scissors mr-1"></i> Cut / Tear along this line</span>
            </div>

            <!-- Copy 2: School Office Copy -->
            <?php render_half_a4_slip($receipt, $school_name, $school_address, $school_phone, $school_session, $receipt_no, $amount_in_words, "SCHOOL / OFFICE COPY"); ?>
        </div>

    </div>

    <script>
        let currentMode = 'single';

        function setViewMode(mode) {
            currentMode = mode;
            const singleDiv = document.getElementById('view-single');
            const dualDiv = document.getElementById('view-dual');
            const btnSingle = document.getElementById('btn-mode-single');
            const btnDual = document.getElementById('btn-mode-dual');

            if (mode === 'dual') {
                singleDiv.classList.add('hidden');
                dualDiv.classList.remove('hidden');

                btnSingle.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all";
                btnDual.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm";
            } else {
                dualDiv.classList.add('hidden');
                singleDiv.classList.remove('hidden');

                btnSingle.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm";
                btnDual.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all";
            }
        }

        function downloadReceiptPDF() {
            const btnText = document.getElementById('btn-download-text');
            const origText = btnText.innerHTML;
            btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generating PDF...';

            const element = (currentMode === 'dual') 
                ? document.getElementById('view-dual') 
                : document.getElementById('view-single');

            // Format settings for Half A4 (A5 landscape) or Full A4 portrait (Dual)
            const isHalfA4 = (currentMode === 'single');
            
            const opt = {
                margin:       isHalfA4 ? [4, 5, 4, 5] : [6, 6, 6, 6],
                filename:     '<?= $pdf_filename ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2.5, useCORS: true, logging: false },
                jsPDF:        { 
                    unit: 'mm', 
                    format: isHalfA4 ? [210, 148] : 'a4', 
                    orientation: isHalfA4 ? 'landscape' : 'portrait' 
                }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btnText.innerHTML = origText;
            }).catch((err) => {
                console.error("PDF generation failed:", err);
                btnText.innerHTML = origText;
                alert("PDF generation error: " + err.message);
            });
        }
    </script>

</body>
</html>
