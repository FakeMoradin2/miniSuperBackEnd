# Backend MiniSuper - Documentación API

## 📋 Descripción General

Backend REST API desarrollado en PHP para la gestión de un mini supermercado. El sistema permite gestionar usuarios, categorías, productos, proveedores, ventas y generar reportes. Utiliza MySQL como base de datos alojada en AWS RDS.

## 🏗️ Estructura del Proyecto

```
BackendMiniSuper/
├── api/
│   ├── auth/              # Autenticación de usuarios
│   ├── categoria/         # Gestión de categorías
│   ├── productos/         # Gestión de productos
│   ├── proveedores/       # Gestión de proveedores
│   ├── reportes/          # Reportes y estadísticas
│   └── ventas/            # Gestión de ventas y carrito
├── config/
│   ├── cors.php           # Configuración CORS
│   └── database.php       # Conexión a base de datos
└── index.php              # Endpoint de verificación
```

## ⚙️ Configuración

### Base de Datos

El archivo `config/database.php` contiene la configuración de conexión a AWS RDS:

```php
Host: database-2.cfic6ma02we7.us-east-2.rds.amazonaws.com
Database: minisuper
User: admin
Charset: utf8mb4
```

### CORS

El archivo `config/cors.php` configura los headers CORS para permitir peticiones desde cualquier origen:

- **Access-Control-Allow-Origin**: `*`
- **Access-Control-Allow-Methods**: `GET, POST, PUT, DELETE, OPTIONS`
- **Access-Control-Allow-Headers**: `Content-Type, Authorization`

## 🔐 Autenticación

### POST `/api/auth/register.php`

Registra un nuevo usuario en el sistema.

**Parámetros (JSON Body):**
- `nombre_usuario` (string, requerido): Nombre de usuario único
- `password` (string, requerido): Contraseña (se encripta con bcrypt)
- `telefono` (string, requerido): Número de teléfono
- `rol` (string, requerido): Rol del usuario (cliente, cajero, admin)

**Query SQL:**
```sql
INSERT INTO usuario (nombre_usuario, password, telefono, rol) 
VALUES (?, ?, ?, ?)
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Usuario registrado correctamente"
}
```

**Respuesta Error:**
```json
{
  "success": false,
  "message": "El nombre de usuario ya existe"
}
```

---

### POST `/api/auth/login.php`

Inicia sesión de un usuario existente.

**Parámetros (JSON Body):**
- `nombre_usuario` (string, requerido): Nombre de usuario
- `password` (string, requerido): Contraseña sin encriptar

**Query SQL:**
```sql
SELECT * FROM usuario WHERE nombre_usuario = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "usuario": {
    "id": 1,
    "nombre_usuario": "admin",
    "rol": "admin"
  },
  "token": "a1b2c3d4e5f6..."
}
```

**Respuesta Error:**
```json
{
  "success": false,
  "message": "Usuario no encontrado" | "Contraseña incorrecta"
}
```

## 📁 Categorías

### GET `/api/categoria/listar.php`

Obtiene todas las categorías activas e inactivas.

**Parámetros:** Ninguno

**Query SQL:**
```sql
SELECT Id_categoria, Nombre_Categoria, activo 
FROM categoria
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total": 5,
  "data": [
    {
      "Id_categoria": 1,
      "Nombre_Categoria": "Bebidas",
      "activo": true
    }
  ]
}
```

---

### POST `/api/categoria/agregar.php`

Crea una nueva categoría.

**Parámetros (JSON Body):**
- `Nombre_Categoria` (string, requerido): Nombre de la categoría

**Query SQL:**
```sql
INSERT INTO categoria (Nombre_Categoria, activo) 
VALUES (?, TRUE)
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Categoría agregada correctamente"
}
```

---

### PUT `/api/categoria/editar.php`

Actualiza una categoría existente.

**Parámetros (JSON Body):**
- `Id_categoria` (int, requerido): ID de la categoría a editar
- `Nombre_Categoria` (string, opcional): Nuevo nombre
- `activo` (boolean, opcional): Estado activo/inactivo

**Query SQL (dinámico):**
```sql
UPDATE categoria 
SET Nombre_Categoria = ?, activo = ? 
WHERE Id_categoria = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Categoría actualizada correctamente"
}
```

---

### DELETE `/api/categoria/eliminar.php`

Desactiva una categoría (soft delete).

**Parámetros (JSON Body):**
- `Id_categoria` (int, requerido): ID de la categoría a desactivar

