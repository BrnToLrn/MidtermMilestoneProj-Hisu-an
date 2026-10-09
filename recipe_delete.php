<?php
require_once __DIR__ . '/functions.php'; require_login(); verify_csrf();
$id=(int)($_POST['id']??0); $statement=$pdo->prepare('DELETE FROM recipes WHERE id=:id AND user_id=:user'); $statement->execute(['id'=>$id,'user'=>current_user()['id']]);
flash('success',$statement->rowCount()?'Recipe deleted.':'Recipe was not found or is not yours.'); redirect('index.php');
