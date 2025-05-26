# Scripts SQL para la Base de Datos hermes002

Este archivo contiene instrucciones y consultas SQL que deben ejecutarse en la base de datos `hermes002` para realizar actualizaciones o modificaciones específicas.

**¡IMPORTANTE!**

* Asegúrate de estar conectado a la base de datos `hermes002` antes de ejecutar estas consultas.
* Realiza una copia de seguridad de la base de datos antes de ejecutar cualquier script, por si acaso necesitas revertir los cambios.
* Ejecuta las consultas en el orden en que aparecen en este archivo, si el orden es relevante.

## Consultas y Procedimientos

///--------------------------------------------------------------------------------------------------------------------------------///
///INICIO DEL MODULO DE DEVOLUCIONES///

### 1. Hacer modificaciones en la tabla `prestamos`

```sql
ALTER TABLE prestamos
MODIFY COLUMN estado_prestamo ENUM('Prestado','Mantenimiento','Rechazado','Autorizado','Pendiente','Tramite','Disponible') NOT NULL DEFAULT 'Disponible';
```

### 2. Hacer modificaciones en la tabla `detalle_prestamos`
/// Agrega una nueva columna llamada id_estado a la tabla detalle_prestamos, la cual sirve para saber el estado del prestamo y es IMPORTANTE para el trigger///

```sql
ALTER TABLE detalle_prestamo
ADD COLUMN id_estado INT NULL; 
```

```sql
UPDATE detalle_prestamo dp
JOIN equipos e ON dp.equipo_id = e.equipo_id
SET dp.id_estado = e.id_estado;
```

```sql
ALTER TABLE detalle_prestamo
ADD CONSTRAINT fk_detalle_prestamo_estado
FOREIGN KEY (id_estado) REFERENCES estados(id_estado);
```

### 3. Crear un trigger para actualizar el estado del equipo cuando se devuelve

```sql
DELIMITER //
CREATE TRIGGER trg_actualizar_estado_equipo_desde_detalle
AFTER UPDATE ON detalle_prestamo
FOR EACH ROW
BEGIN
    -- Verificar si el id_estado en detalle_prestamo se actualizó a 4 (Mantenimiento)
    -- y si el valor realmente cambió para evitar ejecuciones innecesarias o recursión.
    IF NEW.id_estado = 4 AND NEW.id_estado <> OLD.id_estado THEN
        -- Actualizar el id_estado en la tabla equipos
        UPDATE equipos
        SET id_estado = 4 -- Mantenimiento
        WHERE equipo_id = NEW.equipo_id; -- Usar NEW.equipo_id de la fila actualizada en detalle_prestamo
    END IF;
END //
DELIMITER ;
```

### 4. crear un trigger para mandar el equipo a disponible o mantenimiento

```sql
DELIMITER //
CREATE TRIGGER trg_actualizar_estado_equipo_desde_detalle
AFTER UPDATE ON detalle_prestamo
FOR EACH ROW
BEGIN
    -- Verificar si el id_estado en detalle_prestamo se actualizó a 4 (Mantenimiento)
    -- y si el valor realmente cambió para evitar ejecuciones innecesarias o recursión.
    IF NEW.id_estado = 4 AND NEW.id_estado <> OLD.id_estado THEN
        -- Actualizar el id_estado en la tabla equipos
        UPDATE equipos
        SET id_estado = 4 -- Mantenimiento
        WHERE equipo_id = NEW.equipo_id; -- Usar NEW.equipo_id de la fila actualizada en detalle_prestamo
    END IF;
END //
DELIMITER ;
```

///FIN DEL MODULO DE DEVOLUCIONES///
///--------------------------------------------------------------------------------------------------------------------------------///

### 1.Agregar una nueva tabla 'mantenimiento' en la base de datos hermes002

```sql
CREATE TABLE `mantenimiento` (
  `Id_mantenimiento` int(30) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `detalles` text NOT NULL,
  `gravedad` enum('ninguno','leve','grave') DEFAULT 'ninguno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

``` 
### 2.Agregar dentro de la tabla 'mantenimiento'
```sql
INSERT INTO `mantenimiento` (`Id_mantenimiento`, `equipo_id`, `detalles`, `gravedad`) VALUES
(1, 1, '', 'ninguno'),
(2, 3, '', 'ninguno'),
(3, 12, '', 'ninguno'),
(4, 16, '', 'ninguno');

``` 	
### 3.Agregar los indices para la tabla 'mantenimiento'

```sql
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`Id_mantenimiento`),
  ADD KEY `equipo_id` (`equipo_id`);

```
### 4.Agregar el AUTO_INCREMENT para la tabla 'mantenimiento'

```sql
ALTER TABLE `mantenimiento`
  MODIFY `Id_mantenimiento` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

```

### 5.Agregar columna `foto` a la tabla `usuarios`
### Agregar columna `condicion` a la tabla `usuarios`

Se debe agregar una columna llamada `condicion` de tipo `ENUM` a la tabla `usuarios`, ubicada después de la columna `estado`. Los posibles valores son `'penalizado'`, `'advertido'` y `'en_regla'`, siendo `'en_regla'` el valor por defecto.

```sql
ALTER TABLE usuarios
ADD COLUMN condicion ENUM('penalizado', 'advertido', 'en_regla') NOT NULL DEFAULT 'en_regla'
AFTER estado;
```

### Encriptación de claves para usuarios

Se recomienda almacenar las contraseñas de los usuarios encriptadas utilizando el algoritmo bcrypt. A continuación se muestran los valores encriptados para las contraseñas de ejemplo:

- **admin123**  
  Valor encriptado:  
  `$2a$07$asxx54ahjppf45sd87a5aunxs9bkpyGmGE/.vekdjFg83yRec789S`