**Query SQL:**
```sql
UPDATE categoria SET activo = FALSE 
WHERE Id_categoria = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Categoría desactivada correctamente"
}
```

## 🛍️ Productos

### GET `/api/productos/listar.php`

Obtiene todos los productos activos con información de categoría y proveedor.

**Parámetros (Query String - Opcional):**
- `categoria` (int, opcional): Filtrar por ID de categoría

**Query SQL (base):**
```sql
SELECT 
    p.producto_id,
    p.nombre_producto,
    p.precio,
    p.stock,
    p.activo_producto,
    p.image_url,
    c.Nombre_Categoria AS categoria,
    pr.nombre_proveedor AS proveedor
FROM producto p
LEFT JOIN categoria c ON p.categoria_id = c.Id_categoria
LEFT JOIN proveedor pr ON p.proveedor_id = pr.Id_proveedor
WHERE p.activo_producto = TRUE
```

**Query SQL (con filtro):**
```sql
-- Si se pasa ?categoria=3
... AND p.categoria_id = :categoria
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total": 10,
  "data": [
    {
      "producto_id": 1,
      "nombre_producto": "Coca Cola 2L",
      "precio": 25.50,
      "stock": 50,
      "activo_producto": true,
      "image_url": "https://...",
      "categoria": "Bebidas",
      "proveedor": "Coca Cola Company"
    }
  ]
}
```

---

### POST `/api/productos/agregar.php`

Crea un nuevo producto.

**Parámetros (JSON Body):**
- `nombre_producto` (string, requerido): Nombre del producto
- `precio` (decimal, requerido): Precio unitario
- `stock` (int, requerido): Cantidad en inventario
- `categoria_id` (int, requerido): ID de la categoría
- `proveedor_id` (int, requerido): ID del proveedor
- `image_url` (string, requerido): URL de la imagen

**Query SQL:**
```sql
INSERT INTO producto (nombre_producto, precio, stock, categoria_id, proveedor_id, activo_producto, image_url)
VALUES (?, ?, ?, ?, ?, TRUE, ?)
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Producto agregado correctamente"
}
```

---

### PUT `/api/productos/editar.php`

Actualiza un producto existente.

**Parámetros (JSON Body):**
- `producto_id` (int, requerido): ID del producto a editar
- `nombre_producto` (string, opcional): Nuevo nombre
- `precio` (decimal, opcional): Nuevo precio
- `stock` (int, opcional): Nueva cantidad en stock
- `categoria_id` (int, opcional): Nueva categoría
- `proveedor_id` (int, opcional): Nuevo proveedor
- `image_url` (string, opcional): Nueva URL de imagen
- `activo_producto` (boolean, opcional): Estado activo/inactivo

**Query SQL (dinámico):**
```sql
UPDATE producto 
SET nombre_producto = ?, precio = ?, stock = ?, ... 
WHERE producto_id = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Producto actualizado correctamente"
}
```

---

### DELETE `/api/productos/eliminar.php`

Desactiva un producto (soft delete).

**Parámetros (JSON Body):**
- `producto_id` (int, requerido): ID del producto a desactivar

**Query SQL:**
```sql
UPDATE producto SET activo_producto = FALSE 
WHERE producto_id = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Producto desactivado correctamente"
}
```

## 🏢 Proveedores

### GET `/api/proveedores/listar.php`

Obtiene todos los proveedores.

**Parámetros:** Ninguno

**Query SQL:**
```sql
SELECT Id_proveedor, nombre_proveedor, telefono_proveedor, correo_proveedor, activo_proveedor 
FROM proveedor
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total": 3,
  "data": [
    {
      "Id_proveedor": 1,
      "nombre_proveedor": "Coca Cola Company",
      "telefono_proveedor": "555-1234",
      "correo_proveedor": "contacto@cocacola.com",
      "activo_proveedor": true
    }
  ]
}
```

---

### POST `/api/proveedores/agregar.php`

Crea un nuevo proveedor.

**Parámetros (JSON Body):**
- `nombre_proveedor` (string, requerido): Nombre del proveedor
- `telefono_proveedor` (string, opcional): Teléfono de contacto
- `correo_proveedor` (string, opcional): Correo electrónico

**Query SQL:**
```sql
INSERT INTO proveedor (nombre_proveedor, telefono_proveedor, correo_proveedor, activo_proveedor) 
VALUES (?, ?, ?, TRUE)
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Proveedor agregado correctamente"
}
```

