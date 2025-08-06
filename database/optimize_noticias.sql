-- Database optimization for /noticias performance
-- Run these queries to improve performance

-- Add indexes for blog_posts table (most frequently queried)
CREATE INDEX IF NOT EXISTS idx_blog_posts_estado_fecha ON blog_posts(estado, fecha_publicacion DESC);
CREATE INDEX IF NOT EXISTS idx_blog_posts_slug ON blog_posts(slug);
CREATE INDEX IF NOT EXISTS idx_blog_posts_autor_id ON blog_posts(autor_id);

-- Add indexes for tournaments table
CREATE INDEX IF NOT EXISTS idx_tournaments_estado ON tournaments(estado);
CREATE INDEX IF NOT EXISTS idx_tournaments_fecha_inicio ON tournaments(fecha_inicio DESC);

-- Add indexes for equipos_tournament table
CREATE INDEX IF NOT EXISTS idx_equipos_tournament_id_tournament ON equipos_tournament(id_tournament);
CREATE INDEX IF NOT EXISTS idx_equipos_tournament_posicion ON equipos_tournament(id_tournament, posicion);

-- Add index for usuario table
CREATE INDEX IF NOT EXISTS idx_usuario_correo ON usuario(correo);
CREATE INDEX IF NOT EXISTS idx_usuario_is_admin ON usuario(is_admin);

-- Add composite index for the most common query pattern
CREATE INDEX IF NOT EXISTS idx_blog_posts_published_recent ON blog_posts(estado, fecha_publicacion DESC) WHERE estado = 'publicado';

-- Optimize tournament standings query
CREATE INDEX IF NOT EXISTS idx_tournaments_active ON tournaments(estado, fecha_inicio DESC) WHERE estado = 'activo';