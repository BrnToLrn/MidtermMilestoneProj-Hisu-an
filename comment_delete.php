<?php
require_once __DIR__ . '/functions.php'; require_login(); verify_csrf();
$id=(int)($_POST['id']??0); $recipeId=(int)($_POST['recipe_id']??0); $statement=$pdo->prepare('DELETE FROM comments WHERE id=:id AND user_id=:user'); $statement->execute(['id'=>$id,'user'=>current_user()['id']]);
flash('success',$statement->rowCount()?'Comment deleted.':'Comment was not found or is not yours.'); redirect('recipe.php?id='.$recipeId);
