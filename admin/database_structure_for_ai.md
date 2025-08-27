# 🏆 ESTRUCTURA DE BASE DE DATOS - SISTEMA DE TORNEOS DEPORTIVOS

## 📋 INFORMACIÓN PARA IA: CARGA DE TABLAS DE POSICIONES DESDE FOTOS/EXCEL

### 🗄️ **ESTRUCTURA DE TABLAS**

#### **1. TABLA: `tournaments`**
```sql
CREATE TABLE `tournaments` (
  `id_tournament` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `deporte` enum('futbol','futsal','baloncesto','voleibol') NOT NULL,
  `temporada` varchar(50) NOT NULL,
  `estado` enum('activo','finalizado','proximo') DEFAULT 'activo',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_tournament`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

**CAMPOS:**
- `id_tournament`: ID único del torneo (auto-incremental)
- `nombre`: Nombre del torneo (ej: "Liga Profesional de Fútbol 2024")
- `deporte`: Tipo de deporte ('futbol', 'futsal', 'baloncesto', 'voleibol')
- `temporada`: Temporada del torneo (ej: "2024-1", "2024")
- `estado`: Estado del torneo ('activo', 'finalizado', 'proximo')
- `fecha_inicio`: Fecha de inicio (formato: YYYY-MM-DD)
- `fecha_fin`: Fecha de finalización (formato: YYYY-MM-DD)

