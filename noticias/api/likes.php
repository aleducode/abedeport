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

// For GET requests, authentication is optional
// For POST requests, authentication is required
if ($method === 'POST') {
    // Check if user is logged in for POST requests
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Verify user exists
    try {
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no encontrado']);
            exit();
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos']);
        exit();
    }
} else if ($method === 'GET') {
    // For GET requests, get user data if logged in, otherwise user_id remains null
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Verify user exists
        try {
            $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $user_id = null;
            }
        } catch(PDOException $e) {
            // If there's an error getting user data, just continue with user_id as null
            error_log("Error getting user data: " . $e->getMessage());
            $user_id = null;
        }
    }
}

$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        // Toggle like
        if (!isset($input['post_id']) || !is_numeric($input['post_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de post requerido']);
            exit();
        }

        $post_id = intval($input['post_id']);

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

            // Check if user already liked this post
            $stmt = $conn->prepare("SELECT id_like FROM post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $existing_like = $stmt->fetch();

            $conn->beginTransaction();

            if ($existing_like) {
                // Remove like
                $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
                $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $liked = false;
            } else {
                // Add like
                $stmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
                $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $liked = true;
            }

            // Update like count in blog_posts table
            $stmt = $conn->prepare("UPDATE blog_posts SET like_count = (SELECT COUNT(*) FROM post_likes WHERE post_id = ?) WHERE id_post = ?");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $post_id, PDO::PARAM_INT);
            $stmt->execute();

            // Get updated like count
            $stmt = $conn->prepare("SELECT like_count FROM blog_posts WHERE id_post = ?");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $conn->commit();

            echo json_encode([
                'success' => true,
                'liked' => $liked,
                'like_count' => intval($result['like_count'])
            ]);

        } catch(PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al procesar like']);
        }
        break;

    case 'GET':
        // Get like status and count for a post
        if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de post requerido']);
            exit();
        }

        $post_id = intval($_GET['post_id']);

        try {
            if ($user_id !== null) {
                // Get like count and user's like status for authenticated users
                $stmt = $conn->prepare("
                    SELECT
                        bp.like_count,
                        (SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND user_id = ?) as user_liked
                    FROM blog_posts bp
                    WHERE bp.id_post = ? AND bp.estado = 'publicado'
                ");
                $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Post no encontrado']);
                    exit();
                }

                echo json_encode([
                    'like_count' => intval($result['like_count']),
                    'liked' => intval($result['user_liked']) > 0
                ]);
            } else {
                // Get only like count for non-authenticated users
                $stmt = $conn->prepare("
                    SELECT like_count
                    FROM blog_posts
                    WHERE id_post = ? AND estado = 'publicado'
                ");
                $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Post no encontrado']);
                    exit();
                }

                echo json_encode([
                    'like_count' => intval($result['like_count']),
                    'liked' => false  // Non-authenticated users haven't liked anything
                ]);
            }

        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener likes']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>