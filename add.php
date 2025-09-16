<?php
require_once 'database_config/config.php';

$message = '';
$messageType = '';

// Initialize form variables
$title = '';
$content = '';
$keywords = '';
$status = 'draft';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
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
            $sql = "INSERT INTO article (title, content, keywords, status) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$title, $content, $keywords, $status]);
            
            if ($result) {
                $message = 'Статья успешно добавлена!';
                $messageType = 'success';
                // Clear form data
                $title = $content = $keywords = '';
                $status = 'draft';
            } else {
                $message = 'Ошибка при добавлении статьи';
                $messageType = 'danger';
            }
        } catch (PDOException $e) {
            $message = 'Ошибка базы данных: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить статью - База знаний</title>
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
    </style>
</head>
<body>
    <div class="form-container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center mb-4">
                        <i class="fas fa-plus-circle me-2"></i>Добавить новую статью
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
                                               value="<?php echo htmlspecialchars($title); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-flag me-1"></i>Статус
                                        </label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                            <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Архив</option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="keywords" class="form-label">
                                    <i class="fas fa-tags me-1"></i>Ключевые слова
                                </label>
                                <input type="text" class="form-control" id="keywords" name="keywords" 
                                       value="<?php echo htmlspecialchars($keywords); ?>"
                                       placeholder="Разделяйте ключевые слова запятыми">
                                <div class="form-text">Например: PHP, программирование, веб-разработка</div>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">
                                    <i class="fas fa-file-text me-1"></i>Содержание статьи <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($content); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Сохранить статью
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('content');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>