---

### PUT `/api/proveedores/editar.php`

Actualiza un proveedor existente.

**Parámetros (JSON Body):**
- `Id_proveedor` (int, requerido): ID del proveedor a editar
- `nombre_proveedor` (string, opcional): Nuevo nombre
- `telefono_proveedor` (string, opcional): Nuevo teléfono
- `correo_proveedor` (string, opcional): Nuevo correo
- `activo_proveedor` (boolean, opcional): Estado activo/inactivo

**Query SQL (dinámico):**
```sql
UPDATE proveedor 
SET nombre_proveedor = ?, telefono_proveedor = ?, ... 
WHERE Id_proveedor = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Proveedor actualizado correctamente"
}
```

---

### DELETE `/api/proveedores/eliminar.php`

Desactiva un proveedor (soft delete).

**Parámetros (JSON Body):**
- `Id_proveedor` (int, requerido): ID del proveedor a desactivar

**Query SQL:**
```sql
UPDATE proveedor SET activo_proveedor = FALSE 
WHERE Id_proveedor = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Proveedor desactivado correctamente"
}
```

## 🛒 Ventas

### POST `/api/ventas/crear.php`

Crea un nuevo carrito de compras.

**Parámetros (JSON Body):**
- `comprador_id` (int, requerido): ID del usuario comprador
- `vendedor_id` (int, requerido): ID del usuario vendedor/cajero

**Query SQL:**
```sql
INSERT INTO venta (comprador_id, vendedor_id, estado_venta, total)
VALUES (?, ?, 'carrito', 0)
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Carrito creado correctamente",
  "venta_id": 123
}
```

---

### POST `/api/ventas/agregarProducto.php`

Agrega un producto al carrito.

**Parámetros (JSON Body):**
- `venta_id` (int, requerido): ID de la venta/carrito
- `producto_id` (int, requerido): ID del producto a agregar
- `cantidad` (int, requerido): Cantidad a agregar

**Query SQL (validación):**
```sql
SELECT precio, stock FROM producto 
WHERE producto_id = ? AND activo_producto = TRUE
```

**Query SQL (inserción):**
```sql
INSERT INTO ticket (venta_id, producto_id, cantidad, precio_unitario)
VALUES (?, ?, ?, ?)
```

**Validaciones:**
- Verifica que el producto exista y esté activo
- Verifica que haya stock suficiente
- Calcula el subtotal automáticamente

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Producto agregado al carrito correctamente",
  "subtotal": 127.50
}
```

**Respuesta Error:**
```json
{
  "success": false,
  "message": "Stock insuficiente" | "Producto no encontrado o inactivo"
}
```

---

### PUT `/api/ventas/actualizarProducto.php`

Actualiza la cantidad de un producto en el carrito.

**Parámetros (JSON Body):**
- `venta_id` (int, requerido): ID de la venta/carrito
- `producto_id` (int, requerido): ID del producto
- `cantidad` (int, requerido): Nueva cantidad (si es 0 o menor, se elimina)

**Query SQL (validación):**
```sql
SELECT estado_venta FROM venta WHERE id_venta = ?
SELECT cantidad, precio_unitario FROM ticket 
WHERE venta_id = ? AND producto_id = ?
```

**Query SQL (actualización):**
```sql
UPDATE ticket 
SET cantidad = ?, subtotal = ? 
WHERE venta_id = ? AND producto_id = ?
```

**Query SQL (eliminación si cantidad <= 0):**
```sql
DELETE FROM ticket 
WHERE venta_id = ? AND producto_id = ?
```

**Validaciones:**
- Verifica que la venta esté en estado 'carrito'
- Verifica que el producto exista en el carrito
- Si la cantidad es 0 o menor, elimina el producto

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Cantidad actualizada correctamente",
  "nueva_cantidad": 3,
  "nuevo_subtotal": 76.50
}
```

---

### DELETE `/api/ventas/eliminarProducto.php`

Elimina un producto del carrito.

**Parámetros (JSON Body):**
- `venta_id` (int, requerido): ID de la venta/carrito
- `producto_id` (int, requerido): ID del producto a eliminar

**Query SQL (validación):**
```sql
SELECT estado_venta FROM venta WHERE id_venta = ?
```

**Query SQL (eliminación):**
```sql
DELETE FROM ticket 
WHERE venta_id = ? AND producto_id = ?
```

