# Ejemplo de Datos de una Empresa

Este documento muestra cómo se ven los datos de una empresa en el sistema, tanto en la base de datos como en la interfaz de usuario.

## 📊 Estructura de Datos de una Empresa

### Modelo `Company` (Base de Datos)

Una empresa en el sistema contiene la siguiente información:

```php
{
    "id": 1,
    "ruc": "46464564646",
    "business_name": "vidrioS.A.C",
    "trade_name": "carlitos",
    "certificate": null,                    // Certificado digital (.pem)
    "certificate_password": null,           // Contraseña del certificado (oculto)
    "user_sol": "USUARIO SOL",              // Usuario SOL para SUNAT
    "password_sol": "246246",               // Contraseña SOL (oculto)
    "address": "av.sol",
    "ubigeo": "152011",                     // Código de ubicación geográfica SUNAT
    "created_at": "2025-11-14 17:48:06",
    "updated_at": "2025-11-14 17:48:06"
}
```

### Campos Explicados

| Campo                  | Tipo         | Descripción                          | Ejemplo                          |
| ---------------------- | ------------ | ------------------------------------ | -------------------------------- |
| `id`                   | BIGINT       | Identificador único                  | `1`                              |
| `ruc`                  | CHAR(11)     | RUC de la empresa (11 dígitos)       | `46464564646`                    |
| `business_name`        | VARCHAR(255) | Razón social oficial                 | `vidrioS.A.C`                    |
| `trade_name`           | VARCHAR(255) | Nombre comercial                     | `carlitos`                       |
| `certificate`          | TEXT         | Certificado digital en formato PEM   | `-----BEGIN CERTIFICATE-----...` |
| `certificate_password` | VARCHAR(255) | Contraseña del certificado (oculto)  | `****`                           |
| `user_sol`             | VARCHAR(50)  | Usuario SOL para autenticación SUNAT | `USUARIO SOL`                    |
| `password_sol`         | VARCHAR(50)  | Contraseña SOL (oculto)              | `****`                           |
| `address`              | VARCHAR(255) | Dirección fiscal                     | `av.sol`                         |
| `ubigeo`               | CHAR(6)      | Código de ubicación geográfica SUNAT | `152011`                         |

### Campos Ocultos por Seguridad

Los siguientes campos **NO** se muestran en las respuestas JSON por seguridad:

- `certificate_password`: Contraseña del certificado digital
- `password_sol`: Contraseña del usuario SOL

Estos campos están definidos en el modelo como `$hidden`.

---

## 🖥️ Vista en la Interfaz de Usuario

### Página de Detalle (`/companies/{id}`)

La vista muestra la información de la empresa organizada en secciones:

#### 1. **Información General** (Columna Izquierda)

```
┌─────────────────────────────────────┐
│ Información General                 │
├─────────────────────────────────────┤
│ RUC                                 │
│ 46464564646                         │
│                                     │
│ Razón Social                        │
│ vidrioS.A.C                         │
│                                     │
│ Nombre Comercial                    │
│ carlitos                            │
│                                     │
│ Dirección                           │
│ av.sol                              │
│                                     │
│ Ubigeo                              │
│ 152011                              │
└─────────────────────────────────────┘
```

#### 2. **Estadísticas** (Columna Derecha)

```
┌─────────────────────────────────────┐
│ Estadísticas                        │
├─────────────────────────────────────┤
│ Usuarios                            │
│ 1                                   │
│                                     │
│ Clientes                           │
│ 5                                   │
│                                     │
│ Productos                           │
│ 12                                  │
│                                     │
│ Documentos                          │
│ 23                                  │
└─────────────────────────────────────┘
```

#### 3. **Información del Sistema** (Sección Inferior)

```
┌─────────────────────────────────────┐
│ Información del Sistema              │
├─────────────────────────────────────┤
│ Fecha de Creación                   │
│ 14/11/2025, 17:48:06                │
│                                     │
│ Última Actualización                │
│ 14/11/2025, 17:48:06                │
└─────────────────────────────────────┘
```

---

## 📋 Ejemplo Completo con Relaciones

Cuando se consulta una empresa con sus relaciones cargadas, el objeto incluye:

