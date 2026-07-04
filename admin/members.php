<?php
$title = 'Members';
require __DIR__ . '/../includes/header.php';
require_login();
require __DIR__ . '/../includes/sidebar.php';
verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo = upload_file($_FILES['photo'] ?? [], 'photos');
    $token = secure_token();
    $stmt = db()->prepare('INSERT INTO members(member_uid,verify_token,full_name,father_name,mother_name,gender,dob,blood_group,nationality,religion,address,district,state,country,phone,whatsapp,email,emergency_contact,occupation,qualification,joining_date,status,department_id,class_id,category_id,roll_number,admission_number,notes,photo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$_POST['member_uid'] ?: 'MID' . date('Y') . random_int(1000, 9999), $token, $_POST['full_name'], $_POST['father_name'], $_POST['mother_name'], $_POST['gender'], $_POST['dob'], $_POST['blood_group'], $_POST['nationality'], $_POST['religion'], $_POST['address'], $_POST['district'], $_POST['state'], $_POST['country'], $_POST['phone'], $_POST['whatsapp'], $_POST['email'], $_POST['emergency_contact'], $_POST['occupation'], $_POST['qualification'], $_POST['joining_date'], $_POST['status'], $_POST['department_id'] ?: null, $_POST['class_id'] ?: null, $_POST['category_id'] ?: null, $_POST['roll_number'], $_POST['admission_number'], $_POST['notes'], $photo]);
    audit('create', 'members', (int) db()->lastInsertId());
    header('Location: /admin/members.php?created=1');
    exit;
}
$q = '%' . ($_GET['q'] ?? '') . '%';
$stmt = db()->prepare('SELECT m.*,d.name department FROM members m LEFT JOIN departments d ON d.id=m.department_id WHERE m.full_name LIKE ? OR m.member_uid LIKE ? ORDER BY m.id DESC LIMIT 50');
$stmt->execute([$q, $q]);
$members = $stmt->fetchAll();
?>
<main class="main">
    <header class="topbar">
        <div>
            <div class="breadcrumb-line">Admin / Identity records</div>
            <h1>Members</h1>
            <p class="lead-soft">Create, search and verify premium smart ID profiles with secure QR tokens and polished upload experiences.</p>
        </div>
        <div class="top-actions">
            <button class="icon-btn d-lg-none" data-toggle-sidebar aria-label="Open menu"><i class="bi bi-list"></i></button>
            <button class="icon-btn" data-theme-toggle aria-label="Toggle theme"><i class="bi bi-moon-stars"></i></button>
            <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#memberModal"><i class="bi bi-person-plus me-2"></i>Add Member</button>
        </div>
    </header>

    <section class="table-card lift">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
            <div>
                <p class="page-kicker mb-1">Directory</p>
                <h2 class="h3 fw-black mb-0">Smart member registry</h2>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="filter-chip"><i class="bi bi-funnel"></i>Active</span>
                <span class="filter-chip"><i class="bi bi-qr-code"></i>QR enabled</span>
                <span class="filter-chip"><i class="bi bi-shield-check"></i>Verified</span>
            </div>
        </div>
        <form class="mb-4">
            <div class="input-icon">
                <i class="bi bi-search"></i>
                <input class="form-control search-input" name="q" placeholder="Search by name, member ID, email or phone" value="<?= e($_GET['q'] ?? '') ?>">
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Member</th><th>Member ID <i class="bi bi-arrow-down-up ms-1"></i></th><th>Department</th><th>Status</th><th>Secure QR</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img loading="lazy" class="member-photo" src="/<?= e($m['photo'] ?: 'assets/images/avatar.svg') ?>" alt="<?= e($m['full_name']) ?>">
                                    <div><b><?= e($m['full_name']) ?></b><div class="text-muted small"><?= e($m['email']) ?></div></div>
                                </div>
                            </td>
                            <td><span class="filter-chip"><?= e($m['member_uid']) ?></span></td>
                            <td><?= e($m['department']) ?></td>
                            <td><span class="badge-soft-success"><i class="bi bi-check2-circle me-1"></i><?= e($m['status']) ?></span></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="/id/<?= e($m['verify_token']) ?>"><i class="bi bi-qr-code me-1"></i>Verify</a></td>
                            <td class="text-end"><a class="btn btn-sm btn-premium" href="/admin/id-card.php?id=<?= (int) $m['id'] ?>"><i class="bi bi-person-vcard me-1"></i>Card</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$members): ?>
                        <tr><td colspan="6" class="text-center py-5"><i class="bi bi-stars fs-1 text-primary"></i><h3 class="h5 mt-3">No members found</h3><p class="text-muted mb-0">Create your first premium smart ID profile.</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form class="modal-content" method="post" enctype="multipart/form-data">
            <div class="modal-header border-0 pb-0">
                <div><p class="page-kicker mb-1">New identity</p><h2 class="h3 fw-black mb-0">Add member</h2></div>
                <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <label class="file-drop h-100">
                            <input type="file" class="d-none" name="photo" data-preview="#photoPreview" accept="image/*">
                            <span><i class="bi bi-cloud-arrow-up fs-1 text-primary"></i><b class="d-block mt-2">Upload photo</b><small class="text-muted">Drag, drop or click to preview</small><img id="photoPreview" class="avatar mt-3 d-none" alt="Preview"></span>
                        </label>
                    </div>
                    <div class="col-lg-8"><div class="row g-3">
                        <?php $fields = ['member_uid' => 'Unique Member ID', 'full_name' => 'Full Name', 'father_name' => 'Father Name', 'mother_name' => 'Mother Name', 'gender' => 'Gender', 'dob' => 'DOB', 'blood_group' => 'Blood Group', 'nationality' => 'Nationality', 'religion' => 'Religion', 'address' => 'Address', 'district' => 'District', 'state' => 'State', 'country' => 'Country', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'emergency_contact' => 'Emergency Contact', 'occupation' => 'Occupation', 'qualification' => 'Qualification', 'joining_date' => 'Joining Date', 'status' => 'Status', 'department_id' => 'Department ID', 'class_id' => 'Class ID', 'category_id' => 'Category ID', 'roll_number' => 'Roll Number', 'admission_number' => 'Admission Number']; foreach ($fields as $name => $label): ?>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input class="form-control" id="<?= e($name) ?>" name="<?= e($name) ?>" placeholder="<?= e($label) ?>" <?= $name === 'full_name' ? 'required' : '' ?>>
                                    <label for="<?= e($name) ?>"><?= e($label) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div></div>
                    <div class="col-12"><div class="form-floating"><textarea class="form-control" id="notes" name="notes" placeholder="Notes" style="height: 120px"></textarea><label for="notes">Notes</label></div></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-premium"><i class="bi bi-check2-circle me-2"></i>Save Member</button></div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
