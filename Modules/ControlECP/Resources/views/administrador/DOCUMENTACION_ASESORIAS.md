# Documentación Técnica: Módulo de Asesorías (HU-012)
**Módulo:** `Modules\ControlECP`  
**Rol:** Administrador (`controlecp.admin`)  
**Fecha:** Octubre 2026  

---

## 1. Archivos Creados y Modificados

### Migraciones (`Modules/ControlECP/Database/Migrations/`)
- `2026_10_18_000001_create_categorias_asesoria_table.php`: Crea la tabla `categorias_asesoria` para registrar las temáticas o líneas de asesoría.
- `2026_10_18_000002_create_instructor_categoria_table.php`: Crea la tabla pivote `instructor_categoria` que asocia a los instructores con las categorías que pueden atender.
- `2026_10_18_000003_create_disponibilidades_instructor_table.php`: Crea la tabla `disponibilidades_instructor` con franjas semanales por año y trimestre (con flags de habilitada y vigente).
- `2026_10_18_000004_create_asesorias_table.php`: Crea la tabla principal `asesorias` que almacena solicitudes, programaciones, fechas, horas, estados y auditoría de creación/cancelación.
- `2026_10_18_000005_create_asesoria_historial_table.php`: Crea la tabla `asesoria_historial` para auditar reprogramaciones y cancelaciones con fechas previas, fechas nuevas y motivos obligatorios.
- `2026_10_18_000006_create_asesoria_seguimientos_table.php`: Crea la tabla `asesoria_seguimientos` para registrar avances y compromisos, permitiendo correcciones inmutables mediante `id_asesoria_seguimiento_corrige`.
- `2026_10_18_000007_create_asesoria_recursos_table.php`: Crea la tabla `asesoria_recursos` para relacionar elementos del inventario institucional utilizados en los encuentros.

### Modelos / Entidades (`Modules/ControlECP/Entities/`)
- `CategoriaAsesoria.php`: Representa una temática; define relaciones `hasMany(Asesoria)`, `hasMany(InstructorCategoria)` y `belongsToMany(User)`.
- `InstructorCategoria.php`: Tabla intermedia; define relaciones `belongsTo(CategoriaAsesoria)` y `belongsTo(User, 'id_usuario_instructor')`.
- `DisponibilidadInstructor.php`: Franja horaria de un instructor; relación `belongsTo(User, 'id_usuario_instructor')` y accessor `getDiaNombreAttribute()`.
- `Asesoria.php`: Entidad central; define relaciones `belongsTo` a aprendiz, instructor, registrador, cancelador y categoría; `hasMany` a historial, seguimientos y recursos.
- `AsesoriaHistorial.php`: Registro de auditoría inmutable; relaciones `belongsTo(Asesoria)` y `belongsTo(User, 'id_usuario_accion')`.
- `AsesoriaSeguimiento.php`: Bitácora de acompañamiento; relaciones `belongsTo(Asesoria)`, `belongsTo(User)`, `belongsTo(AsesoriaSeguimiento, 'corrige')` y `hasMany('correcciones')`.
- `AsesoriaRecurso.php`: Material asignado a la asesoría; relaciones `belongsTo(Asesoria)` y `belongsTo(Inventory, 'id_inventario')`.

### Controlador y FormRequests
- `Modules/ControlECP/Http/Controllers/AsesoriaController.php`: Controlador con métodos individuales para listado, consultas AJAX, programación, reprogramación, cancelación, seguimientos, recursos, categorías y reportes.
- `Modules/ControlECP/Http/Requests/StoreAsesoriaRequest.php`: Valida aprendiz (`controlecp.apprentice`), categoría, fecha, horario y motivo de nueva solicitud.
- `Modules/ControlECP/Http/Requests/ProgramarAsesoriaRequest.php`: Valida asignación obligatoria de instructor, fecha y horas válidas (`hora_fin > hora_inicio`).
- `Modules/ControlECP/Http/Requests/ReprogramarAsesoriaRequest.php`: Valida nueva fecha, horario y motivo obligatorio de reprogramación (mínimo 5 caracteres).
- `Modules/ControlECP/Http/Requests/CancelarAsesoriaRequest.php`: Valida motivo obligatorio de cancelación (mínimo 5 caracteres).
- `Modules/ControlECP/Http/Requests/StoreSeguimientoRequest.php`: Valida campos de avances y compromisos al realizar asesoría.
- `Modules/ControlECP/Http/Requests/CorregirSeguimientoRequest.php`: Valida motivo obligatorio de corrección y nuevos avances/compromisos.
- `Modules/ControlECP/Http/Requests/AsignarRecursoRequest.php`: Valida existencia del recurso en `inventories` y cantidad mínima de 1.

