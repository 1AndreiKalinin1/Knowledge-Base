<?php
require_once 'database_config/config.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT * FROM article WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE :search OR content LIKE :search OR keywords LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

if (!empty($status)) {
    $query .= " AND status = :status";
    $params[':status'] = $status;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
} catch(PDOException $e) {
    $articles = [];
    $error = "Ошибка при загрузке статей: " . $e->getMessage();
}

// Get unique categories for filter
try {
    $categoryStmt = $pdo->query("SELECT DISTINCT category FROM article WHERE category IS NOT NULL ORDER BY category");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>База знаний для статей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .article-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .article-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.8em;
        }
        .search-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .tag {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center mb-4">
                        <i class="fas fa-book-open me-2"></i>База знаний - Статьи
                    </h1>
                    
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Поиск по заголовку, содержанию или тегам..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">Все категории</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Все статусы</option>
                                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Архив</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-light w-100">
                                <i class="fas fa-search me-1"></i>Поиск
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if(isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-12">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Добавить статью
                </a>
            </div>
        </div>

        <?php if(empty($articles)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Статьи не найдены</h4>
                <p class="text-muted">Попробуйте изменить параметры поиска или добавьте новую статью</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($articles as $article): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card article-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                    <span class="badge status-badge 
                                        <?php 
                                        switch($article['status']) {
                                            case 'published': echo 'bg-success'; break;
                                            case 'draft': echo 'bg-warning'; break;
                                            case 'archived': echo 'bg-secondary'; break;
                                        }
                                        ?>">
                                        <?php 
                                        switch($article['status']) {
                                            case 'published': echo 'Опубликовано'; break;
                                            case 'draft': echo 'Черновик'; break;
                                            case 'archived': echo 'Архив'; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <?php if($article['category']): ?>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($article['category']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($article['content'], 0, 150)) . (strlen($article['content']) > 150 ? '...' : ''); ?>
                                </p>
                                
                                <?php if($article['keywords']): ?>
                                    <div class="mb-2">
                                        <strong><i class="fas fa-tags me-1"></i>Ключевые слова:</strong>
                                        <?php 
                                        $keywords = explode(',', $article['keywords']);
                                        foreach($keywords as $keyword): 
                                            $keyword = trim($keyword);
                                            if($keyword):
                                        ?>
                                            <span class="tag"><?php echo htmlspecialchars($keyword); ?></span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($article['author'] ?: 'Не указан'); ?>
                                    <br>
                                    <i class="fas fa-calendar me-1"></i><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <a href="view.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Просмотр
                                    </a>
                                    <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Редактировать
                                    </a>
                                    <a href="delete.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-danger btn-sm" 
                                       onclick="return confirm('Вы уверены, что хотите удалить эту статью?')">
                                        <i class="fas fa-trash me-1"></i>Удалить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
