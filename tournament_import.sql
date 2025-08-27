-- Tournament Data Import SQL
-- Generated automatically from Excel files
-- Execute this file directly in MySQL/phpMyAdmin

-- Clean existing tournament data
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM equipos_tournament;
DELETE FROM tournaments;
ALTER TABLE equipos_tournament AUTO_INCREMENT = 1;
ALTER TABLE tournaments AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- Liga de Futsal 2024
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(1, 'Liga de Futsal 2024', 'futsal', '2024', 'activo', NOW());

-- Teams for Liga de Futsal 2024
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(1, 'PANTANILLO', 14, 11, 2, 88, 37, 51, 35, 1),
(1, 'PURIMA', 14, 11, 1, 69, 31, 38, 34, 2),
(1, 'CABUYA F.C', 15, 9, 5, 1, 74, 46, 28, 3),
(1, 'DIESEL', 15, 9, 2, 80, 54, 26, 29, 4),
(1, 'DORTMUND', 15, 8, 2, 70, 52, 18, 26, 5),
(1, 'ALTO DE LETRAS', 15, 8, 1, 73, 65, 8, 25, 6),
(1, 'FUTSAL ABEJORRAL', 14, 8, 1, 5, 47, 42, 5, 7),
(1, 'LEONES F.C', 14, 7, 1, 6, 52, 40, 12, 8),
(1, 'TEACHERS CLUB', 15, 7, 1, 7, 74, 73, 1, 9),
(1, 'AGUILAS', 13, 6, 2, 5, 55, 53, 2, 10),
(1, 'TEXAS CLUB', 14, 6, 0, 8, 51, 52, -1, 11),
(1, 'LA CALLE', 14, 2, 3, 9, 35, 50, -15, 12),
(1, 'UNION Y FE', 14, 3, 0, 11, 46, 76, -30, 13),
(1, 'POKER F.C', 2, 2, 10, 31, 71, -40, 8, 14),
(1, 'LOS DISCIPULOS', 2, 2, 11, 32, 74, -42, 8, 15),
(1, 'LOS MOCHOS F.C', 13, 1, 1, 11, 25, 80, -55, 16);

-- Liga de Baloncesto 2024
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(2, 'Liga de Baloncesto 2024', 'baloncesto', '2024', 'activo', NOW());

-- Teams for Liga de Baloncesto 2024
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(2, 'Guerreros', 5, 4, 277, 205, 72, 9, 217, 1),
(2, 'Cabuya', 5, 3, 205, 232, -27, 7, 241, 2),
(2, 'Dinasty Club', 5, 2, 218, 267, -49, 7, 273, 3),
(2, 'Los Profes', 3, 1, 2, 118, 114, 0, 121, 4);

-- Liga de Voleibol 2024
INSERT INTO tournaments (id_tournament, nombre, deporte, temporada, estado, fecha_inicio) VALUES
(3, 'Liga de Voleibol 2024', 'voleibol', '2024', 'activo', NOW());

-- Teams for Liga de Voleibol 2024
INSERT INTO equipos_tournament (id_tournament, nombre_equipo, partidos_jugados, partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, puntos_totales, posicion) VALUES
(3, 'Equipo Nro 4 (Jefry)', 6, 6, 0, 546, 450, 96, 12, 1),
(3, 'The Team', 5, 3, 487, 427, 60, 8, 436, 2),
(3, 'Cabuya', 4, 1, 299, 330, -31, 5, 333, 3),
(3, 'Equipo Nro 2 (Salomé)', 5, 0, 5, 368, 465, -97, 5, 4);

-- Import completed
-- Imported 3 tournaments with 24 teams total