#### **2. TABLA: `equipos_tournament`**
```sql
CREATE TABLE `equipos_tournament` (
  `id_equipo_tournament` int NOT NULL AUTO_INCREMENT,
  `id_tournament` int NOT NULL,
  `nombre_equipo` varchar(100) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `partidos_jugados` int DEFAULT '0',
  `partidos_ganados` int DEFAULT '0',
  `partidos_perdidos` int DEFAULT '0',
  `partidos_empatados` int DEFAULT '0',
  `puntos_favor` int DEFAULT '0',
  `puntos_contra` int DEFAULT '0',
  `puntos_totales` int DEFAULT '0',
  `posicion` int DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_equipo_tournament`),
  FOREIGN KEY (`id_tournament`) REFERENCES `tournaments` (`id_tournament`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

**CAMPOS PRINCIPALES:**
- `id_equipo_tournament`: ID único del equipo en el torneo
- `id_tournament`: ID del torneo al que pertenece (FOREIGN KEY)
- `nombre_equipo`: Nombre del equipo (ej: "Real Madrid CF")
- `ciudad`: Ciudad del equipo (ej: "Madrid")
- `pais`: País del equipo (ej: "España")
- `logo`: URL del logo del equipo (opcional)

**ESTADÍSTICAS DE PARTIDOS:**
- `partidos_jugados`: Total de partidos jugados (PJ)
- `partidos_ganados`: Partidos ganados (PG)
- `partidos_perdidos`: Partidos perdidos (PP)
- `partidos_empatados`: Partidos empatados (PE)
- `puntos_favor`: Puntos/goles a favor (PF)
- `puntos_contra`: Puntos/goles en contra (PC)
- `puntos_totales`: Puntos totales en la tabla (Pts)
- `posicion`: Posición en la tabla (1, 2, 3, etc.)
- `destacado`: Si el equipo está destacado (0 = No, 1 = Sí)

---

## 📊 **EJEMPLOS DE DATOS ACTUALES**

### **Torneos Disponibles:**
```
ID | Nombre                           | Deporte    | Temporada
12 | Liga Profesional de Fútbol 2024  | futbol     | 2024-1
13 | Torneo Nacional de Fútsal        | futsal     | 2024
14 | Campeonato Regional de Baloncesto| baloncesto | 2024
15 | Liga Juvenil de Voleibol         | voleibol   | 2024-2
```

### **Ejemplo de Tabla de Posiciones (Fútbol):**
```
Pos | Equipo                      | Ciudad        | País   | PJ | PG | PP | PE | PF | PC | Pts
1   | Real Madrid CF              | Madrid        | España | 12 | 9  | 2  | 1  | 28 | 10 | 28
2   | FC Barcelona                | Barcelona     | España | 12 | 8  | 3  | 1  | 26 | 12 | 25
3   | Atlético de Madrid          | Madrid        | España | 12 | 7  | 3  | 2  | 22 | 15 | 23
4   | Athletic Club Bilbao        | Bilbao        | España | 12 | 6  | 4  | 2  | 20 | 18 | 20
5   | Valencia CF                 | Valencia      | España | 12 | 5  | 5  | 2  | 18 | 20 | 17
6   | Real Sociedad San Sebastián | San Sebastián | España | 12 | 4  | 6  | 2  | 16 | 22 | 14
```

---

## 🤖 **INSTRUCCIONES PARA IA**

### **OBJETIVO:**
Extraer datos de una tabla de posiciones deportiva desde una **foto** o **archivo Excel** y generar los comandos SQL INSERT necesarios para cargar los datos en la base de datos.

### **PASOS A SEGUIR:**

#### **1. IDENTIFICAR EL TORNEO:**
- Extraer nombre del torneo de la imagen/Excel
- Determinar el tipo de deporte (futbol, futsal, baloncesto, voleibol)
- Identificar la temporada

#### **2. CREAR EL TORNEO (si no existe):**
```sql
INSERT INTO tournaments (nombre, deporte, temporada, estado) 
VALUES ('Nombre del Torneo', 'futbol', '2024', 'activo');
```

#### **3. EXTRAER DATOS DE EQUIPOS:**
De la tabla de posiciones, extraer para cada equipo:
- **Posición** (obligatorio)
- **Nombre del equipo** (obligatorio)
- **Partidos jugados** (PJ)
- **Partidos ganados** (PG)
- **Partidos perdidos** (PP)
- **Partidos empatados** (PE)
- **Puntos/goles a favor** (PF)
- **Puntos/goles en contra** (PC)
- **Puntos totales** (Pts)

#### **4. GENERAR COMANDOS INSERT:**
```sql
-- Asumiendo que el torneo tiene ID = X (reemplazar con el ID real)
INSERT INTO equipos_tournament 
(id_tournament, nombre_equipo, ciudad, pais, partidos_jugados, partidos_ganados, 
 partidos_perdidos, partidos_empatados, puntos_favor, puntos_contra, 
 puntos_totales, posicion, destacado) 
VALUES 
(X, 'Nombre Equipo 1', 'Ciudad', 'País', PJ, PG, PP, PE, PF, PC, Pts, 1, 0),
(X, 'Nombre Equipo 2', 'Ciudad', 'País', PJ, PG, PP, PE, PF, PC, Pts, 2, 0),
-- ... más equipos
;
```

### **CONSIDERACIONES ESPECIALES:**

#### **📋 ABREVIACIONES COMUNES:**
- **PJ** = Partidos Jugados
- **PG** = Partidos Ganados  
- **PP** = Partidos Perdidos
- **PE** = Partidos Empatados
- **PF** = Puntos/Goles a Favor
- **PC** = Puntos/Goles en Contra
- **Pts** = Puntos Totales
- **Pos** = Posición

#### **🏀 DEPORTES ESPECÍFICOS:**
- **Fútbol/Fútsal**: PF/PC = goles
- **Baloncesto**: PF/PC = puntos anotados
- **Voleibol**: PF/PC = sets ganados/perdidos

#### **🎯 VALORES POR DEFECTO:**
- Si no se encuentra un dato: usar 0
- `destacado`: usar 0 (excepto si está marcado como destacado)
- `ciudad` y `pais`: intentar inferir o dejar NULL

#### **🔤 CHARSET:**
- Usar UTF-8 para caracteres especiales (ñ, á, é, í, ó, ú)
- Ejemplos: "Atlético", "São Paulo", "Medellín"

---

## 📁 **FORMATO CSV ESPERADO (para referencia):**
```csv
Equipo,Ciudad,País,Logo,PJ,PG,PP,PE,PF,PC,Pts,Posición,Destacado
Real Madrid CF,Madrid,España,,12,9,2,1,28,10,28,1,1
FC Barcelona,Barcelona,España,,12,8,3,1,26,12,25,2,0
```

---

## 🔗 **CONEXIÓN A LA BASE DE DATOS:**
- **Host**: localhost (o IP del servidor)
- **Puerto**: 3306
- **Base de datos**: ABEDEPORT
- **Usuario**: abedeport_user
- **Contraseña**: abedeport_password
- **Charset**: utf8mb4

---

**¡Esta estructura te permitirá cargar cualquier tabla de posiciones deportiva en el sistema!**