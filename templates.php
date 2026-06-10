<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
$templates = get_templates($conn);
$sample = ['name'=>'MUHAMMED ZAYAN','class'=>'5','admission_no'=>'A-102','register_num'=>'125','chest_no'=>'100','section'=>'A','house_name'=>'Nile','phone'=>'9999999999','item_type'=>'Speech'];
render_header('Templates');
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3">Templates</h1><a href="template-add.php" class="btn btn-dark">Create Template</a></div>
<div class="row g-4">
<?php foreach ($templates as $tpl): ?>
<div class="col-lg-6"><div class="card h-100"><div class="card-header bg-white d-flex justify-content-between"><strong><?= e($tpl['template_name']) ?></strong><?= $tpl['is_default'] ? '<span class="badge text-bg-dark">Default</span>' : '' ?></div><div class="card-body"><div class="preview-wrap mb-3" style="transform-origin:top left;"><div style="transform:scale(.55); transform-origin:top left; width:185mm; height:70mm;"><?= render_card_html($tpl, $sample) ?></div></div><p class="mb-1"><strong>Main:</strong> <?= e($tpl['main_heading']) ?></p><p class="mb-1"><strong>Sub:</strong> <?= e($tpl['sub_heading']) ?></p><p class="mb-0"><strong>Fields:</strong> <?php $fc=parse_field_config($tpl['field_config']); foreach(CARD_FIELDS as $k=>$m){ if(!empty($fc[$k])) echo '<span class="badge text-bg-light border me-1">'.e($m['label']).'</span>'; } ?></p></div><div class="card-footer"><a class="btn btn-sm btn-outline-dark" href="template-edit.php?id=<?= (int)$tpl['id'] ?>">Edit</a> <a class="btn btn-sm btn-outline-secondary" href="generate-cards.php?template_id=<?= (int)$tpl['id'] ?>">Preview/Use</a> <a data-confirm="Delete this template?" class="btn btn-sm btn-outline-danger" href="template-delete.php?id=<?= (int)$tpl['id'] ?>&csrf_token=<?= e(csrf_token()) ?>">Delete</a></div></div></div>
<?php endforeach; ?>
</div>
<?php render_footer(); ?>
