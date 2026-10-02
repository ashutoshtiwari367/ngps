<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pre_student_id = intval($_GET['student_id'] ?? 0);
$errors = [];

// List of academic months
$all_months = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];

// Fetch active students with class and father details
$students = $pdo->query("SELECT s.id, s.admission_no, s.first_name, s.last_name, s.father_name, s.class_id, c.class_name, c.section 
    FROM students s 
    JOIN classes c ON s.class_id = c.id 
    WHERE s.status = 'Active' 
    ORDER BY s.first_name ASC")->fetchAll();

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

// Handle POST request BEFORE html header output to prevent header warnings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        $student_id = intval($_POST['student_id'] ?? 0);
        $total_amount = floatval($_POST['total_amount'] ?? 0);
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $remaining_amount = max(0, $total_amount - $paid_amount);
        $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $payment_method = trim($_POST['payment_method'] ?? 'Cash');
        $payment_status = trim($_POST['payment_status'] ?? 'Paid');
        $remarks = trim($_POST['remarks'] ?? '');

        // Selected Months Array
        $selected_months = $_POST['paid_months'] ?? [];
        $paid_months_str = !empty($selected_months) ? implode(', ', $selected_months) : 'Annual Term';

        if (empty($student_id)) $errors[] = "Please select a student.";
        if ($total_amount <= 0) $errors[] = "Total fee amount must be greater than zero.";

        // Find class_id for selected student
        $class_id = 0;
        foreach ($students as $st) {
            if ($st['id'] == $student_id) {
                $class_id = $st['class_id'];
                break;
            }
        }

        if (empty($class_id)) $errors[] = "Class mapping not found for selected student.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO fees (student_id, class_id, total_amount, paid_amount, remaining_amount, paid_months, payment_date, payment_method, payment_status, remarks) 
                    VALUES (:student_id, :class_id, :total_amount, :paid_amount, :remaining_amount, :paid_months, :payment_date, :payment_method, :payment_status, :remarks)");
                
                $stmt->execute([
                    'student_id'       => $student_id,
                    'class_id'         => $class_id,
                    'total_amount'     => $total_amount,
                    'paid_amount'      => $paid_amount,
                    'remaining_amount' => $remaining_amount,
                    'paid_months'      => $paid_months_str,
                    'payment_date'     => $payment_date,
                    'payment_method'   => $payment_method,
                    'payment_status'   => $payment_status,
                    'remarks'          => $remarks
                ]);

                $fee_id = $pdo->lastInsertId();
                set_flash('success', 'Fee payment recorded for (' . e($paid_months_str) . ') successfully!');
                header('Location: ' . base_url('admin/fees/receipt.php?id=' . $fee_id));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database insertion error: " . $e->getMessage();
            }
        }
    }
}