### Vistas, Navegación y Rutas
- `Modules/ControlECP/Resources/views/administrador/asesoria.blade.php`: Vista consolidada en un solo archivo con CSS propio, barra de filtros, indicadores, tabla interactiva, gráficas CSS nativas, panel de categorías, 11 modales y scripts.
- `Modules/ControlECP/Resources/views/components/sidebar_administrador.blade.php`: Elemento del sidebar con ícono de asesorías y detección de ruta activa `controlecp.administrador.asesorias.*`.
- `Modules/ControlECP/Routes/web.php`: Registro del grupo de 16 rutas con middleware de rol administrador.

---

## 2. Base de Datos

### Las 7 Tablas del Módulo

1. **`categorias_asesoria`**
   - `id_categoria_asesoria` (bigIncrements, PK)
   - `nombre` (string 100, unique)
   - `descripcion` (text, nullable)
   - `estado` (enum: `'Activa'`, `'Inactiva'`, default `'Activa'`)
   - `created_at`, `updated_at` (timestamps)

2. **`instructor_categoria`**
   - `id_instructor_categoria` (bigIncrements, PK)
   - `id_usuario_instructor` (unsignedBigInteger, FK -> `users.id` on delete cascade)
   - `id_categoria_asesoria` (unsignedBigInteger, FK -> `categorias_asesoria.id_categoria_asesoria` on delete cascade)
   - `created_at`, `updated_at` (timestamps)
   - Restricción única: `['id_usuario_instructor', 'id_categoria_asesoria']`

3. **`disponibilidades_instructor`**
   - `id_disponibilidad` (bigIncrements, PK)
   - `id_usuario_instructor` (unsignedBigInteger, FK -> `users.id` on delete cascade)
   - `anio` (year)
   - `trimestre` (tinyInteger unsigned, 1 a 4)
   - `dia_semana` (tinyInteger unsigned: 1=Lunes, 2=Martes, ..., 7=Domingo)
   - `hora_inicio` (time)
   - `hora_fin` (time)
   - `habilitada` (boolean, default true)
   - `vigente` (boolean, default true)
   - `created_at`, `updated_at` (timestamps)

4. **`asesorias`**
   - `id_asesoria` (bigIncrements, PK)
   - `id_usuario_aprendiz` (unsignedBigInteger, FK -> `users.id` on delete cascade)
   - `id_usuario_instructor` (unsignedBigInteger, nullable, FK -> `users.id` on delete set null)
   - `id_categoria_asesoria` (unsignedBigInteger, FK -> `categorias_asesoria.id_categoria_asesoria` on delete restrict)
   - `id_usuario_registro` (unsignedBigInteger, nullable, FK -> `users.id` on delete set null)
   - `fecha` (date, nullable)
   - `hora_inicio` (time, nullable)
   - `hora_fin` (time, nullable)
   - `estado` (enum: `'Solicitada'`, `'Programada'`, `'Realizada'`, `'Cancelada'`, default `'Solicitada'`)
   - `motivo` (text, nullable)
   - `fecha_solicitud` (timestamp, nullable)
   - `motivo_cancelacion` (text, nullable)
   - `id_usuario_cancelador` (unsignedBigInteger, nullable, FK -> `users.id` on delete set null)
   - `fecha_cancelacion` (timestamp, nullable)
   - `created_at`, `updated_at` (timestamps)

5. **`asesoria_historial`**
   - `id_historial` (bigIncrements, PK)
   - `id_asesoria` (unsignedBigInteger, FK -> `asesorias.id_asesoria` on delete cascade)
   - `id_usuario_accion` (unsignedBigInteger, FK -> `users.id` on delete cascade)
   - `accion` (enum: `'Reprogramo'`, `'Cancelo'`)
   - `motivo` (text)
   - `fecha_anterior` (date, nullable)
   - `hora_inicio_anterior` (time, nullable)
   - `hora_fin_anterior` (time, nullable)
   - `fecha_nueva` (date, nullable)
   - `hora_inicio_nueva` (time, nullable)
   - `hora_fin_nueva` (time, nullable)
   - `created_at`, `updated_at` (timestamps)

