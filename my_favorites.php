<?php
require_once __DIR__ . '/functions.php'; require_login(); $user=current_user();
$statement=$pdo->prepare('SELECT r.id,r.title,r.description,c.name AS category_name,u.name AS author_name FROM favorites f JOIN recipes r ON r.id=f.recipe_id JOIN categories c ON c.id=r.category_id JOIN users u ON u.id=r.user_id WHERE f.user_id=:user ORDER BY f.created_at DESC'); $statement->execute(['user'=>$user['id']]); $recipes=$statement->fetchAll();
page_start('My favorites');
?>
<div class="hero"><div><p class="eyebrow">Your saved dishes</p><h1>Favorites</h1><p class="muted">Japanese recipes you want to come back to.</p></div></div>
<?php if(!$recipes):?><div class="form-card"><h2>No favorites yet</h2><p class="muted">Save a recipe and it will appear here.</p><a class="button" href="index.php">Browse recipes</a></div><?php else:?><div class="grid"><?php foreach($recipes as $recipe):?><article class="card"><span class="tag"><?=e($recipe['category_name'])?></span><h3><a href="recipe.php?id=<?=$recipe['id']?>"><?=e($recipe['title'])?></a></h3><p><?=e($recipe['description'])?></p><p class="meta muted">By <?=e($recipe['author_name'])?></p></article><?php endforeach;?></div><?php endif;?>
<?php page_end(); ?>
