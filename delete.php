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

// Load article data for confirmation
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM article WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $message = 'Статья успешно удалена!';
            $messageType = 'success';
            $article = null; // Clear article data
        } else {
            $message = 'Ошибка при удалении статьи';
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        $message = 'Ошибка базы данных: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить статью - База знаний</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .delete-container {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .article-preview {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc3545;
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        }
    </style>
</head>
<body>
    <div class="delete-container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center mb-4">
                        <i class="fas fa-trash-alt me-2"></i>Удаление статьи
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
                    
                    <?php if ($messageType === 'success'): ?>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i>Вернуться к списку статей
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($article && !$message): ?>
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>Внимание!
                        </h5>
                        <p class="mb-0">Вы собираетесь удалить статью. Это действие нельзя отменить!</p>
                    </div>

                    <div class="article-preview">
                        <h4 class="text-danger mb-3">
                            <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($article['title']); ?>
                        </h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <strong>Автор:</strong> <?php echo htmlspecialchars($article['author'] ?: 'Не указан'); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-folder me-1"></i>
                                    <strong>Категория:</strong> <?php echo htmlspecialchars($article['category'] ?: 'Не указана'); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <strong>Создано:</strong> <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-flag me-1"></i>
                                    <strong>Статус:</strong> 
                                    <?php 
                                    switch($article['status']) {
                                        case 'published': echo '<span class="badge bg-success">Опубликовано</span>'; break;
                                        case 'draft': echo '<span class="badge bg-warning">Черновик</span>'; break;
                                        case 'archived': echo '<span class="badge bg-secondary">Архив</span>'; break;
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                        
                        <?php if($article['keywords']): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-tags me-1"></i>
                                    <strong>Ключевые слова:</strong>
                                </small>
                                <div class="mt-1">
                                    <?php 
                                    $keywords = explode(',', $article['keywords']);
                                    foreach($keywords as $keyword): 
                                        $keyword = trim($keyword);
                                        if($keyword):
                                    ?>
                                        <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($keyword); ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="content-preview">
                            <small class="text-muted">
                                <i class="fas fa-file-text me-1"></i>
                                <strong>Содержание:</strong>
                            </small>
                            <div class="mt-2 p-3 bg-white rounded border">
                                <?php echo nl2br(htmlspecialchars(substr($article['content'], 0, 300)) . (strlen($article['content']) > 300 ? '...' : '')); ?>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="text-center">
                                    <h5 class="text-danger mb-3">Вы уверены, что хотите удалить эту статью?</h5>
                                    <p class="text-muted mb-4">Это действие нельзя отменить. Все данные статьи будут безвозвратно удалены.</p>
                                    
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>Отмена
                                        </a>
                                        <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Редактировать вместо удаления
                                        </a>
                                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                                            <i class="fas fa-trash me-1"></i>Да, удалить статью
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