6. **`asesoria_seguimientos`**
   - `id_seguimiento` (bigIncrements, PK)
   - `id_asesoria` (unsignedBigInteger, FK -> `asesorias.id_asesoria` on delete cascade)
   - `id_usuario_seguimiento` (unsignedBigInteger, FK -> `users.id` on delete cascade)
   - `id_asesoria_seguimiento_corrige` (unsignedBigInteger, nullable, FK -> `asesoria_seguimientos.id_seguimiento` on delete set null)
   - `avances` (text)
   - `compromisos` (text)
   - `observaciones` (text, nullable)
   - `motivo_correccion` (text, nullable)
   - `created_at`, `updated_at` (timestamps)

7. **`asesoria_recursos`**
   - `id_asesoria_recurso` (bigIncrements, PK)
   - `id_asesoria` (unsignedBigInteger, FK -> `asesorias.id_asesoria` on delete cascade)
   - `id_inventario` (unsignedBigInteger, FK -> `inventories.id` on delete cascade)
   - `cantidad` (integer unsigned, default 1)
   - `created_at`, `updated_at` (timestamps)

### Tablas Existentes Leídas
- `users`: Usuarios del sistema.
- `roles` y `role_user`: Roles `controlecp.admin`, `controlecp.instructor` y `controlecp.apprentice`.
- `people`: Datos de personas vinculadas a los usuarios (`person->first_name`, `person->first_last_name`, etc. para `full_name`).
- `inventories` y `elements`: Catálogo de inventario institucional.

### Único Dato Ajeno que se Modifica
- **`inventories.stock`** e **`inventories.amount`**: Al asociar un recurso a una asesoría (`POST /{id}/recursos`), se descuenta atómicamente la cantidad utilizada dentro de una transacción con `DB::transaction`.

---

## 3. Rutas

Prefijo URL: `control-ecp/administrador/asesorias`  
Prefijo de Nombre: `controlecp.administrador.asesorias.`

| Método | URL | Nombre Completo | Método Controlador | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `controlecp.administrador.asesorias.index` | `index` | Listado paginado, indicadores, filtros y gráficas. |
| `POST` | `/` | `controlecp.administrador.asesorias.store` | `store` | Crea solicitud inicial o programa directo. |
| `GET` | `/instructores-disponibles` | `controlecp.administrador.asesorias.instructores-disponibles` | `instructoresDisponibles` | Búsqueda AJAX de instructores habilitados sin cruce. |
| `GET` | `/reporte` | `controlecp.administrador.asesorias.reporte` | `reporte` | Descarga CSV (Excel) o vista de impresión para PDF. |
| `GET` | `/disponibilidad/{usuario}` | `controlecp.administrador.asesorias.disponibilidad` | `consultarDisponibilidad` | Consulta AJAX de franjas de un instructor. |
| `POST` | `/disponibilidad` | `controlecp.administrador.asesorias.disponibilidad.guardar` | `guardarDisponibilidad` | Guarda franja (pasa anteriores a vigente=false). |
| `POST` | `/categorias` | `controlecp.administrador.asesorias.categorias.store` | `storeCategoria` | Crea categoría y vincula instructores. |
| `PUT` | `/categorias/{id}` | `controlecp.administrador.asesorias.categorias.update` | `updateCategoria` | Actualiza nombre e instructores asignados. |
| `PUT` | `/categorias/{id}/estado` | `controlecp.administrador.asesorias.categorias.estado` | `toggleCategoriaEstado` | Alterna estado Activa/Inactiva (no elimina). |
| `PUT` | `/seguimiento/{id}` | `controlecp.administrador.asesorias.seguimiento.corregir` | `corregirSeguimiento` | Inserta nueva corrección enlazada al seguimiento. |
| `GET` | `/{id}` | `controlecp.administrador.asesorias.show` | `show` | Retorna JSON con ficha completa y trazabilidad. |
| `PUT` | `/{id}/programar` | `controlecp.administrador.asesorias.programar` | `programar` | Asigna instructor y pasa a Programada con transacción. |
| `PUT` | `/{id}/reprogramar` | `controlecp.administrador.asesorias.reprogramar` | `reprogramar` | Valida cruce, audita en historial y cambia horario. |
| `PUT` | `/{id}/cancelar` | `controlecp.administrador.asesorias.cancelar` | `cancelar` | Audita en historial y pasa a Cancelada. |
| `POST` | `/{id}/seguimiento` | `controlecp.administrador.asesorias.seguimiento` | `storeSeguimiento` | Registra avances/compromisos y pasa a Realizada. |
| `POST` | `/{id}/recursos` | `controlecp.administrador.asesorias.recursos` | `agregarRecurso` | Valida stock, descuenta inventario y asocia recurso. |

