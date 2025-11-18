# Flujo del Sistema de Facturación Electrónica

Este documento describe el flujo completo que sigue el sistema desde la creación de un documento hasta su envío a SUNAT.

## 📋 Flujo General del Sistema

### 1. **Configuración Inicial (Setup)**

#### 1.1. Creación de Empresa

- **Ruta**: `/companies/create`
- **Controlador**: `CompanyController@create`
- **Proceso**:
    - Usuario crea una empresa con sus datos básicos
    - Se registran las credenciales SOL (Usuario SOL y Contraseña SOL)
    - Se almacena el certificado digital (.pem) y su contraseña
    - La empresa queda asociada al usuario creador

#### 1.2. Asociación de Usuario a Empresa

- Cada usuario debe tener un `company_id` asignado
- Sin empresa asociada, el usuario no puede:
    - Ver clientes, productos o documentos
    - Crear nuevos registros
    - Acceder al dashboard completo

---

### 2. **Gestión de Maestros (Datos Base)**

#### 2.1. Clientes (`/customers`)

- **Flujo**:
    1. Usuario accede a `/customers`
    2. Sistema verifica que tenga `company_id`
    3. Muestra lista de clientes filtrados por empresa
    4. Puede crear, editar, ver o eliminar clientes
    5. Cada cliente tiene: DNI/RUC, nombre, dirección, email, teléfono

#### 2.2. Productos (`/products`)

- **Flujo**:
    1. Usuario accede a `/products`
    2. Sistema verifica que tenga `company_id`
    3. Muestra productos filtrados por empresa
    4. Puede crear productos con:
        - Categoría, unidad de medida, precio de venta/compra
        - Tipo de impuesto (Gravado, Exonerado, Inafecto, Exportación)
        - Estado activo/inactivo

#### 2.3. Categorías y Catálogos

- El sistema usa catálogos SUNAT oficiales:
    - `catalog_document_types`: Tipos de documento (01=Factura, 03=Boleta, etc.)
    - `catalog_units`: Unidades de medida (NIU, KG, ZZ, etc.)
    - `catalog_tax_types`: Tipos de impuesto (10=Gravado, 20=Exonerado, etc.)

---

### 3. **Creación de Documentos Electrónicos**

#### 3.1. Formulario de Creación (`/documents/create`)

- **Controlador**: `DocumentController@create`
- **Datos requeridos**:
    - Cliente (debe existir en la empresa)
    - Tipo de documento (Factura, Boleta, Nota de Crédito, Nota de Débito)
    - Serie y número
    - Fecha de emisión
    - Moneda
    - Items (productos/servicios):
        - Descripción
        - Cantidad
        - Precio unitario
        - Tipo de impuesto
        - IGV calculado

#### 3.2. Almacenamiento (`DocumentController@store`)

- **Proceso**:
    1. Validación de datos mediante `StoreDocumentRequest`
    2. Asignación automática de `company_id` del usuario autenticado
    3. Creación del documento con estado `PENDING`
    4. Creación de items asociados (`DocumentItem`)
    5. Cálculo automático de totales (total_taxed, total_igv, total)
    6. Redirección a vista de detalle del documento

#### 3.3. Estados del Documento

- `PENDING`: Recién creado, puede editarse o eliminarse
- `SENT`: Enviado a SUNAT, procesándose
- `ACCEPTED`: Aceptado por SUNAT (con CDR)
- `REJECTED`: Rechazado por SUNAT
- `CANCELED`: Anulado manualmente

---

### 4. **Procesamiento y Envío a SUNAT**

#### 4.1. Inicio del Envío (`/documents/{id}/send-to-sunat`)

- **Controlador**: `DocumentController@sendToSunat`
- **Validaciones**:
    - Usuario tiene permiso (`DocumentPolicy@sendToSunat`)
    - Documento está en estado `PENDING`
- **Acción**: Despacha job `SendDocumentToSunat` a la cola

#### 4.2. Job Asíncrono (`SendDocumentToSunat`)

- **Ubicación**: `app/Jobs/SendDocumentToSunat.php`
- **Proceso**:
    1. Cambia estado del documento a `SENT`
    2. Llama a `SunatApiService@send()`
    3. Si hay error, vuelve el estado a `PENDING`
    4. Registra logs de éxito/error

#### 4.3. Generación de XML (`XmlGeneratorService`)

- **Ubicación**: `app/Services/Sunat/XmlGeneratorService.php`
- **Proceso** (TODO - Pendiente de implementación completa):
    1. Genera XML según formato UBL 2.1 de SUNAT
    2. Incluye datos del emisor (empresa)
    3. Incluye datos del cliente
    4. Incluye items con impuestos
    5. Genera hash SHA-256 del XML
    6. Firma XML con certificado digital de la empresa
    7. Almacena XML original y XML firmado en el documento

#### 4.4. Envío a SUNAT (`SunatApiService`)

