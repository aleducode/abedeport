<?php
/**
 * AEDEPORT Blog Installation Script
 * 
 * This script will create the necessary database table for the blog system.
 * Run this script once to set up the blog functionality.
 */

// Include database connection
include "app/conn.php";

echo "<!DOCTYPE html>
<html lang='es-CO'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Instalación del Blog - AEDEPORT</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; }
        .install-container { max-width: 800px; margin: 2rem auto; }
    </style>
</head>
<body>
    <div class='container install-container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3 class='mb-0'><i class='bi bi-gear me-2'></i>Instalación del Blog AEDEPORT</h3>
            </div>
            <div class='card-body'>";

try {
    // Check if blog_posts table already exists
    $check_table = $conn->query("SHOW TABLES LIKE 'blog_posts'");
    
    if ($check_table->rowCount() > 0) {
        echo "<div class='alert alert-warning'>
                <h5><i class='bi bi-exclamation-triangle me-2'></i>Tabla ya existe</h5>
                <p>La tabla 'blog_posts' ya existe en la base de datos. La instalación no es necesaria.</p>
                <a href='app/' class='btn btn-primary'>Ir al Panel de Administración</a>
              </div>";
    } else {
        // Create the blog_posts table
        $sql = "
        CREATE TABLE IF NOT EXISTS `blog_posts` (
          `id_post` INT NOT NULL AUTO_INCREMENT,
          `titulo` VARCHAR(255) NOT NULL,
          `contenido` TEXT NOT NULL,
          `imagen` VARCHAR(255) NULL,
          `autor_id` INT NOT NULL,
          `estado` ENUM('borrador', 'publicado', 'archivado') DEFAULT 'borrador',
          `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `fecha_publicacion` TIMESTAMP NULL,
          `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `slug` VARCHAR(255) UNIQUE,
          `meta_descripcion` VARCHAR(160) NULL,
          `etiquetas` VARCHAR(255) NULL,
          PRIMARY KEY (`id_post`),
          INDEX `fk_blog_posts_usuario_idx` (`autor_id` ASC),
          INDEX `idx_estado` (`estado` ASC),
          INDEX `idx_fecha_publicacion` (`fecha_publicacion` ASC),
          CONSTRAINT `fk_blog_posts_usuario`
            FOREIGN KEY (`autor_id`)
            REFERENCES `usuario` (`id_usuario`)
            ON DELETE CASCADE
            ON UPDATE CASCADE)
        ENGINE = InnoDB;
        ";
        
        $conn->exec($sql);
        
        // Insert sample data
        $sample_posts = [
            [
                'titulo' => 'Bienvenidos a AEDEPORT',
                'contenido' => 'Somos una organización dedicada al desarrollo deportivo y la recreación. Nuestro objetivo es promover un estilo de vida saludable a través del deporte.',
                'autor_id' => 1,
                'estado' => 'publicado',
                'fecha_publicacion' => date('Y-m-d H:i:s'),
                'slug' => 'bienvenidos-aededeport',
                'meta_descripcion' => 'Descubre AEDEPORT, tu organización deportiva líder en recreación y desarrollo deportivo.',
                'etiquetas' => 'deporte, recreación, salud'
            ],
            [
                'titulo' => 'Nuevos Programas de Entrenamiento',
                'contenido' => 'Hemos implementado nuevos programas de entrenamiento para todas las edades y niveles. Incluyen fútbol, baloncesto, natación y atletismo.',
                'autor_id' => 1,
                'estado' => 'publicado',
                'fecha_publicacion' => date('Y-m-d H:i:s'),
                'slug' => 'nuevos-programas-entrenamiento',
                'meta_descripcion' => 'Conoce nuestros nuevos programas de entrenamiento deportivo para todas las edades.',
                'etiquetas' => 'entrenamiento, programas, deporte'
            ],
            [
                'titulo' => 'Resultados del Torneo Regional',
                'contenido' => 'Nuestros equipos obtuvieron excelentes resultados en el torneo regional. Los Leones FC se coronaron campeones en fútbol.',
                'autor_id' => 1,
                'estado' => 'publicado',
                'fecha_publicacion' => date('Y-m-d H:i:s'),
                'slug' => 'resultados-torneo-regional',
                'meta_descripcion' => 'Celebra con nosotros los logros de nuestros equipos en el torneo regional.',
                'etiquetas' => 'torneo, resultados, campeones'
            ]
        ];
        
        $insert_sql = "INSERT INTO blog_posts (titulo, contenido, autor_id, estado, fecha_publicacion, slug, meta_descripcion, etiquetas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($sample_posts as $post) {
            $stmt->execute([
                $post['titulo'],
                $post['contenido'],
                $post['autor_id'],
                $post['estado'],
                $post['fecha_publicacion'],
                $post['slug'],
                $post['meta_descripcion'],
                $post['etiquetas']
            ]);
        }
        
        // Create blog images directory if it doesn't exist
        $blog_dir = "assets/img/blog/";
        if (!file_exists($blog_dir)) {
            mkdir($blog_dir, 0755, true);
        }
        
        echo "<div class='alert alert-success'>
                <h5><i class='bi bi-check-circle me-2'></i>¡Instalación Completada!</h5>
                <p>El sistema de blog ha sido instalado exitosamente con los siguientes elementos:</p>
                <ul>
                    <li>✅ Tabla 'blog_posts' creada</li>
                    <li>✅ Índices y restricciones configurados</li>
                    <li>✅ 3 posts de ejemplo agregados</li>
                    <li>✅ Directorio de imágenes creado</li>
                </ul>
                <hr>
                <div class='d-flex gap-2'>
                    <a href='app/' class='btn btn-primary'>
                        <i class='bi bi-speedometer2 me-2'></i>Ir al Panel de Administración
                    </a>
                    <a href='blog/' class='btn btn-outline-primary'>
                        <i class='bi bi-newspaper me-2'></i>Ver Blog Público
                    </a>
                </div>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
            <h5><i class='bi bi-x-circle me-2'></i>Error en la Instalación</h5>
            <p>Se produjo un error durante la instalación:</p>
            <code>" . htmlspecialchars($e->getMessage()) . "</code>
            <hr>
            <p>Por favor, verifica:</p>
            <ul>
                <li>La conexión a la base de datos</li>
                <li>Los permisos de la base de datos</li>
                <li>Que la tabla 'usuario' exista</li>
            </ul>
          </div>";
}

echo "
            </div>
            <div class='card-footer text-muted'>
                <small>Instalador del Blog AEDEPORT - " . date('Y-m-d H:i:s') . "</small>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?> 