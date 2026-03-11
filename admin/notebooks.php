<?php
require_once __DIR__ . '/../config/db.php';
require_admin();
$msg='';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verify_csrf()){$err='Invalid CSRF token.';} else {
  $action=$_POST['action']??''; $type=trim($_POST['notebook_type']??''); $pages=(int)($_POST['pages']??0); $price=(float)($_POST['price']??0);
  if($action==='add'||$action==='edit'){
   if($type===''||$pages<=0||$price<=0){$err='Valid data required.';}
   elseif($action==='add'){$pdo->prepare('INSERT INTO notebooks (notebook_type,pages,price) VALUES (:t,:g,:p)')->execute([':t'=>$type,':g'=>$pages,':p'=>$price]);$msg='Added.';}
   else {$id=(int)($_POST['id']??0);$pdo->prepare('UPDATE notebooks SET notebook_type=:t,pages=:g,price=:p WHERE id=:id')->execute([':t'=>$type,':g'=>$pages,':p'=>$price,':id'=>$id]);$msg='Updated.';}
  } elseif($action==='delete'){ $id=(int)($_POST['id']??0);$pdo->prepare('DELETE FROM notebooks WHERE id=:id')->execute([':id'=>$id]);$msg='Deleted.'; }
 }
}
$edit=null; if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM notebooks WHERE id=:id');$s->execute([':id'=>(int)$_GET['edit']]);$edit=$s->fetch();}
$rows=$pdo->query('SELECT * FROM notebooks ORDER BY pages')->fetchAll();
?>
<!doctype html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Notebooks</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'><link rel='stylesheet' href='../assets/style.css'></head><body>
<nav class="navbar navbar-expand-lg bg-madrasa navbar-dark"><div class="container"><a class="navbar-brand" href="dashboard.php">Admin Panel</a><div class="ms-auto d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="textbooks.php">Textbooks</a><a class="btn btn-outline-light btn-sm" href="orders.php">Orders</a><a class="btn btn-light btn-sm" href="logout.php">Logout</a></div></div></nav>
<div class='container py-4'><?php if($msg):?><div class='alert alert-success'><?=e($msg)?></div><?php endif;?><?php if($err):?><div class='alert alert-danger'><?=e($err)?></div><?php endif;?>
<div class='row g-3'><div class='col-lg-4'><div class='form-section'><h5><?= $edit?'Edit':'Add'?> Notebook</h5><form method='post'><input type='hidden' name='csrf_token' value='<?=e(csrf_token())?>'><input type='hidden' name='action' value='<?= $edit?'edit':'add' ?>'><?php if($edit):?><input type='hidden' name='id' value='<?= (int)$edit['id'] ?>'><?php endif;?><div class='mb-2'><label class='form-label'>Notebook Type</label><input class='form-control' name='notebook_type' value='<?=e($edit['notebook_type']??'')?>' required></div><div class='mb-2'><label class='form-label'>Pages</label><input class='form-control' name='pages' type='number' min='1' value='<?=e((string)($edit['pages']??''))?>' required></div><div class='mb-3'><label class='form-label'>Price</label><input class='form-control' name='price' type='number' step='0.01' min='1' value='<?=e((string)($edit['price']??''))?>' required></div><button class='btn btn-madrasa'>Save</button></form></div></div>
<div class='col-lg-8'><div class='form-section'><h5>Notebooks</h5><div class='table-responsive'><table class='table'><thead><tr><th>Type</th><th>Pages</th><th>Price</th><th>Action</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['notebook_type'])?></td><td><?= (int)$r['pages'] ?></td><td>₹<?=number_format((float)$r['price'],2)?></td><td><a class='btn btn-sm btn-outline-primary' href='notebooks.php?edit=<?= (int)$r['id'] ?>'>Edit</a><form method='post' class='d-inline'><input type='hidden' name='csrf_token' value='<?=e(csrf_token())?>'><input type='hidden' name='action' value='delete'><input type='hidden' name='id' value='<?= (int)$r['id'] ?>'><button class='btn btn-sm btn-outline-danger'>Delete</button></form></td></tr><?php endforeach;?></tbody></table></div></div></div></div></div>
<?php render_site_footer(); ?>
</body></html>