- **Ubicación**: `app/Services/Sunat/SunatApiService.php`
- **Proceso** (TODO - Pendiente de implementación completa):
    1. Construye petición SOAP a SUNAT
    2. Usa credenciales SOL (Usuario SOL y Contraseña SOL)
    3. Envía XML firmado codificado en Base64
    4. Espera respuesta de SUNAT
    5. Procesa respuesta SOAP
    6. Extrae CDR (Constancia de Recepción)
    7. Almacena respuesta en `SunatResponse`

#### 4.5. Procesamiento de Respuesta

- **Modelo**: `SunatResponse`
- **Datos almacenados**:
    - `cdr_zip`: Archivo ZIP con el CDR
    - `cdr_xml`: XML del CDR extraído
    - `sunat_code`: Código de respuesta SUNAT
    - `sunat_message`: Mensaje de SUNAT
- **Actualización de estado**:
    - Si código es éxito → `ACCEPTED`
    - Si código es error → `REJECTED`

---

### 5. **Visualización y Consulta**

#### 5.1. Dashboard (`/dashboard`)

- **Controlador**: `DashboardController@__invoke`
- **Muestra**:
    - Estadísticas generales:
        - Total de clientes
        - Total de productos activos
        - Total de documentos
        - Documentos pendientes/aceptados/rechazados
        - Total de ventas (documentos aceptados)
    - Últimos 10 documentos emitidos

#### 5.2. Lista de Documentos (`/documents`)

- **Filtros disponibles**:
    - Búsqueda por serie/número
    - Filtro por tipo de documento
    - Filtro por estado
- **Paginación**: 15 documentos por página

#### 5.3. Detalle de Documento (`/documents/{id}`)

- **Información mostrada**:
    - Datos del documento (serie, número, fecha, estado)
    - Datos del cliente
    - Items del documento
    - Totales e impuestos
    - Respuesta de SUNAT (si existe)
    - Botón para enviar a SUNAT (si está pendiente)

---

## 🔐 Seguridad y Autorización

### Autenticación

- **Laravel Fortify**: Maneja login, registro, recuperación de contraseña
- **2FA**: Autenticación de dos factores habilitada
- **Middleware**: `auth` y `verified` en todas las rutas protegidas

### Autorización

- **Spatie Laravel Permission**: Roles y permisos
- **Policies**:
    - `DocumentPolicy`: Controla quién puede enviar documentos a SUNAT
    - `CompanyPolicy`, `CustomerPolicy`, `ProductPolicy`: Controlan acceso a recursos

### Multi-tenancy

- Todos los recursos están filtrados por `company_id`
- Usuarios solo ven datos de su empresa
- No hay fuga de datos entre empresas

---

## 📊 Flujo de Datos Completo

```
Usuario → Dashboard
    ↓
Crear/Elegir Empresa → Asociar empresa al usuario
    ↓
Gestionar Clientes → Crear clientes con DNI/RUC
    ↓
Gestionar Productos → Crear productos con precios e impuestos
    ↓
Crear Documento → Seleccionar cliente, tipo, agregar items
    ↓
Documento en estado PENDING → Puede editarse o eliminarse
    ↓
Enviar a SUNAT → Job asíncrono se ejecuta
    ↓
Generar XML → Formato UBL 2.1
    ↓
Firmar XML → Certificado digital de la empresa
    ↓
Enviar a SUNAT → Petición SOAP con credenciales SOL
    ↓
Procesar Respuesta → Extraer CDR
    ↓
Actualizar Estado → ACCEPTED o REJECTED
    ↓
Almacenar CDR → En tabla sunat_responses
```

---

## ⚠️ Pendientes de Implementación (TODOs)

1. **Generación de XML UBL 2.1 completo**
    - Actualmente es un placeholder
    - Necesita implementar toda la estructura según especificación SUNAT

2. **Firma Digital del XML**
    - Integración con OpenSSL
    - Uso de biblioteca XMLSecLibs

3. **Comunicación SOAP con SUNAT**
    - Construcción correcta del envelope SOAP
    - Manejo de respuestas SOAP

4. **Extracción y procesamiento del CDR**
    - Descomprimir ZIP del CDR
    - Parsear XML del CDR
    - Actualizar estado del documento según respuesta

5. **Validaciones SUNAT**
    - Validar formato antes de enviar
    - Validar números de serie/número únicos
    - Validar RUC/DNI del cliente

---

## 🎯 Resumen del Flujo Principal

1. **Setup**: Usuario crea empresa y se asocia
2. **Maestros**: Crea clientes y productos
3. **Documento**: Crea factura/boleta con items
4. **Envío**: Presiona botón "Enviar a SUNAT"
5. **Procesamiento**: Job genera XML, firma y envía
6. **Respuesta**: Sistema procesa CDR y actualiza estado
7. **Consulta**: Usuario puede ver estado y descargar CDR

El sistema está diseñado para ser **asíncrono**, **escalable** y **seguro**, con separación clara de responsabilidades entre controladores, servicios y jobs.
