<?php
include 'config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_note') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $note_date = trim($_POST['note_date']);

    if (!strtotime($note_date)) {
        $error = "Formato de fecha inválido.";
    } elseif (empty($title) || empty($content)) {
        $error = "El título y el contenido no pueden estar vacíos.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO notes (title, content, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $note_date]);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al añadir la nota: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    $note_id = (int)$_POST['id']; 
    try {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$note_id]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al eliminar la nota: " . $e->getMessage();
    }
}

$today_date = date('Y-m-d');

$upcoming_notes = [];
$past_notes = [];

try {
    $stmt_upcoming = $pdo->prepare("SELECT id, title, content, created_at FROM notes WHERE DATE(created_at) >= ? ORDER BY created_at ASC");
    $stmt_upcoming->execute([$today_date]);
    $upcoming_notes = $stmt_upcoming->fetchAll(PDO::FETCH_ASSOC);

    $stmt_past = $pdo->prepare("SELECT id, title, content, created_at FROM notes WHERE DATE(created_at) < ? ORDER BY created_at DESC");
    $stmt_past->execute([$today_date]);
    $past_notes = $stmt_past->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al cargar las notas: " . $e->getMessage();
    $upcoming_notes = []; 
    $past_notes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Notas Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .note-card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .note-content {
            white-space: pre-wrap;
        }
        .note-card.bg-light {
            border: 1px dashed #ccc;
        }
        .note-card.bg-light .card-title,
        .note-card.bg-light .note-content {
            color: #6c757d !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Mis Notas Simples</h1>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                Añadir Nota
            </button>
        </div>

        <h2 class="text-center mt-5 mb-3">Notas </h2>
        <div class="row justify-content-center">
            <?php if (empty($upcoming_notes)): ?>
                <div class="col-md-8">
                    <p class="text-center text-muted">No hay notas. ¡Añade una!</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcoming_notes as $note): ?>
                    <div class="col-md-8">
                        <div class="card note-card">
                            <div class="card-body">
                                <div class="note-header">
                                    <span class="text-muted small"><?php echo date('d/m/Y', strtotime($note['created_at'])); ?></span>
                                    <pre> </pre>
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($note['title']); ?></h5>
                                    <form method="POST" class="d-inline-block ms-auto">
                                        <input type="hidden" name="action" value="delete_note">
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta nota?');">Eliminar</button>
                                    </form>
                                </div>
                                <p class="card-text note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <hr class="my-5">

        <h2 class="text-center mt-5 mb-3 text-muted">Notas Pasadas</h2>
        <div class="row justify-content-center">
            <?php if (empty($past_notes)): ?>
                <div class="col-md-8">
                    <p class="text-center text-muted">No hay notas pasadas.</p>
                </div>
            <?php else: ?>
                <?php foreach ($past_notes as $note): ?>
                    <div class="col-md-8">
                        <div class="card note-card bg-light"> <div class="card-body">
                                <div class="note-header">
                                    <span class="text-muted small"><?php echo date('d/m/Y', strtotime($note['created_at'])); ?></span>
                                    <pre> </pre>
                                    <h5 class="card-title mb-0 text-muted"><?php echo htmlspecialchars($note['title']); ?></h5>
                                    <form method="POST" class="d-inline-block ms-auto">
                                        <input type="hidden" name="action" value="delete_note">
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta nota?');">Eliminar</button>
                                    </form>
                                </div>
                                <p class="card-text note-content text-muted"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">Añadir Nueva Nota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_note">
                        <div class="mb-3">
                            <label for="noteTitle" class="form-label">Título</label>
                            <input type="text" class="form-control" id="noteTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="noteDate" class="form-label">Fecha de la Nota</label>
                            <input type="date" class="form-control" id="noteDate" name="note_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Nota</label>
                            <textarea class="form-control" id="noteContent" name="content" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Nota</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>