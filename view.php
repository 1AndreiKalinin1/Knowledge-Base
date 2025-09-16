<?php
require_once 'database_config/config.php';

$article = null;
$message = '';

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
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title'] ?? 'Статья'); ?> - База знаний</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .article-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .article-meta {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1.5rem;
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
        .status-badge {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <div class="container mt-4">
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="article-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h1 class="display-4 mb-0"><?php echo htmlspecialchars($article['title']); ?></h1>
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-user me-2"></i>
                                    <strong>Автор:</strong> <?php echo htmlspecialchars($article['author'] ?: 'Не указан'); ?>
                                </p>
                                <?php if($article['category']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-folder me-2"></i>
                                        <strong>Категория:</strong> <?php echo htmlspecialchars($article['category']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong>Дата создания:</strong> <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="article-meta">
                        <div class="row">
                            <div class="col-12">
                                <?php if($article['keywords']): ?>
                                    <div class="mb-2">
                                        <strong><i class="fas fa-tags me-1"></i>Ключевые слова:</strong>
                                        <div class="mt-2">
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
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="article-content">
                        <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>Действия
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>Редактировать статью
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                </a>
                                <a href="delete.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-1"></i>Удалить статью
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Информация о статье
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h6 class="text-muted">Символов</h6>
                                        <h4 class="text-primary"><?php echo number_format(strlen($article['content'])); ?></h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted">Слов</h6>
                                    <h4 class="text-primary"><?php echo number_format(str_word_count($article['content'])); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