**Validaciones:**
- Verifica que la venta esté en estado 'carrito'

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Producto eliminado del carrito"
}
```

---

### POST `/api/ventas/confirmar.php`

Confirma y completa una venta (cambia de 'carrito' a 'completada').

**Parámetros (JSON Body):**
- `venta_id` (int, requerido): ID de la venta a confirmar

**Query SQL (cálculo de total):**
```sql
SELECT SUM(subtotal) AS total 
FROM ticket 
WHERE venta_id = ?
```

**Query SQL (actualización):**
```sql
UPDATE venta
SET estado_venta = 'completada', total = ?, creada_en_venta = NOW()
WHERE id_venta = ?
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Venta confirmada correctamente",
  "total": 450.75
}
```

---

### POST `/api/ventas/cancelar.php`

Cancela un carrito de compras.

**Parámetros (JSON Body):**
- `venta_id` (int, requerido): ID de la venta a cancelar

**Query SQL (validación):**
```sql
SELECT * FROM venta 
WHERE id_venta = ? AND estado_venta = 'carrito'
```

**Query SQL (cancelación):**
```sql
UPDATE venta SET estado_venta = 'cancelada' 
WHERE id_venta = ?
```

**Validaciones:**
- Verifica que la venta exista y esté en estado 'carrito'

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Carrito cancelado correctamente"
}
```

## 📊 Reportes

### GET `/api/reportes/historial.php`

Obtiene el historial de ventas con filtros opcionales.

**Parámetros (Query String - Opcionales):**
- `usuario_id` (int, opcional): ID del usuario
- `rol` (string, opcional): 'cliente' o 'cajero'

**Query SQL (base):**
```sql
SELECT 
    v.id_venta,
    v.estado_venta,
    v.total,
    v.creada_en_venta,
    u1.nombre_usuario AS comprador,
    u2.nombre_usuario AS vendedor
FROM venta v
LEFT JOIN usuario u1 ON v.comprador_id = u1.usuario_id
LEFT JOIN usuario u2 ON v.vendedor_id = u2.usuario_id
WHERE 1=1
```

**Query SQL (con filtros):**
```sql
-- Si rol='cliente' y usuario_id existe:
AND v.comprador_id = :usuario_id

-- Si rol='cajero' y usuario_id existe:
AND v.vendedor_id = :usuario_id
```

**Ordenamiento:**
```sql
ORDER BY v.creada_en_venta DESC
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total": 25,
  "data": [
    {
      "id_venta": 100,
      "estado_venta": "completada",
      "total": 450.75,
      "creada_en_venta": "2024-01-15 14:30:00",
      "comprador": "Juan Pérez",
      "vendedor": "María García"
    }
  ]
}
```

---

### GET `/api/reportes/productosTop.php`

Obtiene los top 10 productos más vendidos.

**Parámetros (Query String - Opcionales):**
- `inicio` (date, opcional): Fecha de inicio (formato: YYYY-MM-DD)
- `fin` (date, opcional): Fecha de fin (formato: YYYY-MM-DD)

**Query SQL (base):**
```sql
SELECT 
    p.producto_id,
    p.nombre_producto,
    c.Nombre_Categoria AS categoria,
    SUM(t.cantidad) AS total_vendido,
    SUM(t.subtotal) AS ingresos_generados
FROM ticket t
INNER JOIN producto p ON p.producto_id = t.producto_id
INNER JOIN venta v ON v.id_venta = t.venta_id
LEFT JOIN categoria c ON p.categoria_id = c.Id_categoria
WHERE v.estado_venta = 'completada'
```

**Query SQL (con filtros de fecha):**
```sql
-- Si inicio y fin existen:
AND DATE(v.creada_en_venta) BETWEEN :inicio AND :fin

-- Si solo inicio existe:
AND DATE(v.creada_en_venta) = :inicio
```

**Agrupación y ordenamiento:**
```sql
GROUP BY p.producto_id, p.nombre_producto, c.Nombre_Categoria
ORDER BY total_vendido DESC
LIMIT 10
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total": 10,
  "data": [
    {
      "producto_id": 5,
      "nombre_producto": "Coca Cola 2L",
      "categoria": "Bebidas",
      "total_vendido": 150,
      "ingresos_generados": 3825.00
    }
  ]
}
```

---

### GET `/api/reportes/reporteDia.php`

Obtiene reportes diarios de ventas con estadísticas.

