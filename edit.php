<?php
require_once 'database_config/config.php';

$message = '';
$messageType = '';
$article = null;

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Load article data
try {
    $stmt = $pdo->prepare("SELECT * FROM article WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $message = 'Ошибка при загрузке статьи: ' . $e->getMessage();
    $messageType = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    // Validation
    if (empty($title)) {
        $message = 'Заголовок обязателен для заполнения';
        $messageType = 'danger';
    } elseif (empty($content)) {
        $message = 'Содержание статьи обязательно для заполнения';
        $messageType = 'danger';
    } else {
        try {
            $sql = "UPDATE article SET title = ?, content = ?, author = ?, category = ?, keywords = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$title, $content, $author, $category, $keywords, $status, $id]);
            
            if ($result) {
                $message = 'Статья успешно обновлена!';
                $messageType = 'success';
                // Reload article data
                $stmt = $pdo->prepare("SELECT * FROM article WHERE id = ?");
                $stmt->execute([$id]);
                $article = $stmt->fetch();
            } else {
                $message = 'Ошибка при обновлении статьи';
                $messageType = 'danger';
            }
        } catch (PDOException $e) {
            $message = 'Ошибка базы данных: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get existing categories for suggestions
try {
    $categoryStmt = $pdo->query("SELECT DISTINCT category FROM article WHERE category IS NOT NULL ORDER BY category");
    $existingCategories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $existingCategories = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать статью - База знаний</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .article-info {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center mb-4">
                        <i class="fas fa-edit me-2"></i>Редактировать статью
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($article): ?>
                    <div class="article-info">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Создано: <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-edit me-1"></i>
                                    Создано: <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">
                                                <i class="fas fa-heading me-1"></i>Заголовок статьи <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($article['title']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">
                                                <i class="fas fa-flag me-1"></i>Статус
                                            </label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                                <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                                <option value="archived" <?php echo $article['status'] === 'archived' ? 'selected' : ''; ?>>Архив</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="author" class="form-label">
                                                <i class="fas fa-user me-1"></i>Автор
                                            </label>
                                            <input type="text" class="form-control" id="author" name="author" 
                                                   value="<?php echo htmlspecialchars($article['author']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">
                                                <i class="fas fa-folder me-1"></i>Категория
                                            </label>
                                            <input type="text" class="form-control" id="category" name="category" 
                                                   value="<?php echo htmlspecialchars($article['category']); ?>"
                                                   list="categoryList">
                                            <datalist id="categoryList">
                                                <?php foreach($existingCategories as $cat): ?>
                                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="keywords" class="form-label">
                                        <i class="fas fa-tags me-1"></i>Ключевые слова
                                    </label>
                                    <input type="text" class="form-control" id="keywords" name="keywords" 
                                           value="<?php echo htmlspecialchars($article['keywords']); ?>"
                                           placeholder="Разделяйте ключевые слова запятыми">
                                    <div class="form-text">Например: PHP, программирование, веб-разработка</div>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">
                                        <i class="fas fa-file-text me-1"></i>Содержание статьи <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="index.php" class="btn btn-secondary me-2">
                                            <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                        </a>
                                        <a href="view.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-info">
                                            <i class="fas fa-eye me-1"></i>Просмотр
                                        </a>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Сохранить изменения
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('content');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        }
    </script>
</body>
</html>