---

## 4. Controlador (`AsesoriaController.php`)

1. **`index(Request $request)`**
   - *Recibe:* Filtros opcionales `buscar`, `estado`, `id_categoria_asesoria`, `fecha_desde`, `fecha_hasta`.
   - *Valida:* Sanitización implícita.
   - *Toca:* Lee `asesorias`, `categorias_asesoria`, `users`, `inventories`.
   - *Devuelve:* Vista Blade con indicadores de conteo, asesorías paginadas, listas de selección y series para gráficas.

2. **`show($id)`**
   - *Recibe:* ID de la asesoría.
   - *Valida:* `Asesoria::findOrFail($id)`.
   - *Toca:* Lee `asesorias` cargando `aprendiz`, `instructor`, `categoria`, `cancelador`, `seguimientos`, `historial`, `recursos`.
   - *Devuelve:* JSON con objeto `asesoria` formateado para el modal de detalle.

3. **`instructoresDisponibles(Request $request)`**
   - *Recibe:* `categoria_id`, `fecha`, `hora_inicio`, `hora_fin`, `asesoria_id` (opcional).
   - *Valida:* Existencia de parámetros y `hora_inicio < hora_fin`.
   - *Toca:* Lee `instructor_categoria`, `disponibilidades_instructor` y `asesorias`.
   - *Devuelve:* JSON con array de instructores disponibles sin cruce de horario.

4. **`store(StoreAsesoriaRequest $request)`**
   - *Recibe:* `id_usuario_aprendiz`, `id_categoria_asesoria`, `fecha`, `hora_inicio`, `hora_fin`, `motivo`, `id_usuario_instructor` (opcional).
   - *Valida:* Reglas de `StoreAsesoriaRequest`.
   - *Toca:* Inserta en `asesorias` (`id_usuario_registro = Auth::id()`, `fecha_solicitud = now()`).
   - *Devuelve:* Redirección `back` con mensaje flash. Si se asignó instructor sin cruce, pasa a `Programada`; si no, queda en `Solicitada`.

5. **`programar(ProgramarAsesoriaRequest $request, $id)`**
   - *Recibe:* ID de asesoría, `id_usuario_instructor`, `fecha`, `hora_inicio`, `hora_fin`.
   - *Valida:* Reglas de `ProgramarAsesoriaRequest` y verificación de cruce del instructor.
   - *Toca:* Actualiza `asesorias` a `Programada` dentro de `DB::transaction`.
   - *Devuelve:* Redirección `back` con mensaje flash. Invoca `notificarUsuario()`.

6. **`reprogramar(ReprogramarAsesoriaRequest $request, $id)`**
   - *Recibe:* ID de asesoría, nueva `fecha`, `hora_inicio`, `hora_fin`, `motivo`.
   - *Valida:* Reglas de `ReprogramarAsesoriaRequest` y no solapamiento del instructor.
   - *Toca:* Inserta en `asesoria_historial` (`accion = 'Reprogramo'`) y actualiza `asesorias` en `DB::transaction`.
   - *Devuelve:* Redirección `back` con mensaje flash.

7. **`cancelar(CancelarAsesoriaRequest $request, $id)`**
   - *Recibe:* ID de asesoría, `motivo`.
   - *Valida:* Reglas de `CancelarAsesoriaRequest`.
   - *Toca:* Inserta en `asesoria_historial` (`accion = 'Cancelo'`) y actualiza `asesorias` (`estado = 'Cancelada'`, `id_usuario_cancelador = Auth::id()`, `fecha_cancelacion = now()`).
   - *Devuelve:* Redirección `back` con mensaje flash.

8. **`storeSeguimiento(StoreSeguimientoRequest $request, $id)`**
   - *Recibe:* ID de asesoría, `avances`, `compromisos`, `observaciones`.
   - *Valida:* Reglas de `StoreSeguimientoRequest`.
   - *Toca:* Inserta en `asesoria_seguimientos` y actualiza `asesorias.estado = 'Realizada'` en `DB::transaction`.
   - *Devuelve:* Redirección `back` con mensaje flash.