// Now require header for HTML rendering
$page_title = 'Collect Fee Payment';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Collect Fee Payment</h1>
            <p class="text-sm text-slate-500 mt-1">Record student fee collection, select payment months, and calculate remaining dues.</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8 space-y-6">
        
        <?php if (!empty($errors)): ?>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center space-x-2"><i class="fa-solid fa-circle-exclamation text-rose-500"></i><span><?= e($err) ?></span></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Student Quick Finder Tool -->
        <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900 flex items-center space-x-2">
                <i class="fa-solid fa-magnifying-glass text-blue-600"></i>
                <span>Find & Filter Student (By Name, Student ID, Father Name, or Class)</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search Box -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Search Student Name / Admission No / Father Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user-tag text-xs"></i>
                        </div>
                        <input type="text" id="student_search_input" onkeyup="filterStudentList()" placeholder="Type name, STU-2026-001, or father name..."
                            class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>
                </div>

                <!-- Class Filter Dropdown -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter by Class</label>
                    <select id="class_filter_select" onchange="filterStudentList()" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="">-- All Classes --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <form action="add.php" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Student Select List -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Student *</label>
                <select id="student_id" name="student_id" required class="w-full py-3 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($students as $st): 
                        $search_string = strtolower($st['first_name'] . ' ' . $st['last_name'] . ' ' . $st['admission_no'] . ' ' . $st['father_name']);
                    ?>
                        <option value="<?= $st['id'] ?>" 
                                data-class-id="<?= $st['class_id'] ?>" 
                                data-search="<?= e($search_string) ?>"
                                <?= (($pre_student_id == $st['id']) || (($_POST['student_id'] ?? '') == $st['id'])) ? 'selected' : '' ?>>
                            <?= e($st['first_name'] . ' ' . $st['last_name']) ?> | Adm: <?= e($st['admission_no']) ?> | Father: <?= e($st['father_name'] ?: 'N/A') ?> (Class: <?= e($st['class_name']) ?> Sec <?= e($st['section']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Month Selection Checkboxes -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-blue-700 flex items-center justify-between">
                    <span>Select Fee Payment Month(s) *</span>
                    <span class="text-[11px] font-semibold text-slate-500 hover:text-blue-600 cursor-pointer" onclick="toggleSelectAllMonths(this)">Select All</span>
                </label>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
                    <?php 
                    $post_months = $_POST['paid_months'] ?? ['April', 'May'];
                    foreach ($all_months as $m): 
                        $is_checked = in_array($m, $post_months);
                    ?>
                        <label class="flex items-center space-x-2 p-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold cursor-pointer hover:border-blue-400 transition-all">
                            <input type="checkbox" name="paid_months[]" value="<?= $m ?>" class="month-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?= $is_checked ? 'checked' : '' ?>>
                            <span class="text-slate-800"><?= $m ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Financial Calculation Amounts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Total Fee Amount (₹) *</label>
                    <input type="number" step="0.01" id="total_amount" name="total_amount" required value="<?= e($_POST['total_amount'] ?? '15000.00') ?>" placeholder="0.00"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Paid Amount (₹) *</label>
                    <input type="number" step="0.01" id="paid_amount" name="paid_amount" required value="<?= e($_POST['paid_amount'] ?? '15000.00') ?>" placeholder="0.00"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-emerald-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Remaining Amount (₹)</label>
                    <input type="number" step="0.01" id="remaining_amount" name="remaining_amount" readonly value="<?= e($_POST['remaining_amount'] ?? '0.00') ?>"
                        class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-rose-600 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Date *</label>
                    <input type="date" name="payment_date" value="<?= e($_POST['payment_date'] ?? date('Y-m-d')) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Method</label>
                    <select name="payment_method" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI / GPay / PhonePe</option>
                        <option value="Card">Credit / Debit Card</option>
                        <option value="Bank Transfer">Bank Transfer / NEFT</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Status</label>
                    <select id="payment_status" name="payment_status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="Paid" selected>Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Remarks / Payment Notes</label>
                <input type="text" name="remarks" value="<?= e($_POST['remarks'] ?? 'Fee Collection') ?>" placeholder="e.g. Online Ref No..."
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all">
                    Submit & Generate Receipt
                </button>
            </div>
        </form>

    </div>

</div>

<script>
function filterStudentList() {
    const searchVal = document.getElementById('student_search_input').value.toLowerCase().trim();
    const classVal = document.getElementById('class_filter_select').value;
    const select = document.getElementById('student_id');
    const options = select.getElementsByTagName('option');

    for (let i = 0; i < options.length; i++) {
        const opt = options[i];
        if (opt.value === "") continue;

        const optSearch = opt.getAttribute('data-search') || "";
        const optClass = opt.getAttribute('data-class-id') || "";

        const matchesSearch = searchVal === "" || optSearch.includes(searchVal);
        const matchesClass = classVal === "" || optClass === classVal;

        if (matchesSearch && matchesClass) {
            opt.style.display = "";
        } else {
            opt.style.display = "none";
        }
    }
}

function toggleSelectAllMonths(btn) {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    btn.innerText = allChecked ? 'Select All' : 'Deselect All';
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