**Parámetros (Query String - Opcionales):**
- `inicio` (date, opcional): Fecha de inicio (formato: YYYY-MM-DD)
- `fin` (date, opcional): Fecha de fin (formato: YYYY-MM-DD)

**Query SQL (base):**
```sql
SELECT 
    DATE(v.creada_en_venta) AS fecha,
    COUNT(*) AS total_ventas,
    SUM(v.total) AS monto_total,
    AVG(v.total) AS promedio_venta
FROM venta v
WHERE v.estado_venta = 'completada'
```

**Query SQL (con filtros de fecha):**
```sql
-- Si inicio y fin existen:
AND DATE(v.creada_en_venta) BETWEEN :inicio AND :fin

-- Si solo inicio existe:
AND DATE(v.creada_en_venta) = :inicio
```

**Agrupación y ordenamiento:**
```sql
GROUP BY DATE(v.creada_en_venta)
ORDER BY fecha DESC
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "total_dias": 7,
  "data": [
    {
      "fecha": "2024-01-15",
      "total_ventas": 25,
      "monto_total": 11250.75,
      "promedio_venta": 450.03
    }
  ]
}
```

## 🔍 Endpoint de Verificación

### GET `/index.php`

Verifica que el servidor esté conectado correctamente a la base de datos.

**Parámetros:** Ninguno

**Respuesta:**
```json
{
  "status": "ok",
  "message": "Servidor PHP conectado a AWS RDS correctamente"
}
```

## 📝 Notas Importantes

### Seguridad
- Las contraseñas se encriptan usando `password_hash()` con algoritmo bcrypt
- Las contraseñas se verifican con `password_verify()`
- Se utilizan prepared statements para prevenir SQL injection
- CORS está configurado para permitir peticiones desde cualquier origen (ajustar en producción)

### Soft Delete
- Las operaciones de eliminación no borran registros físicamente
- Se marcan como inactivos usando campos booleanos (`activo`, `activo_producto`, `activo_proveedor`)
- Los productos y categorías inactivos no aparecen en las listas principales

### Estados de Venta
- **carrito**: Venta en proceso, se puede modificar
- **completada**: Venta finalizada y confirmada
- **cancelada**: Venta cancelada

### Formato de Respuestas
Todas las respuestas siguen el formato:
```json
{
  "success": true|false,
  "message": "Mensaje descriptivo",
  "data": {...}  // Solo en respuestas exitosas con datos
}
```

### Manejo de Errores
- Errores de validación: `success: false` con `message` descriptivo
- Errores de base de datos: `success: false` con `error` conteniendo el mensaje de PDOException
- Códigos HTTP: 200 para éxito, 500 para errores de servidor

## 🚀 Ejemplos de Uso

### Ejemplo: Crear una venta completa

```bash
# 1. Crear carrito
curl -X POST http://tu-servidor/api/ventas/crear.php \
  -H "Content-Type: application/json" \
  -d '{
    "comprador_id": 1,
    "vendedor_id": 2
  }'

# Respuesta: {"success": true, "venta_id": 123}

# 2. Agregar productos
curl -X POST http://tu-servidor/api/ventas/agregarProducto.php \
  -H "Content-Type: application/json" \
  -d '{
    "venta_id": 123,
    "producto_id": 5,
    "cantidad": 2
  }'

# 3. Confirmar venta
curl -X POST http://tu-servidor/api/ventas/confirmar.php \
  -H "Content-Type: application/json" \
  -d '{
    "venta_id": 123
  }'
```

### Ejemplo: Obtener productos por categoría

```bash
curl "http://tu-servidor/api/productos/listar.php?categoria=3"
```

### Ejemplo: Obtener reporte de productos top del mes

```bash
curl "http://tu-servidor/api/reportes/productosTop.php?inicio=2024-01-01&fin=2024-01-31"
```

## 📚 Tecnologías Utilizadas

- **PHP 7.4+**: Lenguaje de programación del backend
- **MySQL**: Base de datos relacional
- **PDO**: Interfaz de acceso a datos
- **AWS RDS**: Servicio de base de datos en la nube
- **JSON**: Formato de intercambio de datos

## 🔧 Requisitos del Sistema

- PHP 7.4 o superior
- Extensión PDO habilitada
- Extensión PDO_MySQL habilitada
- Acceso a internet para conectar con AWS RDS
- Servidor web (Apache/Nginx) configurado

---

**Desarrollado para BackendMiniSuper** 🛒

