<?php
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$errors = [];
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

// Auto-generate next admission number
$max_id_stmt = $pdo->query("SELECT MAX(id) FROM students");
$next_id = ($max_id_stmt->fetchColumn() ?: 0) + 1;
$auto_adm_no = "STU-2026-" . str_pad($next_id, 3, '0', STR_PAD_LEFT);

// Handle POST request BEFORE HTML header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        // 1. Student Personal & Academic Data
        $admission_no   = trim($_POST['admission_no'] ?? '');
        $first_name     = trim($_POST['first_name'] ?? '');
        $last_name      = trim($_POST['last_name'] ?? '');
        $father_name    = trim($_POST['father_name'] ?? '');
        $mother_name    = trim($_POST['mother_name'] ?? '');
        $dob            = trim($_POST['dob'] ?? '');
        $gender         = trim($_POST['gender'] ?? 'Male');
        $class_id       = intval($_POST['class_id'] ?? 0);
        $section        = trim($_POST['section'] ?? 'A');
        $mobile         = trim($_POST['mobile'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $admission_date = trim($_POST['admission_date'] ?? date('Y-m-d'));
        $status         = 'Active';

        // 2. Fee Breakdown Data
        $admission_fee   = floatval($_POST['admission_fee'] ?? 5000);
        $tuition_fee     = floatval($_POST['tuition_fee'] ?? 4000);
        $development_fee = floatval($_POST['development_fee'] ?? 2000);
        $computer_fee    = floatval($_POST['computer_fee'] ?? 1000);
        $security_deposit = floatval($_POST['security_deposit'] ?? 1000);
        $transport_fee   = floatval($_POST['transport_fee'] ?? 0);
        $discount_amount = floatval($_POST['discount_amount'] ?? 0);

        // Calculated Total Fee
        $gross_total  = $admission_fee + $tuition_fee + $development_fee + $computer_fee + $security_deposit + $transport_fee;
        $total_amount = max(0, $gross_total - $discount_amount);

        // 3. Payment Collection Data
        $paid_amount      = floatval($_POST['paid_amount'] ?? $total_amount);
        $remaining_amount = max(0, $total_amount - $paid_amount);
        $payment_date     = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $payment_method   = trim($_POST['payment_method'] ?? 'Cash');
        $payment_status   = trim($_POST['payment_status'] ?? 'Paid');
        $remarks          = trim($_POST['remarks'] ?? 'Initial Admission Fee');

        // Validation
        if (empty($admission_no)) $errors[] = "Admission number is required.";
        if (empty($first_name)) $errors[] = "Student first name is required.";
        if (empty($last_name)) $errors[] = "Student last name is required.";
        if (empty($class_id)) $errors[] = "Please select an assigned class.";
        if (empty($mobile)) $errors[] = "Mobile number is required.";
        if ($total_amount <= 0) $errors[] = "Total fee amount must be greater than zero.";

        // Handle Photo Upload
        $photo_name = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_orig = $_FILES['photo']['name'];
            $file_ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $photo_name = "stu_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
                $upload_dir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $upload_dir . $photo_name);
            } else {
                $errors[] = "Invalid photo format. Only JPG, PNG, and WEBP images are allowed.";
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Step A: Insert Student Profile (password defaults to student123)
                $default_student_pwd = password_hash('student123', PASSWORD_BCRYPT);
                $stu_stmt = $pdo->prepare("INSERT INTO students 
                    (admission_no, password, first_name, last_name, father_name, mother_name, dob, gender, class_id, section, mobile, email, address, admission_date, photo, status) 
                    VALUES 
                    (:admission_no, :password, :first_name, :last_name, :father_name, :mother_name, :dob, :gender, :class_id, :section, :mobile, :email, :address, :admission_date, :photo, :status)");
                
                $stu_stmt->execute([
                    'admission_no'   => $admission_no,
                    'password'       => $default_student_pwd,
                    'first_name'     => $first_name,
                    'last_name'      => $last_name,
                    'father_name'    => $father_name,
                    'mother_name'    => $mother_name,
                    'dob'            => $dob ?: '2012-01-01',
                    'gender'         => $gender,
                    'class_id'       => $class_id,
                    'section'        => $section,
                    'mobile'         => $mobile,
                    'email'          => $email,
                    'address'        => $address,
                    'admission_date' => $admission_date,
                    'photo'          => $photo_name,
                    'status'         => $status
                ]);

                $student_id = $pdo->lastInsertId();

                // Step B: Insert Admission Fee Voucher with Itemized Breakdown
                $fee_stmt = $pdo->prepare("INSERT INTO fees 
                    (student_id, class_id, admission_fee, tuition_fee, development_fee, computer_fee, security_deposit, transport_fee, discount_amount, fee_type, total_amount, paid_amount, remaining_amount, paid_months, payment_date, payment_method, payment_status, remarks) 
                    VALUES 
                    (:student_id, :class_id, :admission_fee, :tuition_fee, :development_fee, :computer_fee, :security_deposit, :transport_fee, :discount_amount, :fee_type, :total_amount, :paid_amount, :remaining_amount, :paid_months, :payment_date, :payment_method, :payment_status, :remarks)");
                
                $fee_stmt->execute([
                    'student_id'       => $student_id,
                    'class_id'         => $class_id,
                    'admission_fee'   => $admission_fee,
                    'tuition_fee'     => $tuition_fee,
                    'development_fee' => $development_fee,
                    'computer_fee'    => $computer_fee,
                    'security_deposit' => $security_deposit,
                    'transport_fee'   => $transport_fee,
                    'discount_amount' => $discount_amount,
                    'fee_type'        => 'Admission & Enrolment',
                    'total_amount'     => $total_amount,
                    'paid_amount'      => $paid_amount,
                    'remaining_amount' => $remaining_amount,
                    'paid_months'      => 'Admission Fee & First Term',
                    'payment_date'     => $payment_date,
                    'payment_method'   => $payment_method,
                    'payment_status'   => $payment_status,
                    'remarks'          => $remarks
                ]);

                $fee_id = $pdo->lastInsertId();
                $pdo->commit();

                set_flash('success', 'Student Admission completed successfully! Registration & Fee Receipt generated.');
                header('Location: ' . base_url('admin/accounts/admission_receipt.php?id=' . $fee_id));
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = "Database transaction failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Full Student Admission & Fee Entry';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white font-extrabold flex items-center justify-center text-xl shadow-md shadow-amber-500/20">
                <i class="fa-solid fa-id-card-clip"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Integrated Student Admission & Fee Form</h1>
                <p class="text-xs text-slate-500 mt-0.5">Accounts Department • Register Student Profile + Calculate Fee Breakdown + Issue Slip</p>
            </div>
        </div>
        <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Accounts</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center space-x-2"><i class="fa-solid fa-circle-exclamation text-rose-500"></i><span><?= e($err) ?></span></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="admission.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <?= csrf_field() ?>

            <!-- SECTION 1: ACADEMIC & ADMISSION INFO -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-graduation-cap text-amber-600"></i>
                    <span>1. Academic & Enrollment Reference</span>
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission No *</label>
                        <input type="text" name="admission_no" required value="<?= e($_POST['admission_no'] ?? $auto_adm_no) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class Assigned *</label>
                        <select name="class_id" required onchange="updateSection(this)" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" data-section="<?= e($c['section']) ?>" <?= (($_POST['class_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                    <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Section *</label>
                        <input type="text" id="section" name="section" required value="<?= e($_POST['section'] ?? 'A') ?>" placeholder="e.g. A"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission Date *</label>
                        <input type="date" name="admission_date" value="<?= e($_POST['admission_date'] ?? date('Y-m-d')) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: STUDENT & PARENT PERSONAL DETAILS -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-user text-amber-600"></i>
                    <span>2. Student Profile & Parent Details</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">First Name *</label>
                        <input type="text" name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>" placeholder="e.g. Aarav"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Last Name *</label>
                        <input type="text" name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>" placeholder="e.g. Sharma"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Gender *</label>
                        <select name="gender" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Date of Birth</label>
                        <input type="date" name="dob" value="<?= e($_POST['dob'] ?? '') ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Father / Guardian Name *</label>
                        <input type="text" name="father_name" required value="<?= e($_POST['father_name'] ?? '') ?>" placeholder="Father's full name"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mother's Name</label>
                        <input type="text" name="mother_name" value="<?= e($_POST['mother_name'] ?? '') ?>" placeholder="Mother's full name"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mobile Phone *</label>
                        <input type="text" name="mobile" required value="<?= e($_POST['mobile'] ?? '') ?>" placeholder="10-digit mobile"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="parent@example.com"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Student Passport Photo</label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition-all cursor-pointer">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Residential Address</label>
                        <input type="text" name="address" value="<?= e($_POST['address'] ?? '') ?>" placeholder="Complete residential address..."
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: ITEMIZED ADMISSION FEE BREAKDOWN -->
            <div class="p-6 bg-gradient-to-br from-amber-50/60 to-orange-50/60 rounded-2xl border border-amber-200/80 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900 pb-2 border-b border-amber-200 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-coins text-amber-600"></i>
                        <span>3. Itemized Admission Fee Breakdown</span>
                    </span>
                    <span class="text-[11px] font-mono text-amber-700">Currency: INR (₹)</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">One-time Admission Fee (₹)</label>
                        <input type="number" step="0.01" id="admission_fee" name="admission_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['admission_fee'] ?? '5000.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tuition Fee / First Term (₹)</label>
                        <input type="number" step="0.01" id="tuition_fee" name="tuition_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['tuition_fee'] ?? '4000.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Annual / Development Fee (₹)</label>
                        <input type="number" step="0.01" id="development_fee" name="development_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['development_fee'] ?? '2000.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Computer & Activity Fee (₹)</label>
                        <input type="number" step="0.01" id="computer_fee" name="computer_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['computer_fee'] ?? '1000.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Security Deposit (Refundable) (₹)</label>
                        <input type="number" step="0.01" id="security_deposit" name="security_deposit" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['security_deposit'] ?? '1000.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Transport Fee (Optional) (₹)</label>
                        <input type="number" step="0.01" id="transport_fee" name="transport_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['transport_fee'] ?? '0.00') ?>" 
                            class="fee-calc-input w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-rose-700 mb-1">Special Discount / Concession (-) (₹)</label>
                        <input type="number" step="0.01" id="discount_amount" name="discount_amount" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['discount_amount'] ?? '0.00') ?>" 
                            class="w-full py-2.5 px-3 bg-rose-50/50 border border-rose-200 rounded-xl text-sm font-mono font-bold text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-all">
                    </div>

                    <div class="bg-white p-3.5 rounded-xl border-2 border-amber-400 flex flex-col justify-center">
                        <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Grand Total Net Fee</span>
                        <span id="grand_total_display" class="text-xl font-black text-amber-700 mt-0.5">₹13,000.00</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: INITIAL PAYMENT COLLECTION & RECEIPT GENERATION -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-receipt text-amber-600"></i>
                    <span>4. Initial Payment Collection & Transaction Details</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Net Payable Fee (₹) *</label>
                        <input type="number" step="0.01" id="total_amount" name="total_amount" readonly value="13000.00" 
                            class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-900 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Amount Paid Now (₹) *</label>
                        <input type="number" step="0.01" id="paid_amount" name="paid_amount" required onkeyup="calculateBalance()" onchange="calculateBalance()" value="13000.00" 
                            class="w-full py-2.5 px-3 bg-white border border-emerald-300 rounded-xl text-sm font-mono font-bold text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Remaining Balance (₹)</label>
                        <input type="number" step="0.01" id="remaining_amount" name="remaining_amount" readonly value="0.00" 
                            class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-rose-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Status</label>
                        <select id="payment_status" name="payment_status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Paid" selected>Paid</option>
                            <option value="Partial">Partial</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Method *</label>
                        <select name="payment_method" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Cash" selected>Cash</option>
                            <option value="UPI">UPI / Google Pay / PhonePe</option>
                            <option value="Card">Credit / Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer / NEFT</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Date *</label>
                        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Transaction Remarks / Notes</label>
                        <input type="text" name="remarks" value="Admission Fee Collection" placeholder="e.g. Transaction Ref No..."
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- Submit Button Footer -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Complete Admission & Print Slip</span>
                </button>
            </div>
        </form>

    </div>

</div>

<script>
function updateSection(select) {
    const selectedOption = select.options[select.selectedIndex];
    const section = selectedOption.getAttribute('data-section');
    if (section) {
        document.getElementById('section').value = section;
    }
}

function calculateFeeTotal() {
    const adm = parseFloat(document.getElementById('admission_fee').value) || 0;
    const tui = parseFloat(document.getElementById('tuition_fee').value) || 0;
    const dev = parseFloat(document.getElementById('development_fee').value) || 0;
    const cmp = parseFloat(document.getElementById('computer_fee').value) || 0;
    const sec = parseFloat(document.getElementById('security_deposit').value) || 0;
    const trn = parseFloat(document.getElementById('transport_fee').value) || 0;
    const dsc = parseFloat(document.getElementById('discount_amount').value) || 0;

    const gross = adm + tui + dev + cmp + sec + trn;
    const net = Math.max(0, gross - dsc);

    document.getElementById('total_amount').value = net.toFixed(2);
    document.getElementById('grand_total_display').innerText = '₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    calculateBalance();
}

function calculateBalance() {
    const net = parseFloat(document.getElementById('total_amount').value) || 0;
    const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    const rem = Math.max(0, net - paid);

    document.getElementById('remaining_amount').value = rem.toFixed(2);

    const statusSelect = document.getElementById('payment_status');
    if (rem <= 0) {
        statusSelect.value = 'Paid';
    } else if (paid > 0) {
        statusSelect.value = 'Partial';
    } else {
        statusSelect.value = 'Pending';
    }
}

// Initial calculation on page load
document.addEventListener("DOMContentLoaded", function() {
    calculateFeeTotal();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