9. **`corregirSeguimiento(CorregirSeguimientoRequest $request, $id)`**
   - *Recibe:* ID del seguimiento a corregir, `motivo_correccion`, `avances`, `compromisos`, `observaciones`.
   - *Valida:* Reglas de `CorregirSeguimientoRequest`.
   - *Toca:* Inserta un nuevo registro en `asesoria_seguimientos` con `id_asesoria_seguimiento_corrige = $id` en `DB::transaction`. No modifica el original.
   - *Devuelve:* Redirección `back` con mensaje flash.

10. **`agregarRecurso(AsignarRecursoRequest $request, $id)`**
    - *Recibe:* ID de asesoría, `id_inventario`, `cantidad`.
    - *Valida:* Reglas de `AsignarRecursoRequest` y `inventories.stock >= cantidad`.
    - *Toca:* Decrementa `inventories.stock` y `inventories.amount`; crea o incrementa en `asesoria_recursos` en `DB::transaction`.
    - *Devuelve:* Redirección `back` con mensaje flash.

11. **`consultarDisponibilidad($usuario)`**
    - *Recibe:* ID de usuario instructor.
    - *Valida:* Existencia de usuario.
    - *Toca:* Lee `disponibilidades_instructor` activas y vigentes.
    - *Devuelve:* JSON con lista de franjas horarias.

12. **`guardarDisponibilidad(Request $request)`**
    - *Recibe:* `id_usuario`, `anio`, `trimestre`, `dia_semana`, `hora_inicio`, `hora_fin`.
    - *Valida:* Campos requeridos y `hora_inicio < hora_fin`.
    - *Toca:* Actualiza franjas anteriores del mismo instructor, año, trimestre y día a `vigente = false`, e inserta la nueva franja (`vigente = true`) en `DB::transaction`.
    - *Devuelve:* Redirección `back` con mensaje flash.

13. **`storeCategoria(Request $request)`**
    - *Recibe:* `nombre`, `instructores` (array opcional).
    - *Valida:* Nombre requerido y único en `categorias_asesoria`.
    - *Toca:* Inserta en `categorias_asesoria` y sincroniza `instructor_categoria`.
    - *Devuelve:* Redirección `back` con mensaje flash.

14. **`updateCategoria(Request $request, $id)`**
    - *Recibe:* ID de categoría, `nombre`, `instructores`.
    - *Valida:* Nombre requerido y único excepto el ID actual.
    - *Toca:* Actualiza `categorias_asesoria` y resincroniza `instructor_categoria`.
    - *Devuelve:* Redirección `back` con mensaje flash.

15. **`toggleCategoriaEstado($id)`**
    - *Recibe:* ID de categoría.
    - *Valida:* Existencia.
    - *Toca:* Alterna `categorias_asesoria.estado` entre `'Activa'` e `'Inactiva'`.
    - *Devuelve:* Redirección `back` con mensaje flash.

16. **`reporte(Request $request)`**
    - *Recibe:* Filtros de reporte y parámetro `formato` (`'csv'` o `'print'`).
    - *Valida:* Formato válido.
    - *Toca:* Lee `asesorias` con relaciones.
    - *Devuelve:* StreamedResponse con BOM UTF-8 y delimitador `;` para Excel, o HTML imprimible que ejecuta `window.print()`.

---

## 5. Mapa de `asesoria.blade.php` por Bloques

