<?php
require_once __DIR__ . '/functions.php'; require_login(); $user=current_user(); $id=(int)($_GET['id']??$_POST['id']??0);
$get=$pdo->prepare('SELECT * FROM comments WHERE id=:id AND user_id=:user'); $get->execute(['id'=>$id,'user'=>$user['id']]); $comment=$get->fetch();
if (!$comment) { http_response_code(403); exit('You can only edit your own comments.'); }
$errors=[]; if ($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf(); $body=trim($_POST['body']??''); if(strlen($body)<2||strlen($body)>1000)$errors[]='Comment must be 2 to 1,000 characters.'; else {$update=$pdo->prepare('UPDATE comments SET body=:body WHERE id=:id AND user_id=:user');$update->execute(['body'=>$body,'id'=>$id,'user'=>$user['id']]);flash('success','Comment updated.');redirect('recipe.php?id='.$comment['recipe_id']);}}
page_start('Edit comment');
?>
<div class="form-card"><p class="eyebrow">Your feedback</p><h1>Edit comment</h1><?php if($errors):?><div class="notice error"><?=e($errors[0])?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><label for="body">Comment</label><textarea id="body" name="body" maxlength="1000" required><?=e($_POST['body']??$comment['body'])?></textarea><div class="form-actions"><button class="button" type="submit">Save comment</button><a class="button button-secondary" href="recipe.php?id=<?=$comment['recipe_id']?>">Cancel</a></div></form></div>
<?php page_end(); ?>
