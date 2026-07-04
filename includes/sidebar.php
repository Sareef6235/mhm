<?php
$menus = [
    'Core' => [
        'Dashboard' => 'bi-speedometer2',
        'Members' => 'bi-people',
        'Digital ID Cards' => 'bi-person-vcard',
        'Verification' => 'bi-patch-check',
    ],
    'Operations' => [
        'QR Code' => 'bi-qr-code',
        'Barcode' => 'bi-upc-scan',
        'Attendance' => 'bi-calendar-check',
        'Payments' => 'bi-credit-card',
        'Visitors' => 'bi-person-walking',
    ],
    'Records' => [
        'Departments' => 'bi-building',
        'Classes' => 'bi-diagram-3',
        'Categories' => 'bi-tags',
        'Certificates' => 'bi-award',
        'Documents' => 'bi-folder2',
    ],
    'Insights' => [
        'Reports' => 'bi-file-earmark-bar-graph',
        'Analytics' => 'bi-graph-up',
        'Activity Logs' => 'bi-clock-history',
    ],
    'System' => [
        'Settings' => 'bi-gear',
        'Users' => 'bi-person-gear',
        'Roles' => 'bi-shield-lock',
        'Permissions' => 'bi-key',
        'Backup' => 'bi-cloud-arrow-down',
        'Website Settings' => 'bi-window',
        'Profile' => 'bi-person-circle',
    ],
];
$currentPath = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<aside class="sidebar" id="sidebar" aria-label="Primary navigation">
    <div class="brand">
        <span class="brand-mark">ID</span>
        <span><b><?= e(setting('website_name', 'SmartID Pro')) ?></b><small>Identity Cloud</small></span>
    </div>
    <div class="sidebar-search">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Search menu" data-menu-search>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menus as $group => $items): ?>
            <p class="nav-group"><?= e($group) ?></p>
            <?php foreach ($items as $label => $icon):
                $slug = strtolower(str_replace(' ', '-', $label)) . '.php';
                $active = $currentPath === $slug;
            ?>
                <a class="nav-link <?= $active ? 'active' : '' ?>" href="/admin/<?= e($slug) ?>">
                    <i class="bi <?= e($icon) ?>"></i>
                    <span><?= e($label) ?></span>
                    <?php if ($active): ?><em></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <a class="nav-link logout-link" href="/logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
    </nav>
</aside>
