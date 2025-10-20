-- SQL for adding likes and comments functionality to blog posts
-- Run this script after the main init.sql

USE `ABEDEPORT`;

-- -----------------------------------------------------
-- Table `ABEDEPORT`.`post_likes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ABEDEPORT`.`post_likes`;

CREATE TABLE IF NOT EXISTS `ABEDEPORT`.`post_likes` (
  `id_like` INT NOT NULL AUTO_INCREMENT,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `fecha_like` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_like`),
  INDEX `fk_post_likes_blog_posts_idx` (`post_id` ASC),
  INDEX `fk_post_likes_usuario_idx` (`user_id` ASC),
  UNIQUE INDEX `unique_user_post_like` (`post_id` ASC, `user_id` ASC),
  CONSTRAINT `fk_post_likes_blog_posts`
    FOREIGN KEY (`post_id`)
    REFERENCES `ABEDEPORT`.`blog_posts` (`id_post`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_post_likes_usuario`
    FOREIGN KEY (`user_id`)
    REFERENCES `ABEDEPORT`.`usuario` (`id_usuario`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `ABEDEPORT`.`post_comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ABEDEPORT`.`post_comments`;

CREATE TABLE IF NOT EXISTS `ABEDEPORT`.`post_comments` (
  `id_comment` INT NOT NULL AUTO_INCREMENT,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `contenido` TEXT NOT NULL,
  `fecha_comentario` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado` ENUM('activo', 'moderado', 'eliminado') DEFAULT 'activo',
  `parent_comment_id` INT NULL,
  PRIMARY KEY (`id_comment`),
  INDEX `fk_post_comments_blog_posts_idx` (`post_id` ASC),
  INDEX `fk_post_comments_usuario_idx` (`user_id` ASC),
  INDEX `fk_post_comments_parent_idx` (`parent_comment_id` ASC),
  INDEX `idx_comment_fecha` (`fecha_comentario` ASC),
  INDEX `idx_comment_estado` (`estado` ASC),
  CONSTRAINT `fk_post_comments_blog_posts`
    FOREIGN KEY (`post_id`)
    REFERENCES `ABEDEPORT`.`blog_posts` (`id_post`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_post_comments_usuario`
    FOREIGN KEY (`user_id`)
    REFERENCES `ABEDEPORT`.`usuario` (`id_usuario`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_post_comments_parent`
    FOREIGN KEY (`parent_comment_id`)
    REFERENCES `ABEDEPORT`.`post_comments` (`id_comment`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- Add like count field to blog_posts table for performance optimization
ALTER TABLE `ABEDEPORT`.`blog_posts`
ADD COLUMN `like_count` INT DEFAULT 0,
ADD COLUMN `comment_count` INT DEFAULT 0;

-- Create index for better performance
ALTER TABLE `ABEDEPORT`.`blog_posts`
ADD INDEX `idx_like_count` (`like_count` ASC),
ADD INDEX `idx_comment_count` (`comment_count` ASC);

-- Insert some sample data for testing
INSERT INTO post_likes (post_id, user_id) VALUES
(1, 2),
(1, 3),
(2, 2),
(3, 3),
(4, 2)
ON DUPLICATE KEY UPDATE id_like = id_like;

INSERT INTO post_comments (post_id, user_id, contenido) VALUES
(1, 2, '¡Excelente artículo! Me encanta la iniciativa de AEDEPORT para promover el deporte.'),
(1, 3, 'Totalmente de acuerdo. Es genial ver organizaciones comprometidas con el desarrollo deportivo.'),
(2, 2, 'Los nuevos programas de entrenamiento se ven muy prometedores. ¿Cuándo inician las inscripciones?'),
(3, 3, '¡Felicitaciones a Los Leones FC! Bien merecido el campeonato.'),
(4, 2, 'El torneo multideporte suena increíble. Espero poder participar este año.')
ON DUPLICATE KEY UPDATE id_comment = id_comment;

-- Update the count fields based on existing data
UPDATE blog_posts SET
  like_count = (SELECT COUNT(*) FROM post_likes WHERE post_likes.post_id = blog_posts.id_post),
  comment_count = (SELECT COUNT(*) FROM post_comments WHERE post_comments.post_id = blog_posts.id_post AND post_comments.estado = 'activo');