<?php
$page_title = 'Fee Defaulters & Pending Dues';
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$class_filter = intval($_GET['class_id'] ?? 0);

$classes = get_all_classes($pdo);

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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center space-x-3.5">
            <img src="<?= get_school_logo() ?>" alt="Next Generation Public School" class="w-14 h-14 object-contain rounded-full border border-slate-200 p-0.5 shadow-sm">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">Next Generation Public School</h1>
                <p class="text-[10px] font-bold text-rose-700 tracking-wider">विद्या ददाति विनयं &bull; Outstanding Fee Dues & Defaulters Register</p>
            </div>
        </div>

        <!-- Action & Filter Buttons -->
        <div class="flex items-center space-x-2">
            <button type="button" id="btn-download-pdf" onclick="downloadDueListPDF()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-circle-down"></i>
                <span id="btn-download-text">Download PDF</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
            <form method="GET" action="due_list.php" class="w-48">
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
    <div id="defaulters-list-printable-card" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
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

<script src="<?= base_url('assets/js/html2pdf.bundle.min.js') ?>"></script>
<script>
    if (typeof html2pdf === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');
    }

    function downloadDueListPDF() {
        const btnText = document.getElementById('btn-download-text');
        const origText = btnText.innerHTML;
        btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generating...';

        const element = document.getElementById('defaulters-list-printable-card');
        
        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     'Fee_Defaulters_List_' + '<?= date('Y-m-d') ?>' + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2.2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            btnText.innerHTML = origText;
        }).catch((err) => {
            btnText.innerHTML = origText;
            alert("PDF generation failed: " + err.message);
        });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
