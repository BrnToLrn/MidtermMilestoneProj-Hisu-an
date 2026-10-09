<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid form token. Please go back and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    global $pdo;
    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }

    $statement = $pdo->prepare('SELECT id, name, email FROM users WHERE id = :id');
    $statement->execute(['id' => (int) $user['id']]);
    $databaseUser = $statement->fetch();
    if (!$databaseUser) {
        unset($_SESSION['user']);
        flash('error', 'Your account is no longer available. Please create a new account.');
        redirect('login.php');
    }

    $_SESSION['user'] = $databaseUser;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function page_start(string $title): void
{
    $user = current_user();
    $flash = get_flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Sakura Kitchen</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <header class="site-header">
        <a class="brand" href="index.php"><span>✦</span> Sakura Kitchen</a>
        <?php if ($user): ?>
            <nav>
                <a href="index.php">Recipes</a>
                <a href="my_favorites.php">Favorites</a>
                <a class="button button-small" href="recipe_form.php">+ Share recipe</a>
                <a href="logout.php">Log out</a>
            </nav>
        <?php endif; ?>
    </header>
    <main class="container">
    <?php if ($flash): ?>
        <div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}

function page_end(): void
{
    ?>
    </main>
    <footer>Japanese-inspired recipes, shared with heart.</footer>
    <script src="assets/app.js"></script>
    </body>
    </html>
    <?php
}

function categories(): array
{
    global $pdo;
    return $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function recipe_repository(): RecipeRepository
{
    global $pdo;
    return new RecipeRepository($pdo);
}

final class RecipeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id, ?int $viewerId = null): ?array
    {
        $sql = 'SELECT r.*, c.name AS category_name, u.name AS author_name,
                (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id) AS favorite_count';
        if ($viewerId !== null) {
            $sql .= ', EXISTS(SELECT 1 FROM favorites vf WHERE vf.recipe_id = r.id AND vf.user_id = :viewer_id) AS is_favorite';
        }
        $sql .= ' FROM recipes r JOIN categories c ON c.id = r.category_id
                  JOIN users u ON u.id = r.user_id WHERE r.id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        if ($viewerId !== null) {
            $statement->bindValue(':viewer_id', $viewerId, PDO::PARAM_INT);
        }
        $statement->execute();
        return $statement->fetch() ?: null;
    }

    public function ingredients(int $recipeId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM ingredients WHERE recipe_id = :id ORDER BY id');
        $statement->execute(['id' => $recipeId]);
        return $statement->fetchAll();
    }

    public function comments(int $recipeId): array
    {
        $statement = $this->pdo->prepare('SELECT c.*, u.name AS author_name FROM comments c
            JOIN users u ON u.id = c.user_id WHERE c.recipe_id = :id ORDER BY c.created_at DESC');
        $statement->execute(['id' => $recipeId]);
        return $statement->fetchAll();
    }

    public function save(array $data, int $userId, ?int $id = null): int
    {
        $this->pdo->beginTransaction();
        try {
            if ($id === null) {
                $statement = $this->pdo->prepare('INSERT INTO recipes (user_id, category_id, title, description, servings, steps)
                    VALUES (:user_id, :category_id, :title, :description, :servings, :steps)');
            } else {
                $statement = $this->pdo->prepare('UPDATE recipes SET category_id = :category_id, title = :title,
                    description = :description, servings = :servings, steps = :steps WHERE id = :id AND user_id = :user_id');
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $statement->execute([
                'user_id' => $userId, 'category_id' => $data['category_id'],
                'title' => $data['title'], 'description' => $data['description'],
                'servings' => $data['servings'], 'steps' => $data['steps'],
            ]);
            if ($id === null) {
                $id = (int) $this->pdo->lastInsertId();
            }
            $this->pdo->prepare('DELETE FROM ingredients WHERE recipe_id = :id')->execute(['id' => $id]);
            $ingredient = $this->pdo->prepare('INSERT INTO ingredients (recipe_id, name, quantity)
                VALUES (:recipe_id, :name, :quantity)');
            foreach ($data['ingredients'] as $item) {
                $ingredient->execute(['recipe_id' => $id, 'name' => $item['name'], 'quantity' => $item['quantity']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
