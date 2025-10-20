<?php
require_once '../../admin/conn.php';

session_start();

header('Content-Type: application/json');

// Check if database connection exists
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = null;
$user = null;

// For GET requests, authentication is optional
// For POST/DELETE requests, authentication is required
if ($method === 'GET') {
    // For GET requests, get user data if logged in, otherwise user_id remains null
    if (isset($_SESSION['usuario'])) {
        try {
            $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido FROM usuario WHERE correo = ?");
            $stmt->bindParam(1, $_SESSION['usuario']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $user_id = $user['id_usuario'];
            }
        } catch(PDOException $e) {
            // If there's an error getting user data, just continue with user_id as null
            error_log("Error getting user data: " . $e->getMessage());
        }
    }
} else {
    // For POST/DELETE requests, authentication is required
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit();
    }

    // Get user data for POST/DELETE requests
    try {
        $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido FROM usuario WHERE correo = ?");
        $stmt->bindParam(1, $_SESSION['usuario']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no encontrado']);
            exit();
        }

        $user_id = $user['id_usuario'];
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos']);
        exit();
    }
}

$input = json_decode(file_get_contents('php://input'), true);

// Helper function to format Spanish date
function formatSpanishDate($date) {
    $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    $time = date('H:i', $timestamp);

    return "$day de $month de $year a las $time";
}

switch ($method) {
    case 'POST':
        // Add new comment
        if (!isset($input['post_id']) || !is_numeric($input['post_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de post requerido']);
            exit();
        }

        if (!isset($input['contenido']) || empty(trim($input['contenido']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Contenido del comentario requerido']);
            exit();
        }

        $post_id = intval($input['post_id']);
        $contenido = trim($input['contenido']);
        $parent_comment_id = isset($input['parent_comment_id']) && is_numeric($input['parent_comment_id'])
            ? intval($input['parent_comment_id']) : null;

        // Validate content length
        if (strlen($contenido) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'El comentario es demasiado largo (máximo 1000 caracteres)']);
            exit();
        }

        try {
            // Check if post exists and is published
            $stmt = $conn->prepare("SELECT id_post FROM blog_posts WHERE id_post = ? AND estado = 'publicado'");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Post no encontrado']);
                exit();
            }

            // If replying to a comment, check if parent comment exists
            if ($parent_comment_id) {
                $stmt = $conn->prepare("SELECT id_comment FROM post_comments WHERE id_comment = ? AND post_id = ? AND estado = 'activo'");
                $stmt->bindParam(1, $parent_comment_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $post_id, PDO::PARAM_INT);
                $stmt->execute();

                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Comentario padre no encontrado']);
                    exit();
                }
            }

            $conn->beginTransaction();

            // Insert comment
            $stmt = $conn->prepare("
                INSERT INTO post_comments (post_id, user_id, contenido, parent_comment_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $contenido);
            $stmt->bindParam(4, $parent_comment_id, PDO::PARAM_INT);
            $stmt->execute();

            $comment_id = $conn->lastInsertId();

            // Update comment count in blog_posts table
            $stmt = $conn->prepare("
                UPDATE blog_posts
                SET comment_count = (
                    SELECT COUNT(*) FROM post_comments
                    WHERE post_id = ? AND estado = 'activo'
                )
                WHERE id_post = ?
            ");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $post_id, PDO::PARAM_INT);
            $stmt->execute();

            // Get the created comment with user info
            $stmt = $conn->prepare("
                SELECT c.*, u.nombre, u.apellido
                FROM post_comments c
                JOIN usuario u ON c.user_id = u.id_usuario
                WHERE c.id_comment = ?
            ");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            $conn->commit();

            echo json_encode([
                'success' => true,
                'comment' => [
                    'id_comment' => intval($comment['id_comment']),
                    'contenido' => $comment['contenido'],
                    'nombre' => $comment['nombre'],
                    'apellido' => $comment['apellido'],
                    'fecha_comentario' => formatSpanishDate($comment['fecha_comentario']),
                    'parent_comment_id' => $comment['parent_comment_id']
                ]
            ]);

        } catch(PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar comentario']);
        }
        break;

    case 'GET':
        // Get comments for a post
        if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de post requerido']);
            exit();
        }

        $post_id = intval($_GET['post_id']);

        try {
            // Get all comments for the post (ordered by date)
            $stmt = $conn->prepare("
                SELECT c.*, u.nombre, u.apellido
                FROM post_comments c
                JOIN usuario u ON c.user_id = u.id_usuario
                WHERE c.post_id = ? AND c.estado = 'activo'
                ORDER BY c.fecha_comentario ASC
            ");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Organize comments into threaded structure
            $organized_comments = [];
            $comment_map = [];

            // First pass: create map and add top-level comments
            foreach ($comments as $comment) {
                $comment['fecha_comentario_formatted'] = formatSpanishDate($comment['fecha_comentario']);
                $comment['replies'] = [];
                $comment_map[$comment['id_comment']] = $comment;

                if (!$comment['parent_comment_id']) {
                    $organized_comments[] = &$comment_map[$comment['id_comment']];
                }
            }

            // Second pass: add replies to their parents
            foreach ($comments as $comment) {
                if ($comment['parent_comment_id'] && isset($comment_map[$comment['parent_comment_id']])) {
                    $comment_map[$comment['parent_comment_id']]['replies'][] = $comment_map[$comment['id_comment']];
                }
            }

            echo json_encode([
                'success' => true,
                'comments' => $organized_comments,
                'total_count' => count($comments)
            ]);

        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener comentarios']);
        }
        break;

    case 'DELETE':
        // Delete comment (only by comment author or admin)
        if (!isset($_GET['comment_id']) || !is_numeric($_GET['comment_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de comentario requerido']);
            exit();
        }

        $comment_id = intval($_GET['comment_id']);

        try {
            // Check if user owns the comment or is admin
            $stmt = $conn->prepare("
                SELECT c.*, u.is_admin
                FROM post_comments c
                JOIN usuario u ON u.id_usuario = ?
                WHERE c.id_comment = ?
            ");
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                http_response_code(404);
                echo json_encode(['error' => 'Comentario no encontrado']);
                exit();
            }

            $stmt = $conn->prepare("SELECT user_id, post_id FROM post_comments WHERE id_comment = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment) {
                http_response_code(404);
                echo json_encode(['error' => 'Comentario no encontrado']);
                exit();
            }

            // Check permissions
            if ($comment['user_id'] != $user_id && $result['is_admin'] != 1) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado para eliminar este comentario']);
                exit();
            }

            $conn->beginTransaction();

            // Mark comment as deleted instead of actually deleting
            $stmt = $conn->prepare("UPDATE post_comments SET estado = 'eliminado' WHERE id_comment = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();

            // Update comment count
            $stmt = $conn->prepare("
                UPDATE blog_posts
                SET comment_count = (
                    SELECT COUNT(*) FROM post_comments
                    WHERE post_id = ? AND estado = 'activo'
                )
                WHERE id_post = ?
            ");
            $stmt->bindParam(1, $comment['post_id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $comment['post_id'], PDO::PARAM_INT);
            $stmt->execute();

            $conn->commit();

            echo json_encode(['success' => true, 'message' => 'Comentario eliminado']);

        } catch(PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar comentario']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>