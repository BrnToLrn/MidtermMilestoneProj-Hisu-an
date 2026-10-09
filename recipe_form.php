<?php
require_once __DIR__ . '/functions.php'; require_login();
$user = current_user(); $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); $editing = $id > 0; $recipe = null; $items = [['name'=>'','quantity'=>'']]; $errors = [];
if ($editing) { $recipe = recipe_repository()->find($id, (int)$user['id']); if (!$recipe || (int)$recipe['user_id'] !== (int)$user['id']) { http_response_code(403); exit('You can only edit your own recipes.'); } $items = recipe_repository()->ingredients($id); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $title=trim($_POST['title']??''); $description=trim($_POST['description']??''); $steps=trim($_POST['steps']??''); $category=(int)($_POST['category_id']??0); $servings=(int)($_POST['servings']??0);
    $names=$_POST['ingredient']??[]; $quantities=$_POST['quantity']??[]; $ingredients=[];
    foreach ($names as $i=>$name) {
        $ingredientName = trim((string) $name);
        $quantity = trim((string) ($quantities[$i] ?? ''));
        if ($ingredientName !== '' && $quantity !== '') {
            $ingredients[] = ['name' => $ingredientName, 'quantity' => $quantity];
        } elseif ($ingredientName !== '' || $quantity !== '') {
            $errors[] = 'Complete both the quantity and ingredient name for every ingredient row.';
        }
    }
    if (strlen($title)<3 || strlen($title)>140) $errors[]='Title must be 3 to 140 characters.';
    if (strlen($description)<10 || strlen($description)>500) $errors[]='Description must be 10 to 500 characters.';
    if (!$category || !in_array($category,array_map('intval',array_column(categories(),'id')),true)) $errors[]='Choose a valid category.';
    if ($servings < 1 || $servings > 100) $errors[]='Servings must be a number from 1 to 100.';
    if (!$ingredients) $errors[]='Add at least one ingredient.'; if (strlen($steps)<20) $errors[]='Cooking steps must be at least 20 characters.';
    if (!$errors) { $savedId=recipe_repository()->save(['category_id'=>$category,'title'=>$title,'description'=>$description,'servings'=>$servings,'steps'=>$steps,'ingredients'=>$ingredients],(int)$user['id'],$editing?$id:null); flash('success',$editing?'Recipe updated.':'Recipe shared!'); redirect('recipe.php?id='.$savedId); }
}
page_start($editing?'Edit recipe':'Share a recipe');
?>
<div class="form-card"><p class="eyebrow"><?= $editing?'Make it even better':'From your kitchen' ?></p><h1><?= $editing?'Edit recipe':'Share a Japanese recipe' ?></h1><?php if ($errors): ?><div class="notice error"><?php foreach($errors as $error): ?><div><?=e($error)?></div><?php endforeach; ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><?php if($editing):?><input type="hidden" name="id" value="<?=$id?>"><?php endif; ?><label for="title">Recipe title</label><input id="title" name="title" required maxlength="140" value="<?=e($_POST['title']??$recipe['title']??'')?>"><label for="description">Short description</label><textarea id="description" name="description" required maxlength="500"><?=e($_POST['description']??$recipe['description']??'')?></textarea><label for="category">Category</label><select id="category" name="category_id" required><option value="">Choose one</option><?php foreach(categories() as $cat):?><option value="<?=$cat['id']?>" <?=((int)($_POST['category_id']??$recipe['category_id']??0)===(int)$cat['id'])?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;?></select><label for="servings">Servings</label><input id="servings" name="servings" type="number" min="1" max="100" required value="<?=e($_POST['servings']??$recipe['servings']??'1')?>"><label>Ingredients</label><div data-ingredients><?php foreach($items as $index=>$item):?><div class="ingredient-row"><input name="quantity[]" placeholder="Quantity" required value="<?=e($_POST['quantity'][$index]??$item['quantity'])?>"><input name="ingredient[]" placeholder="Ingredient" required value="<?=e($_POST['ingredient'][$index]??$item['name'])?>"><button type="button" class="button button-secondary remove-ingredient">Remove</button></div><?php endforeach;?></div><button type="button" class="button button-secondary" data-add-ingredient>+ Add ingredient</button><label for="steps">Cooking steps</label><textarea id="steps" name="steps" required placeholder="Tell your neighbors how to make it..."><?=e($_POST['steps']??$recipe['steps']??'')?></textarea><div class="form-actions"><button class="button" type="submit"><?= $editing?'Save changes':'Share recipe' ?></button><a class="button button-secondary" href="index.php">Cancel</a></div></form></div>
<?php page_end(); ?>