| Bloque | Comentario de Búsqueda (Ctrl+F) | Línea Aprox. | Contenido Principal |
| :--- | :--- | :--- | :--- |
| **1. Estilos** | `{{-- 1. Estilos y Variables del Módulo --}}` | ~7 | Variables CSS `:root`, paleta verde/petróleo, tarjetas, badges, gráficas nativas. |
| **2. Encabezado** | `{{-- 2. Encabezado --}}` | ~215 | Título, subtítulo, botones de acción superior (Nueva solicitud, Disponibilidad, Categorías, Reporte). |
| **3. Indicadores** | `{{-- 3. Indicadores (5 tarjetas) --}}` | ~241 | 5 tarjetas resumen: Solicitadas, Programadas, Realizadas, Canceladas y Total. |
| **4. Filtros** | `{{-- 4. Barra de Filtros --}}` | ~320 | Buscador de texto, select de estados, select de categorías y rango de fechas con botón limpiar. |
| **5. Tabla** | `{{-- 5. Tabla de Asesorías --}}` | ~405 | Tabla responsiva con columnas ID, Aprendiz, Instructor, Categoría, Fecha/Hora, Estado y Acciones (Ver detalle, Asignar, Menú dropdown). |
| **6. Gráficas** | `{{-- 6. Gráficas (Día, Semana, Trimestre - CSS/HTML puro) --}}` | ~545 | 3 tarjetas visuales: barra diaria (últimos 7 días), comparativa semanal y progreso trimestral construidas con CSS puro. |
| **7. Categorías** | `{{-- 7. Panel Informativo de Categorías --}}` | ~630 | Rejilla informativa con badges de estado y conteo de instructores asociados. |
| **8. Modales** | `{{-- 8. Modales del Módulo --}}` | ~679 | Contenedor de los 11 modales de la interfaz. |
| **9. JavaScript** | `{{-- 9. JavaScript --}}` | ~1747 | Lógica cliente: toasts dinámicos, consumo AJAX, reanudación tras fallas de validación y utilidades. |

### Relación de Modales
1. **Ficha de Detalle:**  
   - `id`: `modalDetalle` (línea ~682).  
   - Función JS: `verDetalle(id)` -> consume `GET /{id}` y llena datos, historial, seguimientos y recursos.
2. **Nueva Solicitud:**  
   - `id`: `modalNuevaSolicitud` (línea ~885).  
   - `form`: `formNuevaSolicitud` -> envía `POST /control-ecp/administrador/asesorias`.  
   - Función JS: Se abre con botón superior; dispara `consultarInstructoresDisponibles('sol')`.
3. **Asignar y Programar:**  
   - `id`: `modalProgramar` (línea ~970).  
   - `form`: `formProgramar` -> envía `PUT /control-ecp/administrador/asesorias/{id}/programar`.  
   - Función JS: `abrirProgramar(id, aprendiz, catId, fecha, hInicio, hFin, instId, catNombre)`.
4. **Administrar Categorías:**  
   - `id`: `modalCategorias` (línea ~1045).  
   - `forms`: `formCrearCategoria` (`POST /categorias`) y `formEditarCategoria` (`PUT /categorias/{id}`).  
   - Función JS: `editarCategoria(id, nombre, instructoresIds)` y `cancelarEdicionCategoria()`.
5. **Disponibilidad Horaria:**  
   - `id`: `modalDisponibilidad` (línea ~1209).  
   - `form`: `formGuardarDisponibilidad` -> envía `POST /control-ecp/administrador/asesorias/disponibilidad`.  
   - Función JS: `cargarDisponibilidadesInstructor(usuarioId)`.
6. **Reprogramar Asesoría:**  
   - `id`: `modalReprogramar` (línea ~1324).  
   - `form`: `formReprogramar` -> envía `PUT /control-ecp/administrador/asesorias/{id}/reprogramar`.  
   - Función JS: `abrirReprogramar(id, fecha, hInicio, hFin)`.
7. **Cancelar Asesoría:**  
   - `id`: `modalCancelar` (línea ~1393).  
   - `form`: `formCancelar` -> envía `PUT /control-ecp/administrador/asesorias/{id}/cancelar`.  
   - Función JS: `abrirCancelar(id)`.
8. **Registrar Seguimiento:**  
   - `id`: `modalSeguimiento` (línea ~1451).  
   - `form`: `formSeguimiento` -> envía `POST /control-ecp/administrador/asesorias/{id}/seguimiento`.  
   - Función JS: `abrirSeguimiento(id)`.
9. **Corregir Seguimiento:**  
   - `id`: `modalCorregirSeguimiento` (línea ~1513).  
   - `form`: `formCorregirSeguimiento` -> envía `PUT /control-ecp/administrador/asesorias/seguimiento/{id}`.  
   - Función JS: `prepararCorreccionSeguimiento(idSeg)` / `abrirCorregirSeguimiento(idSeg, avances, comp, obs)`.
10. **Vincular Recursos:**  
    - `id`: `modalRecursos` (línea ~1586).  
    - `form`: `formRecursos` -> envía `POST /control-ecp/administrador/asesorias/{id}/recursos`.  
    - Función JS: `abrirRecursos(id)`.
