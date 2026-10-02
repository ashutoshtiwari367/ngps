<?php
$page_title = 'Student Profile';
require_once __DIR__ . '/../../includes/header.php';

$student_id = intval($_GET['id'] ?? 0);

if (!$student_id) {
    set_flash('error', 'Invalid student ID.');
    header('Location: ' . base_url('admin/students/index.php'));
    exit();
}

try {
    // Fetch student with class details
    $stmt = $pdo->prepare("SELECT s.*, c.class_name, c.room_number 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE s.id = :id LIMIT 1");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        set_flash('error', 'Student not found.');
        header('Location: ' . base_url('admin/students/index.php'));
        exit();
    }

    // Fetch fee records
    $fees_stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = :student_id ORDER BY id DESC");
    $fees_stmt->execute(['student_id' => $student_id]);
    $fee_records = $fees_stmt->fetchAll();

    // Calculate fee totals
    $total_fee_sum = 0;
    $total_paid_sum = 0;
    $total_rem_sum = 0;
    foreach ($fee_records as $f) {
        $total_fee_sum += $f['total_amount'];
        $total_paid_sum += $f['paid_amount'];
        $total_rem_sum += $f['remaining_amount'];
    }

    // Fetch attendance summary
    $att_stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_days,
        COUNT(CASE WHEN status = 'Present' THEN 1 END) as present_days,
        COUNT(CASE WHEN status = 'Absent' THEN 1 END) as absent_days
        FROM attendance WHERE student_id = :student_id");
    $att_stmt->execute(['student_id' => $student_id]);
    $att_summary = $att_stmt->fetch();

    $att_total = $att_summary['total_days'] ?: 0;
    $att_present = $att_summary['present_days'] ?: 0;
    $att_pct = ($att_total > 0) ? round(($att_present / $att_total) * 100) : 0;

} catch (PDOException $e) {
    set_flash('error', 'Database error: ' . $e->getMessage());
    header('Location: ' . base_url('admin/students/index.php'));
    exit();
}
?>

<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Profile</h1>
            <p class="text-sm text-slate-500 mt-1">Detailed academic, financial, and attendance dossier.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="edit.php?id=<?= $student['id'] ?>" class="px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 font-semibold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Profile</span>
            </a>
            <a href="<?= base_url('admin/fees/add.php?student_id=' . $student['id']) ?>" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-receipt"></i>
                <span>Collect Fee</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Profile</span>
            </button>
        </div>
    </div>

    <!-- Student Info Card Header -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            
            <!-- Photo -->
            <div class="w-32 h-32 rounded-2xl bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-3xl overflow-hidden border-2 border-blue-200 shadow-md shrink-0">
                <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $student['photo'])): ?>
                    <img src="<?= base_url('assets/uploads/' . e($student['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                <?php else: ?>
                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>

            <!-- Basic Details -->
            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <h2 class="text-2xl font-extrabold text-slate-900"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
                    <span class="px-3 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-100 text-blue-700 border border-blue-200">
                        <?= e($student['admission_no']) ?>
                    </span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <?= e($student['status']) ?>
                    </span>
                </div>

                <p class="text-sm font-semibold text-slate-600">
                    <i class="fa-solid fa-school text-blue-500 mr-1"></i> <?= e($student['class_name']) ?> - Section <?= e($student['section']) ?> (<?= e($student['room_number'] ?? 'N/A') ?>)
                </p>

                <div class="pt-3 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-medium text-slate-600 border-t border-slate-100 mt-3">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Gender</span>
                        <span class="font-bold text-slate-800"><?= e($student['gender']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Date of Birth</span>
                        <span class="font-bold text-slate-800"><?= e($student['dob']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Mobile</span>
                        <span class="font-bold text-slate-800 font-mono"><?= e($student['mobile']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Admission Date</span>
                        <span class="font-bold text-slate-800"><?= e($student['admission_date']) ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 2 Column Details: Parents & Contact Info vs Attendance Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Left: Guardian & Address -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                <i class="fa-solid fa-users text-blue-500"></i>
                <span>Family & Contact Details</span>
            </h3>

            <div class="space-y-3 text-xs font-medium text-slate-700">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Father's Name:</span>
                    <span class="font-bold text-slate-800"><?= e($student['father_name'] ?: 'N/A') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Mother's Name:</span>
                    <span class="font-bold text-slate-800"><?= e($student['mother_name'] ?: 'N/A') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Email Address:</span>
                    <span class="font-bold text-slate-800"><?= e($student['email'] ?: 'N/A') ?></span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Residential Address:</span>
                    <span class="font-bold text-slate-800 text-right max-w-xs"><?= e($student['address'] ?: 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Right: Attendance Analytics -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                <i class="fa-solid fa-chart-line text-purple-500"></i>
                <span>Attendance Summary</span>
            </h3>

            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-100">
                <div>
                    <span class="text-xs text-purple-700 font-bold block uppercase">Total Attendance Rate</span>
                    <span class="text-3xl font-extrabold text-purple-900 mt-1 block"><?= $att_pct ?>%</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xl shadow">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center text-xs font-semibold">
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px]">Total Days</span>
                    <span class="font-mono font-bold text-slate-800 text-sm"><?= $att_total ?></span>
                </div>
                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-800">
                    <span class="text-emerald-600 block text-[10px]">Present</span>
                    <span class="font-mono font-bold text-sm"><?= $att_present ?></span>
                </div>
                <div class="p-2.5 bg-rose-50 rounded-xl border border-rose-100 text-rose-800">
                    <span class="text-rose-600 block text-[10px]">Absent</span>
                    <span class="font-mono font-bold text-sm"><?= $att_summary['absent_days'] ?: 0 ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Fee History Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-receipt text-emerald-500"></i>
                <span>Fee Payments & Dues Ledger</span>
            </h3>
            <div class="text-xs font-semibold text-slate-500">
                Total Paid: <span class="font-mono font-bold text-emerald-700">₹<?= number_format($total_paid_sum) ?></span> | 
                Remaining Due: <span class="font-mono font-bold text-rose-600">₹<?= number_format($total_rem_sum) ?></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Receipt ID</th>
                        <th class="py-3 px-4">Payment Date</th>
                        <th class="py-3 px-4">Total Fee</th>
                        <th class="py-3 px-4">Paid Amount</th>
                        <th class="py-3 px-4">Remaining</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($fee_records)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No fee payment history found for this student.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fee_records as $f): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#FEE-<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3 px-4"><?= e($f['payment_date']) ?></td>
                                <td class="py-3 px-4 font-mono">₹<?= number_format($f['total_amount'], 2) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-700">₹<?= number_format($f['paid_amount'], 2) ?></td>
                                <td class="py-3 px-4 font-mono font-bold <?= $f['remaining_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                                    ₹<?= number_format($f['remaining_amount'], 2) ?>
                                </td>
                                <td class="py-3 px-4"><?= e($f['payment_method']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $f['payment_status'] === 'Paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($f['payment_status'] === 'Partial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') ?>">
                                        <?= e($f['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="<?= base_url('admin/fees/receipt.php?id=' . $f['id']) ?>" target="_blank" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors font-semibold flex items-center justify-center space-x-1">
                                        <i class="fa-solid fa-print"></i>
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
