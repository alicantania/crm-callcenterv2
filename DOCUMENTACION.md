# Documentación de la Aplicación CRM CallCenter

## Descripción General
Aplicación CRM para gestión de call center con seguimiento de empresas, ventas y llamadas.

## Módulos Principales

### 1. Gestión de Empresas (`CompanyResource`)
- Campos principales: CIF, nombre, dirección, teléfono, email
- Estados posibles: Contactada, Seguimiento, Error
- Fechas importantes: último contacto, fecha de seguimiento

### 2. Gestión de Llamadas (`CallResource`)
- Registro de llamadas realizadas
- Resultados: Contacto, Volver a llamar, Error
- Fecha y hora de llamada

### 3. Gestión de Ventas (`SaleResource`)
- Registro de ventas
- Asociación a empresas y productos
- Estados de venta

### 4. Páginas del Operador
- `LlamadaManualPage`: Interfaz para realizar llamadas
- `MisContactosPage`: Listado de empresas asignadas
- `SeguimientoDeVentas`: Ventas en proceso
- `VentasPendientesDeTramitar`: Ventas por completar

### 5. Configuración
- Usuarios y roles
- Productos y líneas de negocio

## Flujos de Trabajo

### Flujo de Llamadas
1. Operador asigna empresa desde `MisContactosPage`
2. Realiza llamada en `LlamadaManualPage`
3. Registra resultado:
   - Contacto: Establece fecha de seguimiento (mañana)
   - Volver a llamar: Programa fecha específica
   - Error: Marca empresa como no contactable

### Flujo de Ventas
1. Creación de venta asociada a empresa
2. Seguimiento en `SeguimientoDeVentas`
3. Finalización en `VentasPendientesDeTramitar`

## Configuración de Fechas
Todas las fechas siguen el formato `dd-mm-yyyy HH:MM` en la interfaz, pero se almacenan en formato `yyyy-mm-dd HH:MM:SS` en la base de datos.

## Roles de Usuario
- Operador: Gestión de llamadas y seguimiento
- Gerente: Supervisión y reportes
- Superadmin: Configuración completa

## Documentación Técnica de la Aplicación CRM CallCenter

### 1. Módulo de Empresas (Company)

#### Modelo (Company.php)
- **Campos principales**:
  - Información básica (CIF, nombre, dirección, teléfono, email)
  - Datos de contacto (contact_person, contacts_count)
  - Fechas importantes (last_contact_at, follow_up_date)
  - Estado (status) con valores: Contactada, Seguimiento, Error

- **Relaciones**:
  - Operador asignado (belongsTo User)
  - Llamadas realizadas (hasMany Call)
  - Ventas (hasMany Sale)

#### Recurso Filament (CompanyResource.php)
- **Estructura**:
  - Formulario con pestañas para organizar la información
  - Tabla con columnas principales y filtros
  - Operaciones CRUD estándar

### 2. Módulo de Llamadas

#### Página Llamada Manual (LlamadaManualPage.php)
- **Funcionalidades**:
  - Registro de resultados de llamada
  - Programación de seguimientos
  - Gestión de estados de empresa
  - Solicitud de emails

- **Flujo**:
  1. Selección de empresa desde Mis Contactos
  2. Registro de llamada con resultado
  3. Actualización automática del estado y fechas

### 3. Páginas del Operador

#### Mis Contactos (MisContactosPage.php)
- **Filtros**:
  - Por estado de empresa
  - Por fecha de seguimiento
- **Acciones**:
  - Acceso rápido a Llamada Manual
  - Visualización de fechas clave

### 4. Configuración del Sistema

#### Formatos de Fecha
- **Almacenamiento**: yyyy-mm-dd HH:MM:SS (formato PostgreSQL)
- **Visualización**: dd-mm-yyyy HH:MM (formato español)
- **Conversión automática** mediante:
  - Casts en el modelo Company
  - Métodos format() de Carbon

#### Roles y Permisos
- **Operador**:
  - Gestión de sus contactos asignados
  - Registro de llamadas
  - Creación de ventas básicas

- **Administrador**:
  - Acceso completo
  - Gestión de usuarios
  - Reportes avanzados

## 5. Manejo de Fechas y Flujo de Llamadas

### Actualización de Fechas de Seguimiento
1. **Resultado 'Volver a llamar'**:
   - El operador selecciona fecha/hora específica
   - Se guarda en formato PostgreSQL (`Y-m-d H:i:s`)
   - Se muestra en formato español (`d-m-Y H:i`)
   - Ejemplo de código:
     ```php
     $fechaSeguimiento = Carbon::parse($data['fecha_rellamada'])->format('Y-m-d H:i:s');
     $empresa->update(['follow_up_date' => $fechaSeguimiento]);
     ```

2. **Resultado 'Contacto'**:
   - Fecha automática (mañana mismo horario)
   - Mismo formato de almacenamiento/visualización

### Validaciones
- Campo obligatorio para 'Volver a llamar'
- Formato consistente en toda la aplicación
- Sincronización entre:
  - LlamadaManualPage (registro)
  - MisContactosPage (visualización)
  - CompanyResource (gestión)

### Troubleshooting
**Problema**: Fecha no aparece después de actualizar
**Solución**:
1. Verificar que el campo exista en DB
2. Confirmar que el modelo tenga el cast a 'datetime'
3. Revisar formato de almacenamiento
4. Chequear actualización en tiempo real con `$empresa->follow_up_date = $fecha`

## 6. Pruebas y Verificación

### Pruebas de Flujo de Llamadas
1. **Registrar llamada con resultado 'Volver a llamar'**:
   - Seleccionar fecha futura
   - Verificar que:
     - Aparezca en Mis Contactos
     - Muestre notificación correcta
     - Estado cambie a 'Seguimiento'

2. **Registrar llamada con resultado 'Contacto'**:
   - Verificar que:
     - La fecha sea mañana mismo horario
     - Aparezca correctamente formateada

### Verificación de Base de Datos
- Consulta directa para ver formato de almacenamiento:
  ```sql
  SELECT id, name, follow_up_date FROM companies WHERE follow_up_date IS NOT NULL;
  ```

### Checklist de Implementación
- [ ] Campos de fecha en migraciones
- [ ] $fillable y $casts en modelo Company
- [ ] Formato correcto en:
  - LlamadaManualPage (guardado)
  - MisContactosPage (visualización)
  - CompanyResource (ambos)

## 7. Soporte Técnico
Para problemas con fechas:
1. Revisar logs de Laravel
2. Verificar migraciones aplicadas
3. Confirmar timezone en config/app.php
4. Chequear formato en las notificaciones
