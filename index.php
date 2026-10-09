<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = current_user();
$search = trim($_GET['q'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$sql = 'SELECT r.id, r.title, r.description, r.created_at, r.updated_at, c.name AS category_name, u.name AS author_name
    FROM recipes r JOIN categories c ON c.id = r.category_id JOIN users u ON u.id = r.user_id WHERE 1=1';
$params = [];
if ($search !== '') { $sql .= ' AND (r.title LIKE :search OR r.description LIKE :search)'; $params['search'] = '%' . $search . '%'; }
if ($category > 0) { $sql .= ' AND r.category_id = :category'; $params['category'] = $category; }
$sql .= ' ORDER BY r.created_at DESC';
$statement = $pdo->prepare($sql); $statement->execute($params); $recipes = $statement->fetchAll();
page_start('Japanese recipes');
?>
<div class="listing-page">
<section class="hero">
    <div><p class="eyebrow">Japanese home cooking</p><h1>Small dishes, big warmth.</h1><p class="muted">Discover comforting Japanese recipes, from quick bento lunches to cozy bowls of ramen.</p></div>
    <a class="button" href="recipe_form.php">Share a recipe</a>
</section>
<form class="filters" method="get">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by dish or ingredient...">
    <select name="category"><option value="0">All categories</option><?php foreach (categories() as $item): ?><option value="<?= $item['id'] ?>" <?= $category === (int)$item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option><?php endforeach; ?></select>
    <button class="button" type="submit">Find</button>
</form>
<div class="section-heading"><div><p class="eyebrow">From the community</p><h2>Latest dishes</h2></div><span class="muted"><?= count($recipes) ?> <?= count($recipes) === 1 ? 'recipe' : 'recipes' ?></span></div>
<?php if (!$recipes): ?><div class="form-card"><h2>No recipes found</h2><p class="muted">Try another search, or be the first to share one.</p></div><?php else: ?>
<div class="grid"><?php foreach ($recipes as $recipe): ?><article class="card">
    <span class="tag"><?= e($recipe['category_name']) ?></span><h3><a href="recipe.php?id=<?= $recipe['id'] ?>"><?= e($recipe['title']) ?></a></h3>
    <p><?= e($recipe['description']) ?></p><p class="meta muted">By <?= e($recipe['author_name']) ?> · <?= date('M j, Y', strtotime($recipe['created_at'])) ?><?= $recipe['updated_at'] ? ' · edited' : '' ?></p>
</article><?php endforeach; ?></div><?php endif; ?>
</div>
<?php page_end(); ?>
