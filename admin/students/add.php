<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

// Redirect all Add Student requests to the Comprehensive Integrated Admission & Fee Form
header('Location: ' . base_url('admin/accounts/admission.php'));
exit();