- **clave123**  
  Valor encriptado:  
  `$2a$07$asxx54ahjppf45sd87a5auPSL9GB5Ad5sH/D3rUMKo4UJe4U/qGLO`

#### Consultas SQL para actualizar las contraseñas en la tabla `usuarios`

```sql
-- Asignar clave encriptada para el usuario administrador : clave=admin123
UPDATE usuarios
SET clave = '$2a$07$asxx54ahjppf45sd87a5aunxs9bkpyGmGE/.vekdjFg83yRec789S'
WHERE nombre = 'admin';

-- Asignar clave encriptada para los demás usuarios excepto para el admin clave=clave123
UPDATE usuarios
SET clave = '$2a$07$asxx54ahjppf45sd87a5auPSL9GB5Ad5sH/D3rUMKo4UJe4U/qGLO'
WHERE nombre <> 'admin';


Asegúrate de que la columna `clave` en la tabla `usuarios` tenga suficiente longitud para almacenar los hashes bcrypt (al menos `VARCHAR(200)`).

### Agregar columna `foto` a la tabla `usuarios`

Se debe agregar una columna llamada `foto` de tipo `VARCHAR(100)` a la tabla `usuarios`, ubicada después de la columna `genero`.

```sql
ALTER TABLE usuarios
ADD COLUMN foto VARCHAR(100) AFTER genero;
```

### Ruta por defecto para la foto de usuario

Al crear un nuevo usuario, el valor por defecto de la columna `foto` debe ser:  
`vistas/img/usuarios/default/anonymous.png`

### Creación automática de carpetas para fotos de usuario

Cuando se crea un usuario nuevo:
- Se debe crear automáticamente la carpeta `img` dentro de la carpeta `vistas` si no existe.
- Dentro de `img`, se debe crear la carpeta `usuarios`.
- Dentro de `usuarios`, se debe crear una carpeta con el número de documento del usuario.
- En esa carpeta es donde se almacenará la foto del usuario.

### Ejemplo de actualización de datos

```sql
-- Ejemplo: Actualizar la ruta de la foto para todos los usuarios existentes
UPDATE usuarios
SET foto = 'vistas/img/usuarios/default/anonymous.png';
```



### 5. Agregar tabla  `auditoria_usuarios` 
```sql
CREATE TABLE auditoria_aprendices 
  id INT AUTO_INCREMENT PRIMARY KEY,
  accion VARCHAR(50),
  descripcion TEXT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
  ```
  -Esto permite rastrear qué acciones se han ejecutado sobre los datos de los aprendices, cuándo ocurrieron y en qué consistieron

### 6. Agregar trigger a la tabla  `auditoria_usuarios`
```sql 
  DELIMITER $$

CREATE TRIGGER trg_auditar_usuarios
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
  DECLARE cambios TEXT DEFAULT '';
  DECLARE valores_anteriores TEXT DEFAULT '';
  DECLARE valores_nuevos TEXT DEFAULT '';

  -- Comparar y registrar cada campo que cambie
  IF OLD.tipo_documento <> NEW.tipo_documento THEN
    SET cambios = CONCAT(cambios, 'tipo_documento; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.tipo_documento, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.tipo_documento, '; ');
  END IF;

  IF OLD.numero_documento <> NEW.numero_documento THEN
    SET cambios = CONCAT(cambios, 'numero_documento; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.numero_documento, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.numero_documento, '; ');
  END IF;

  IF OLD.nombre <> NEW.nombre THEN
    SET cambios = CONCAT(cambios, 'nombre; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.nombre, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.nombre, '; ');
  END IF;

  IF OLD.apellido <> NEW.apellido THEN
    SET cambios = CONCAT(cambios, 'apellido; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.apellido, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.apellido, '; ');
  END IF;

  IF OLD.correo_electronico <> NEW.correo_electronico THEN
    SET cambios = CONCAT(cambios, 'correo_electronico; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.correo_electronico, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.correo_electronico, '; ');
  END IF;

  IF OLD.telefono <> NEW.telefono THEN
    SET cambios = CONCAT(cambios, 'telefono; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.telefono, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.telefono, '; ');
  END IF;

  IF OLD.direccion <> NEW.direccion THEN
    SET cambios = CONCAT(cambios, 'direccion; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.direccion, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.direccion, '; ');
  END IF;

  IF OLD.genero <> NEW.genero THEN
    SET cambios = CONCAT(cambios, 'genero; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.genero, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.genero, '; ');
  END IF;

  IF OLD.estado <> NEW.estado THEN
    SET cambios = CONCAT(cambios, 'estado; ');
    SET valores_anteriores = CONCAT(valores_anteriores, OLD.estado, '; ');
    SET valores_nuevos = CONCAT(valores_nuevos, NEW.estado, '; ');
  END IF;

  -- Si hubo algún cambio, insertar en la tabla de auditoría
  IF cambios <> '' THEN
    INSERT INTO auditoria_usuarios (
      id_usuario_afectado,
      id_usuario_editor,
      campo_modificado,
      valor_anterior,
      valor_nuevo,
      fecha_cambio
    ) VALUES (
      OLD.id_usuario,
      NEW.id_usuario, -- Asume que el campo NEW.id_usuario guarda el editor; puedes ajustarlo según tu lógica
      cambios,
      valores_anteriores,
      valores_nuevos,
      NOW()
    );
  END IF;
END$$

DELIMITER ;
```
-Este trigger compara los valores antiguos y nuevos de varios campos (como tipo_documento, nombre, apellido, correo_electronico, etc.) y, si detecta algún cambio, inserta un registro en la tabla auditoria_usuarios con los siguientes datos:

ID del usuario afectado.

ID del usuario que realizó el cambio.

Campos modificados.

Valores anteriores.

Valores nuevos.

Fecha del cambio