```json
{
    "id": 1,
    "ruc": "46464564646",
    "business_name": "vidrioS.A.C",
    "trade_name": "carlitos",
    "address": "av.sol",
    "ubigeo": "152011",
    "created_at": "2025-11-14T17:48:06.000000Z",
    "updated_at": "2025-11-14T17:48:06.000000Z",

    // Relaciones cargadas (opcional)
    "users": [
        {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "company_id": 1
        }
    ],
    "customers": [
        {
            "id": 1,
            "identity_type": "DNI",
            "identity_number": "74777394",
            "name": "Carlos",
            "address": "av.sol",
            "email": "carlos0989x@gmail.com",
            "phone": "924219178"
        }
    ],
    "products": [
        {
            "id": 1,
            "name": "Producto Ejemplo",
            "sale_price": "100.00",
            "active": true
        }
    ],
    "documents": [
        {
            "id": 1,
            "document_type": "01",
            "series": "F001",
            "number": 1,
            "status": "PENDING",
            "total": "118.00"
        }
    ],

    // Contadores (cuando se usa withCount)
    "users_count": 1,
    "customers_count": 5,
    "products_count": 12,
    "documents_count": 23
}
```

---

## 🔍 Consultas Útiles

### Obtener Empresa con Contadores

```php
$company = Company::withCount([
    'users',
    'customers',
    'products',
    'documents'
])->find(1);
```

### Obtener Empresa con Relaciones Completas

```php
$company = Company::with([
    'users',
    'customers',
    'products',
    'documents'
])->find(1);
```

### Obtener Empresa con Estadísticas de Documentos

```php
$company = Company::withCount([
    'documents as pending_documents_count' => function ($query) {
        $query->where('status', 'PENDING');
    },
    'documents as accepted_documents_count' => function ($query) {
        $query->where('status', 'ACCEPTED');
    }
])->find(1);
```

---

## 🎨 Componente React (TypeScript)

La interfaz TypeScript para una empresa en el frontend:

```typescript
interface Company {
    id: number;
    ruc: string;
    business_name: string;
    trade_name: string | null;
    address: string | null;
    ubigeo: string | null;
    created_at: string;
    updated_at: string;

    // Opcionales (cuando se cargan relaciones)
    users_count?: number;
    customers_count?: number;
    products_count?: number;
    documents_count?: number;

    // Relaciones completas (opcional)
    users?: User[];
    customers?: Customer[];
    products?: Product[];
    documents?: Document[];
}
```

---

## 📍 Rutas Relacionadas

- **Lista de Empresas**: `GET /companies`
- **Ver Empresa**: `GET /companies/{id}`
- **Crear Empresa**: `GET /companies/create` → `POST /companies`
- **Editar Empresa**: `GET /companies/{id}/edit` → `PUT /companies/{id}`
- **Eliminar Empresa**: `DELETE /companies/{id}`

---

## 🔐 Seguridad

### Campos Protegidos

Los siguientes campos **nunca** se exponen en las respuestas JSON:

1. **`certificate_password`**: Contraseña del certificado digital
2. **`password_sol`**: Contraseña del usuario SOL

Estos campos están definidos en el modelo `Company` como `$hidden`:

```php
protected $hidden = [
    'certificate_password',
    'password_sol',
];
```

### Validaciones

- El RUC debe tener exactamente 11 caracteres
- El Ubigeo debe tener exactamente 6 caracteres
- El certificado debe estar en formato PEM válido
- Las credenciales SOL son requeridas para enviar documentos a SUNAT

---

## 💡 Notas Importantes

1. **Multi-tenancy**: Cada empresa es completamente independiente. Los usuarios solo ven datos de su empresa asociada.

2. **Credenciales SUNAT**: Las credenciales SOL (`user_sol` y `password_sol`) son necesarias para enviar documentos electrónicos a SUNAT.

3. **Certificado Digital**: El certificado digital (.pem) se usa para firmar los XML antes de enviarlos a SUNAT.

4. **Ubigeo**: El código de ubicación geográfica es requerido por SUNAT y debe ser válido según el catálogo oficial.

5. **Estadísticas**: Los contadores se calculan dinámicamente y reflejan el estado actual de la empresa.

---

Este ejemplo muestra cómo se estructura y visualiza la información de una empresa en el sistema de facturación electrónica.