11. **Generar Reporte:**  
    - `id`: `modalReporte` (línea ~1651).  
    - `form`: `formReporte` -> envía `GET /control-ecp/administrador/asesorias/reporte`.  
    - Función JS: Botones directos que conmutan `rep_formato` a `'csv'` o `'print'`.

---

## 6. Reglas de Negocio

1. **Validación de Cruces de Horario:**  
   - *Archivo:* `AsesoriaController.php` (métodos `store`, `programar`, `reprogramar`, `instructoresDisponibles`).  
   - *Lógica:* Se comprueba si existe alguna asesoría con el mismo instructor, misma fecha, estado diferente a `Cancelada` y solapamiento horario (`hora_inicio < nueva_hora_fin` y `hora_fin > nueva_hora_inicio`). Se excluye la asesoría evaluada (`id_asesoria != $id`).

2. **Búsqueda de Instructores Disponibles (AJAX):**  
   - *Archivo:* `AsesoriaController.php` (`instructoresDisponibles`) y `asesoria.blade.php` (`consultarInstructoresDisponibles`).  
   - *Lógica:* Filtra instructores que pertenezcan a la categoría (`instructor_categoria`), que cuenten con una franja habilitada y vigente (`disponibilidades_instructor`) para el año, trimestre y día de la semana (`date('N', strtotime($fecha))`) que abarque el horario, y que no tengan ninguna asesoría no cancelada en ese intervalo. Si no hay disponibilidad, se informa en pantalla y la solicitud se guarda como `Solicitada` sin instructor.

3. **Inactivación de Franjas Anteriores (`vigente = false`):**  
   - *Archivo:* `AsesoriaController.php` (`guardarDisponibilidad`).  
   - *Lógica:* Al guardar una nueva disponibilidad, todas las franjas previas del mismo instructor, año, trimestre y día de la semana pasan automáticamente a `vigente = false`. La nueva se guarda con `vigente = true`, preservando el histórico.

4. **Auditoría de Historial Inmutable:**  
   - *Archivo:* `AsesoriaController.php` (`reprogramar`, `cancelar`).  
   - *Lógica:* Cada reprogramación o cancelación genera un nuevo registro en `asesoria_historial` con `id_usuario_accion = Auth::id()`, motivo y fechas/horas previas y nuevas. Nunca se sobrescribe ni borra un registro histórico.

5. **Corrección de Seguimientos:**  
   - *Archivo:* `AsesoriaController.php` (`corregirSeguimiento`).  
   - *Lógica:* En vez de modificar el seguimiento original, se inserta un nuevo registro en `asesoria_seguimientos` que contiene el `motivo_correccion` y referencia al original mediante la llave foránea `id_asesoria_seguimiento_corrige`.

6. **Descuento Atómico de Inventario:**  
   - *Archivo:* `AsesoriaController.php` (`agregarRecurso`).  
   - *Lógica:* Ejecutado dentro de `DB::transaction` con `Inventory::lockForUpdate()`. Si `stock < cantidad`, se aborta con error. Si hay suficiente existencia, se restan `stock` y `amount` en `inventories`, y se registra en `asesoria_recursos`.

---

## 7. Variables CSS y Estilos

Definidas en el bloque `{{-- 1. Estilos y Variables del Módulo --}}` de `asesoria.blade.php` (~línea 8):

```css
:root {
    --verde: #39A900;           /* Acentos principales, botones primarios y estado Realizada */
    --verde-oscuro: #2e8500;    /* Hover de botones principales */
    --verde-claro: #e8f7e3;     /* Fondos de íconos y badges */
    --verde-suave: #f0fdf4;     /* Fondos secundarios suaves */
    --petroleo: #001A29;        /* Títulos principales, encabezados de modales y tabla */
    --petroleo-medio: #0a2e42;  /* Fondos oscuros de tarjetas y toasts */
    --petroleo-hover: #0f3b54;  /* Estados hover oscuros */
    --gris-fondo: #f4f6f9;      /* Fondo general de la interfaz */
    --gris-texto: #6c757d;      /* Textos secundarios y descripciones */
    --borde: #e2e8f0;           /* Bordes sutiles de tarjetas y paneles */
}
```

---

## 8. Cómo Hacer Cambios Comunes

