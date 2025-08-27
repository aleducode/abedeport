-- Tournament Data - Static SQL File
-- Ready-to-import tournament data extracted from Excel files
-- Execute this file directly in production database

-- Clean existing tournament data (with transaction safety)
START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM equipos_tournament;
DELETE FROM tournaments;
ALTER TABLE equipos_tournament AUTO_INCREMENT = 1;
ALTER TABLE tournaments AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- Liga de Futsal 2024 (Tournament ID: 1)
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(1, 'Liga de Futsal 2024', 'futsal', '2024', 'activo', NOW());

-- Teams for Liga de Futsal 2024 (16 teams)
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(1, 'PANTANILLO', 14, 11, 2, 1, 88, 37, 35, 1),
(1, 'PURIMA', 14, 11, 1, 2, 69, 31, 34, 2),
(1, 'CABUYA F.C', 15, 9, 5, 1, 74, 46, 28, 3),
(1, 'DIESEL', 15, 9, 6, 0, 80, 54, 27, 4),
(1, 'DORTMUND', 15, 8, 7, 0, 70, 52, 24, 5),
(1, 'ALTO DE LETRAS', 15, 8, 6, 1, 73, 65, 25, 6),
(1, 'FUTSAL ABEJORRAL', 14, 8, 6, 0, 47, 42, 24, 7),
(1, 'LEONES F.C', 14, 7, 6, 1, 52, 40, 22, 8),
(1, 'TEACHERS CLUB', 15, 7, 7, 1, 74, 73, 22, 9),
(1, 'AGUILAS', 13, 6, 5, 2, 55, 53, 20, 10),
(1, 'TEXAS CLUB', 14, 6, 8, 0, 51, 52, 18, 11),
(1, 'LA CALLE', 14, 2, 9, 3, 35, 50, 9, 12),
(1, 'UNION Y FE', 14, 3, 11, 0, 46, 76, 9, 13),
(1, 'POKER F.C', 14, 2, 10, 2, 31, 71, 8, 14),
(1, 'LOS DISCIPULOS', 14, 2, 11, 1, 32, 74, 7, 15),
(1, 'LOS MOCHOS F.C', 13, 1, 11, 1, 25, 80, 4, 16);

-- Liga de Baloncesto 2024 (Tournament ID: 2)
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(2, 'Liga de Baloncesto 2024', 'baloncesto', '2024', 'activo', NOW());

-- Teams for Liga de Baloncesto 2024 (4 teams)
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(2, 'Guerreros', 5, 4, 1, 0, 277, 205, 12, 1),
(2, 'Cabuya', 5, 3, 2, 0, 205, 232, 9, 2),
(2, 'Dinasty Club', 5, 2, 3, 0, 218, 267, 6, 3),
(2, 'Los Profes', 4, 1, 3, 0, 118, 144, 3, 4);

-- Liga de Voleibol 2024 (Tournament ID: 3)
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(3, 'Liga de Voleibol 2024', 'voleibol', '2024', 'activo', NOW());

-- Teams for Liga de Voleibol 2024 (4 teams)
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(3, 'Equipo Nro 4 (Jefry)', 6, 6, 0, 0, 546, 450, 18, 1),
(3, 'The Team', 5, 3, 2, 0, 487, 427, 9, 2),
(3, 'Cabuya', 4, 1, 3, 0, 299, 330, 3, 3),
(3, 'Equipo Nro 2 (Salomé)', 5, 0, 5, 0, 368, 465, 0, 4);

-- Commit transaction
COMMIT;

-- Verification queries
SELECT 'Tournaments imported:' as info, COUNT(*) as count FROM tournaments;
SELECT 'Teams imported:' as info, COUNT(*) as count FROM equipos_tournament;
SELECT t.nombre as Tournament, t.deporte as Sport, COUNT(et.id_equipo_tournament) as Teams
FROM tournaments t 
LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
GROUP BY t.id_tournament;