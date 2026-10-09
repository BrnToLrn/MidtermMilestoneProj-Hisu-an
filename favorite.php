<?php
require_once __DIR__ . '/functions.php'; require_login();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['message'=>'POST required']); exit; }
verify_csrf(); $recipeId=(int)($_POST['recipe_id']??0); $userId=(int)current_user()['id'];
$exists=$pdo->prepare('SELECT 1 FROM recipes WHERE id=:id'); $exists->execute(['id'=>$recipeId]);
if (!$exists->fetchColumn()) { http_response_code(404); echo json_encode(['message'=>'Recipe not found']); exit; }
$favorite=$pdo->prepare('SELECT 1 FROM favorites WHERE user_id=:user AND recipe_id=:recipe'); $favorite->execute(['user'=>$userId,'recipe'=>$recipeId]);
if ($favorite->fetchColumn()) { $pdo->prepare('DELETE FROM favorites WHERE user_id=:user AND recipe_id=:recipe')->execute(['user'=>$userId,'recipe'=>$recipeId]); $saved=false; }
else { $pdo->prepare('INSERT INTO favorites (user_id,recipe_id) VALUES (:user,:recipe)')->execute(['user'=>$userId,'recipe'=>$recipeId]); $saved=true; }
$count=$pdo->prepare('SELECT COUNT(*) FROM favorites WHERE recipe_id=:recipe'); $count->execute(['recipe'=>$recipeId]);
echo json_encode(['saved'=>$saved,'count'=>(int)$count->fetchColumn()]);