### Agregar un campo a la tabla de asesorías
1. Crea una migración: `php artisan make:migration add_nuevo_campo_to_asesorias_table`.
2. Añade el campo al array `$fillable` en `Modules/ControlECP/Entities/Asesoria.php`.
3. Añade la regla en el FormRequest correspondiente (`StoreAsesoriaRequest.php` o `ProgramarAsesoriaRequest.php`).
4. Incluye el input HTML en el modal dentro de `asesoria.blade.php`.

### Agregar una columna a la tabla del listado
1. Ubica el bloque `{{-- 5. Tabla de Asesorías --}}` en `asesoria.blade.php` (~línea 405).
2. Agrega el encabezado `<th>` en `<thead>`.
3. Agrega la celda `<td>` correspondiente dentro del bucle `@forelse($asesorias as $item)`.

### Cambiar un color de la interfaz
1. Ubica el bloque `{{-- 1. Estilos y Variables del Módulo --}}` en `asesoria.blade.php` (~línea 8).
2. Cambia los códigos hexadecimales en las variables `:root` (ej. `--verde` o `--petroleo`).

### Agregar un nuevo filtro de búsqueda
1. Ubica el bloque `{{-- 4. Barra de Filtros --}}` en `asesoria.blade.php` (~línea 320) y agrega el `<input>` o `<select>`.
2. En `AsesoriaController::index()`, agrega la condición al `$query`:
   ```php
   if ($request->filled('mi_filtro')) {
       $query->where('columna', $request->mi_filtro);
   }
   ```

### Cambiar un texto o mensaje de validación
1. Abre el FormRequest respectivo en `Modules/ControlECP/Http/Requests/`.
2. Modifica el mensaje en el método `messages()`.

### Agregar un nuevo estado a la asesoría
1. Modifica la columna `estado` en la base de datos para aceptar el nuevo valor de `enum`.
2. En `asesoria.blade.php`, agrega la clase de estilo badge en el bloque CSS y el `<option>` en los filtros y reportes.
3. En `AsesoriaController.php`, añade la lógica de transición según los permisos requeridos.

---

## 9. Pendientes, Supuestos y Limitaciones

1. **Envío de Correos Electrónicos:**  
   - El método `notificarUsuario()` en `AsesoriaController` registra la notificación en el log y despliega mensajes flash. La integración con `Mail::to()` quedó marcada con `// TODO: Integrar Mailable con la plantilla institucional`.
2. **Reversión de Stock al Cancelar:**  
   - Si una asesoría con recursos asignados es cancelada, actualmente el sistema no restituye automáticamente las cantidades a `inventories.stock`. Debe devolverse manualmente o implementar la reversión en `AsesoriaController::cancelar()`.
3. **Cálculo de Trimestres:**  
   - Se calcula con base en el mes calendario estándar (`ceil(date('n') / 3)`). Si el centro de formación utiliza un calendario con trimestres desfasados, debe ajustarse el cálculo del trimestre en `AsesoriaController.php`.
4. **Zona Horaria del Servidor:**  
   - Las validaciones horarias dependen de `config('app.timezone')` (`America/Bogota`). Si el servidor estuviese configurado en UTC, el cálculo del día de la semana (`date('N')`) cambiaría a las 7:00 PM hora local.

---

## 10. Dónde Revisar si Algo Falla

Antes de cualquier diagnóstico, revisa el archivo de registro general: **`storage/logs/laravel.log`**.

| Error o Comportamiento | Archivo a Revisar | Método / Punto Clave |
| :--- | :--- | :--- |
| **El buscador AJAX no muestra instructores** | `AsesoriaController.php` | Método `instructoresDisponibles()`: verificar que el instructor tenga franja habilitada y vigente para el año, trimestre y día actual. |
| **Error al programar o reprogramar** | `AsesoriaController.php` | Métodos `programar()` y `reprogramar()`: revisar la cláusula de cruce de horario y transacciones fallidas. |
| **Error al asignar recursos de inventario** | `AsesoriaController.php` | Método `agregarRecurso()`: verificar si `inventories.stock` es menor a la cantidad solicitada. |
| **El modal se cierra al fallar validación** | `Modules/ControlECP/Http/Requests/*.php` | Verificar que el FormRequest invoque `session()->flash('modal_abrir', 'nombreModal')` en `failedValidation()`. |
| **Fallo en descarga de CSV o vista de impresión** | `AsesoriaController.php` | Método `reporte()`: verificar caracteres especiales o codificación UTF-8. |
