<?php
$title = 'Member Profile';
require __DIR__ . '/../includes/header.php';
require_login();
require __DIR__ . '/../includes/sidebar.php';
verify_csrf();
$memberId = (int) ($_GET['id'] ?? $_POST['member_id'] ?? 0);
$stmt = db()->prepare('SELECT m.*, d.name department FROM members m LEFT JOIN departments d ON d.id=m.department_id WHERE m.id=? LIMIT 1');
$stmt->execute([$memberId]);
$member = $stmt->fetch();
if (!$member) { http_response_code(404); exit('Member not found'); }
create_member_storage($member['member_uid']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'upload';
    if ($action === 'upload') {
        $uploaded = upload_member_files($_FILES['files'] ?? [], $member, $_POST['category'] ?? 'documents', $_POST['notes'] ?? '');
        audit('upload_files', 'members', $memberId);
        header('Location: /admin/member-profile.php?id=' . $memberId . '&uploaded=' . $uploaded);
        exit;
    }
    $fileId = (int) ($_POST['file_id'] ?? 0);
    if ($action === 'rename') {
        $stmt = db()->prepare('UPDATE member_files SET original_file_name=?, notes=? WHERE id=? AND member_id=?');
        $stmt->execute([$_POST['display_name'] ?? '', $_POST['notes'] ?? '', $fileId, $memberId]);
    } elseif ($action === 'archive' || $action === 'restore') {
        $stmt = db()->prepare('UPDATE member_files SET status=? WHERE id=? AND member_id=?');
        $stmt->execute([$action === 'archive' ? 'archived' : 'active', $fileId, $memberId]);
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('UPDATE member_files SET status="deleted" WHERE id=? AND member_id=?');
        $stmt->execute([$fileId, $memberId]);
    } elseif ($action === 'move' || $action === 'copy') {
        $stmt = db()->prepare('SELECT * FROM member_files WHERE id=? AND member_id=?');
        $stmt->execute([$fileId, $memberId]);
        $file = $stmt->fetch();
        $targetCategory = normalize_member_file_category($_POST['target_category'] ?? 'other');
        if ($file) {
            create_member_storage($member['member_uid']);
            $source = __DIR__ . '/../' . $file['file_path'];
            $newName = ($action === 'copy' ? 'copy_' : '') . date('Ymd_His') . '_' . secure_token() . '.' . $file['file_extension'];
            $targetPath = member_storage_root($member['member_uid']) . '/' . $targetCategory . '/' . $newName;
            if (is_file($source) && copy($source, $targetPath)) {
                $relative = member_storage_public_path($member['member_uid'], $targetCategory, $newName);
                if ($action === 'copy') {
                    $insert = db()->prepare('INSERT INTO member_files(member_id,category,file_name,original_file_name,file_path,mime_type,file_extension,file_size,uploaded_by,status,notes) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
                    $insert->execute([$memberId, $targetCategory, $newName, $file['original_file_name'], $relative, $file['mime_type'], $file['file_extension'], $file['file_size'], $_SESSION['user']['id'] ?? null, 'active', $file['notes']]);
                } else {
                    @unlink($source);
                    $update = db()->prepare('UPDATE member_files SET category=?, file_name=?, file_path=? WHERE id=? AND member_id=?');
                    $update->execute([$targetCategory, $newName, $relative, $fileId, $memberId]);
                }
            }
        }
    }
    audit($action . '_file', 'member_files', $fileId);
    header('Location: /admin/member-profile.php?id=' . $memberId);
    exit;
}

$activeTab = normalize_member_file_category($_GET['tab'] ?? 'documents');
$search = '%' . ($_GET['q'] ?? '') . '%';
$status = $_GET['status'] ?? 'active';
$stmt = db()->prepare('SELECT f.*, u.name uploaded_by_name FROM member_files f LEFT JOIN users u ON u.id=f.uploaded_by WHERE f.member_id=? AND f.category=? AND f.status=? AND (f.original_file_name LIKE ? OR f.file_name LIKE ? OR f.notes LIKE ?) ORDER BY f.uploaded_at DESC LIMIT 100');
$stmt->execute([$memberId, $activeTab, $status, $search, $search, $search]);
$files = $stmt->fetchAll();
$counts = [];
foreach (member_storage_categories() as $cat) {
    $c = db()->prepare('SELECT COUNT(*) FROM member_files WHERE member_id=? AND category=? AND status="active"');
    $c->execute([$memberId, $cat]);
    $counts[$cat] = (int) $c->fetchColumn();
}
$tabLabels = ['profile'=>'Profile','signature'=>'Signature','documents'=>'Documents','certificates'=>'Certificates','payments'=>'Payments','attendance'=>'Attendance','gallery'=>'Gallery','other'=>'Other Files'];
?>
<main class="main">
    <header class="topbar">
        <div>
            <div class="breadcrumb-line">Admin / Members / File storage</div>
            <h1><?= e($member['full_name']) ?></h1>
            <p class="lead-soft">Dedicated enterprise storage: <b><?= e('uploads/members/' . safe_member_uid($member['member_uid']) . '/') ?></b></p>
        </div>
        <div class="top-actions">
            <button class="icon-btn d-lg-none" data-toggle-sidebar><i class="bi bi-list"></i></button>
            <a class="btn btn-outline-secondary" href="/admin/members.php"><i class="bi bi-arrow-left me-2"></i>Members</a>
            <a class="btn btn-premium" href="/admin/id-card.php?id=<?= $memberId ?>"><i class="bi bi-person-vcard me-2"></i>ID Card</a>
        </div>
    </header>

    <section class="row g-4 mb-4">
        <div class="col-xl-4"><article class="lux-card lift h-100"><div class="d-flex align-items-center gap-3"><img class="avatar" src="/<?= e($member['photo'] ?: 'assets/images/avatar.svg') ?>" alt="Member"><div><p class="page-kicker mb-1"><?= e($member['member_uid']) ?></p><h2 class="h4 fw-black mb-0"><?= e($member['full_name']) ?></h2><p class="text-muted mb-0"><?= e($member['department'] ?: 'No department') ?></p></div></div></article></div>
        <div class="col-xl-8"><article class="lux-card lift"><p class="page-kicker mb-2">Storage folders</p><div class="storage-path-grid"><?php foreach ($tabLabels as $key => $label): ?><span class="filter-chip"><i class="bi bi-folder2"></i><?= e($label) ?> <b><?= $counts[$key] ?></b></span><?php endforeach; ?></div></article></div>
    </section>

    <section class="table-card lift">
        <ul class="nav nav-pills premium-tabs mb-4" role="tablist">
            <?php foreach ($tabLabels as $key => $label): ?><li class="nav-item"><a class="nav-link <?= $activeTab === $key ? 'active' : '' ?>" href="?id=<?= $memberId ?>&tab=<?= e($key) ?>"><?= e($label) ?><span><?= $counts[$key] ?></span></a></li><?php endforeach; ?>
        </ul>
        <form class="upload-panel mb-4" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="member_id" value="<?= $memberId ?>"><input type="hidden" name="action" value="upload"><input type="hidden" name="category" value="<?= e($activeTab) ?>">
            <label class="file-drop"><input class="d-none" type="file" name="files[]" multiple data-progress-upload><span><i class="bi bi-cloud-arrow-up fs-1 text-primary"></i><b class="d-block mt-2">Drag & drop multiple files to <?= e($tabLabels[$activeTab]) ?></b><small class="text-muted">PDF, images, Office, ZIP, audio and video supported</small></span></label>
            <div class="progress upload-progress mt-3" role="progressbar"><div class="progress-bar" style="width:0%"></div></div>
            <div class="form-floating mt-3"><input class="form-control" name="notes" id="uploadNotes" placeholder="Upload notes"><label for="uploadNotes">Notes for uploaded files</label></div>
            <button class="btn btn-premium mt-3"><i class="bi bi-upload me-2"></i>Upload Files</button>
        </form>
        <form class="row g-3 align-items-center mb-4">
            <input type="hidden" name="id" value="<?= $memberId ?>"><input type="hidden" name="tab" value="<?= e($activeTab) ?>">
            <div class="col-lg-8"><div class="input-icon"><i class="bi bi-search"></i><input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search file name, notes or stored name"></div></div>
            <div class="col-lg-2"><select class="form-select" name="status"><option value="active" <?= $status==='active'?'selected':'' ?>>Active</option><option value="archived" <?= $status==='archived'?'selected':'' ?>>Archived</option><option value="deleted" <?= $status==='deleted'?'selected':'' ?>>Deleted</option></select></div>
            <div class="col-lg-2"><button class="btn btn-outline-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle file-table">
                <thead><tr><th>File</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Status</th><th>Notes</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><div class="d-flex align-items-center gap-3"><span class="file-icon"><i class="bi <?= e(member_file_icon($file['file_extension'])) ?>"></i></span><div><b><?= e($file['original_file_name']) ?></b><div class="text-muted small"><?= e($file['file_name']) ?></div></div></div></td>
                        <td><span class="filter-chip"><?= e(strtoupper($file['file_extension'])) ?></span><div class="small text-muted"><?= e($file['mime_type']) ?></div></td>
                        <td><?= e(format_bytes((int)$file['file_size'])) ?></td>
                        <td><?= e($file['uploaded_at']) ?><div class="small text-muted">By <?= e($file['uploaded_by_name'] ?: 'System') ?></div></td>
                        <td><span class="badge-soft-success"><?= e($file['status']) ?></span><div class="small text-muted">Modified <?= e($file['updated_at']) ?></div></td>
                        <td><?= e($file['notes']) ?></td>
                        <td class="text-end file-actions">
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="/<?= e($file['file_path']) ?>"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-sm btn-outline-secondary" download href="/<?= e($file['file_path']) ?>"><i class="bi bi-download"></i></a>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#fileModal<?= (int)$file['id'] ?>" type="button"><i class="bi bi-three-dots"></i></button>
                        </td>
                    </tr>
                    <div class="modal fade" id="fileModal<?= (int)$file['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header border-0"><h3 class="h5 fw-black">Manage file</h3><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body"><form method="post" class="d-grid gap-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="member_id" value="<?= $memberId ?>"><input type="hidden" name="file_id" value="<?= (int)$file['id'] ?>"><div class="form-floating"><input class="form-control" name="display_name" value="<?= e($file['original_file_name']) ?>" placeholder="Display name"><label>Rename display name</label></div><div class="form-floating"><input class="form-control" name="notes" value="<?= e($file['notes']) ?>" placeholder="Notes"><label>Notes</label></div><button class="btn btn-premium" name="action" value="rename">Save Rename</button><select class="form-select" name="target_category"><?php foreach ($tabLabels as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach; ?></select><div class="d-flex gap-2 flex-wrap"><button class="btn btn-outline-primary" name="action" value="move">Move</button><button class="btn btn-outline-primary" name="action" value="copy">Copy</button><button class="btn btn-outline-warning" name="action" value="archive">Archive</button><button class="btn btn-outline-success" name="action" value="restore">Restore</button><button class="btn btn-outline-danger" name="action" value="delete" onclick="return confirmDelete('Delete this file?')">Delete</button></div></form></div></div></div></div>
                <?php endforeach; ?>
                <?php if (!$files): ?><tr><td colspan="7" class="text-center py-5"><i class="bi bi-folder2-open fs-1 text-primary"></i><h3 class="h5 mt-3">No files in <?= e($tabLabels[$activeTab]) ?></h3><p class="text-muted mb-0">Upload unlimited member files into this dedicated folder.</p></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
