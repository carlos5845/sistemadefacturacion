w# Guía Práctica de Uso del Sistema de Facturación Electrónica

Esta guía te muestra paso a paso cómo utilizar el sistema desde cero hasta emitir y enviar una factura electrónica a SUNAT.

---

## 🚀 Inicio: Primeros Pasos

### Paso 1: Registro e Inicio de Sesión

#### 1.1. Crear una Cuenta

```
1. Ir a: http://tu-dominio.com/register
2. Completar formulario:
   - Nombre: Juan Pérez
   - Email: juan@miempresa.com
   - Contraseña: ********
   - Confirmar Contraseña: ********
3. Hacer clic en "Registrarse"
```

#### 1.2. Iniciar Sesión

```
1. Ir a: http://tu-dominio.com/login
2. Ingresar credenciales:
   - Email: juan@miempresa.com
   - Contraseña: ********
3. Hacer clic en "Iniciar Sesión"
```

**Resultado**: Serás redirigido al Dashboard, pero verás un mensaje indicando que necesitas asociarte a una empresa.

---

## 🏢 Paso 2: Crear tu Empresa

### 2.1. Acceder a la Sección de Empresas

```
1. En el sidebar, hacer clic en "Empresas"
2. O ir directamente a: http://tu-dominio.com/companies
```

### 2.2. Crear Nueva Empresa

```
1. Hacer clic en el botón "Nueva Empresa" o "Crear"
2. Completar el formulario con los datos de tu empresa:

   ┌─────────────────────────────────────────┐
   │ Formulario de Empresa                    │
   ├─────────────────────────────────────────┤
   │ RUC*: 20123456789                        │
   │ Razón Social*: Mi Empresa S.A.C.        │
   │ Nombre Comercial: Mi Tienda              │
   │ Dirección*: Av. Principal 123           │
   │ Ubigeo*: 150101                          │
   │                                          │
   │ Credenciales SUNAT:                      │
   │ Usuario SOL*: MODDATOS                   │
   │ Contraseña SOL*: miPassword123           │
   │                                          │
   │ Certificado Digital:                    │
   │ Certificado (.pem)*: [Seleccionar archivo]│
   │ Contraseña del Certificado*: certPass123│
   │                                          │
   │ [Guardar]  [Cancelar]                   │
   └─────────────────────────────────────────┘

3. Hacer clic en "Guardar"
```

**Datos de Ejemplo**:

- **RUC**: `20123456789` (11 dígitos)
- **Razón Social**: `Mi Empresa S.A.C.`
- **Nombre Comercial**: `Mi Tienda`
- **Dirección**: `Av. Principal 123`
- **Ubigeo**: `150101` (Lima, Lima, Lima)
- **Usuario SOL**: `MODDATOS` (obtenido de SUNAT)
- **Contraseña SOL**: `miPassword123` (obtenido de SUNAT)
- **Certificado**: Archivo `.pem` descargado de SUNAT
- **Contraseña del Certificado**: La que configuraste al generar el certificado

**Resultado**:

- ✅ Empresa creada exitosamente
- ✅ Tu usuario queda automáticamente asociado a esta empresa
- ✅ Ahora puedes acceder a todas las funcionalidades

---

## 👥 Paso 3: Registrar Clientes

### 3.1. Acceder a Clientes

```
1. En el sidebar, hacer clic en "Clientes"
2. O ir a: http://tu-dominio.com/customers
```

### 3.2. Crear Primer Cliente

```
1. Hacer clic en "Nuevo Cliente" o "Crear"
2. Completar formulario:

   ┌─────────────────────────────────────────┐
   │ Formulario de Cliente                    │
   ├─────────────────────────────────────────┤
   │ Tipo de Documento*: [DNI ▼]             │
   │ Número de Documento*: 12345678           │
   │ Nombre Completo*: Carlos Rodríguez      │
   │ Dirección*: Jr. Los Olivos 456          │
   │ Email: carlos@email.com                 │
   │ Teléfono: 987654321                     │
   │                                          │
   │ [Guardar]  [Cancelar]                   │
   └─────────────────────────────────────────┘

3. Hacer clic en "Guardar"
```

**Tipos de Documento Disponibles**:

- **DNI**: Documento Nacional de Identidad (8 dígitos)
- **RUC**: Registro Único de Contribuyente (11 dígitos)
- **CE**: Carné de Extranjería
- **PAS**: Pasaporte

**Ejemplo de Cliente con RUC**:

```
Tipo de Documento: RUC
Número de Documento: 20123456789
Nombre Completo: Empresa Cliente S.A.C.
Dirección: Av. Comercial 789
Email: contacto@cliente.com
Teléfono: 987654321
```

**Resultado**: Cliente registrado y disponible para usar en documentos.

---

## 📦 Paso 4: Registrar Productos

### 4.1. Acceder a Productos

```
1. En el sidebar, hacer clic en "Productos"
2. O ir a: http://tu-dominio.com/products
```

### 4.2. Crear Categoría de Producto (si es necesario)

```
Nota: Las categorías se pueden crear desde la gestión de productos
```

### 4.3. Crear Primer Producto

```
1. Hacer clic en "Nuevo Producto" o "Crear"
2. Completar formulario:

   ┌─────────────────────────────────────────┐
   │ Formulario de Producto                   │
   ├─────────────────────────────────────────┤
   │ Nombre*: Laptop HP 15                   │
   │ Descripción: Laptop HP 15 pulgadas...    │
   │ Categoría: [Electrónica ▼]              │
   │ Unidad de Medida*: [NIU ▼]             │
   │                                          │
   │ Precios:                                 │
   │ Precio de Venta*: 2500.00               │
   │ Precio de Compra: 2000.00                │
   │                                          │
   │ Impuestos:                               │
   │ Tipo de Impuesto*: [Gravado 18% ▼]      │
   │ Incluye IGV: [✓] Sí                      │
   │                                          │
   │ Estado:                                  │
   │ [✓] Activo                              │
   │                                          │
   │ [Guardar]  [Cancelar]                   │
   └─────────────────────────────────────────┘

3. Hacer clic en "Guardar"
```

**Unidades de Medida Disponibles**:

- **NIU**: Unidad (para productos individuales)
- **KG**: Kilogramo
- **ZZ**: Servicio
- Y otras según catálogo SUNAT

**Tipos de Impuesto**:

- **Gravado 18%**: Producto con IGV (18%)
- **Exonerado**: Sin IGV
- **Inafecto**: Sin IGV
- **Exportación**: Sin IGV

**Ejemplo de Servicio**:

```
Nombre: Consultoría en Sistemas
Descripción: Servicio de consultoría...
Unidad de Medida: ZZ (Servicio)
Precio de Venta: 500.00
Tipo de Impuesto: Gravado 18%
Incluye IGV: Sí
```

**Resultado**: Producto registrado y disponible para usar en documentos.

---

## 📄 Paso 5: Crear un Documento Electrónico

### 5.1. Acceder a Documentos

```
1. En el sidebar, hacer clic en "Documentos"
2. O ir a: http://tu-dominio.com/documents
```

### 5.2. Crear Nueva Factura

```
1. Hacer clic en "Nuevo Documento" o "Crear"
2. Completar datos del documento:

   ┌─────────────────────────────────────────┐
   │ Datos del Documento                      │
   ├─────────────────────────────────────────┤
   │ Cliente*: [Carlos Rodríguez ▼]          │
   │ Tipo de Documento*: [Factura ▼]         │
   │ Serie*: F001                             │
   │ Número*: 1                                │
   │ Fecha de Emisión*: 15/11/2025           │
   │ Moneda*: [PEN - Soles ▼]                │
   │                                          │
   │ [Siguiente]                              │
   └─────────────────────────────────────────┘
```

**Tipos de Documento**:

- **01 - Factura**: Para ventas con RUC
- **03 - Boleta**: Para ventas con DNI
- **07 - Nota de Crédito**: Para anulaciones/descuentos
- **08 - Nota de Débito**: Para cargos adicionales

### 5.3. Agregar Items al Documento

```
3. En la sección de Items, agregar productos:

   ┌─────────────────────────────────────────┐
   │ Items del Documento                      │
   ├─────────────────────────────────────────┤
   │ ┌─────────────────────────────────────┐ │
   │ │ Producto: [Laptop HP 15 ▼]          │ │
   │ │ Descripción: Laptop HP 15 pulgadas  │ │
   │ │ Cantidad: 2                          │ │
   │ │ Precio Unitario: 2500.00             │ │
   │ │ Tipo Impuesto: [Gravado 18% ▼]       │ │
   │ │ Total: 5000.00                       │ │
   │ │ IGV: 900.00                          │ │
   │ │ [Eliminar]                            │ │
   │ └─────────────────────────────────────┘ │
   │                                          │
   │ ┌─────────────────────────────────────┐ │
   │ │ Producto: [Consultoría ▼]            │ │
   │ │ Descripción: Servicio de consultoría │ │
   │ │ Cantidad: 1                          │ │
   │ │ Precio Unitario: 500.00               │ │
   │ │ Tipo Impuesto: [Gravado 18% ▼]       │ │
   │ │ Total: 500.00                        │ │
   │ │ IGV: 90.00                           │ │
   │ │ [Eliminar]                            │ │
   │ └─────────────────────────────────────┘ │
   │                                          │
   │ [+ Agregar Item]                         │
   │                                          │
   │ Resumen:                                 │
   │ Subtotal Gravado: 5500.00               │
   │ IGV (18%): 990.00                       │
   │ Total: 6490.00                          │
   │                                          │
   │ [Guardar Documento]                      │
   └─────────────────────────────────────────┘
```

**Cómo Agregar un Item**:

1. Hacer clic en "Agregar Item"
2. Seleccionar producto (o escribir descripción manual)
3. Ingresar cantidad
4. El sistema calcula automáticamente:
    - Total del item (cantidad × precio)
    - IGV si aplica
    - Totales del documento

**Resultado**:

- ✅ Documento creado con estado `PENDING`
- ✅ Puedes editarlo o eliminarlo
- ✅ Redirección a la vista de detalle del documento

---

## 📤 Paso 6: Enviar Documento a SUNAT

### 6.1. Ver Detalle del Documento

```
1. Después de crear el documento, serás redirigido a:
   http://tu-dominio.com/documents/{id}

2. Verás la información completa:
   ┌─────────────────────────────────────────┐
   │ Factura F001-000001                      │
   ├─────────────────────────────────────────┤
   │ Cliente: Carlos Rodríguez                │
   │ DNI: 12345678                           │
   │ Fecha: 15/11/2025                       │
   │ Estado: PENDIENTE                       │
   │                                          │
   │ Items:                                   │
   │ 1. Laptop HP 15         2 x 2500 = 5000 │
   │ 2. Consultoría         1 x 500  = 500  │
   │                                          │
   │ Subtotal: 5500.00                       │
   │ IGV: 990.00                             │
   │ Total: 6490.00                          │
   │                                          │
   │ [Enviar a SUNAT]  [Editar]  [Eliminar]  │
   └─────────────────────────────────────────┘
```

### 6.2. Enviar a SUNAT

```
1. Hacer clic en el botón "Enviar a SUNAT"
2. Confirmar el envío (si se solicita)
3. El sistema mostrará:
   ✅ "Documento enviado a SUNAT. El proceso se está ejecutando en segundo plano."
```

**¿Qué sucede internamente?**:

1. El documento cambia su estado a `SENT`
2. Se genera el XML en formato UBL 2.1
3. Se firma el XML con el certificado digital
4. Se envía a SUNAT mediante petición SOAP
5. Se procesa la respuesta y se extrae el CDR
6. El estado se actualiza a `ACCEPTED` o `REJECTED`

### 6.3. Verificar Estado

```
1. Refrescar la página del documento
2. Ver el estado actualizado:

   ┌─────────────────────────────────────────┐
   │ Estado: ACEPTADO ✓                      │
   │                                          │
   │ Respuesta SUNAT:                        │
   │ Código: 0                               │
   │ Mensaje: La Factura ha sido aceptada    │
   │                                          │
   │ CDR Disponible: [Descargar CDR]         │
   └─────────────────────────────────────────┘
```

**Estados Posibles**:

- ✅ **ACCEPTED**: Documento aceptado por SUNAT (con CDR)
- ❌ **REJECTED**: Documento rechazado (ver mensaje de error)
- ⏳ **SENT**: Enviado, procesándose
- 📝 **PENDING**: Pendiente de envío

---

## 📊 Paso 7: Consultar Dashboard y Estadísticas

### 7.1. Ver Dashboard

```
1. Hacer clic en "Dashboard" en el sidebar
2. O ir a: http://tu-dominio.com/dashboard
```

**Vista del Dashboard**:

```
┌─────────────────────────────────────────┐
│ Dashboard                                │
├─────────────────────────────────────────┤
│ Estadísticas Generales:                 │
│                                          │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│ │ Clientes │ │ Productos│ │Documentos│ │
│ │    5     │ │   12     │ │   23     │ │
│ └──────────┘ └──────────┘ └──────────┘ │
│                                          │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│ │Pendientes│ │Aceptados│ │Rechazados│ │
│ │    3     │ │   18     │ │    2     │ │
│ └──────────┘ └──────────┘ └──────────┘ │
│                                          │
│ Total de Ventas: S/ 45,230.00           │
│                                          │
│ Últimos Documentos:                     │
│ • F001-000023 - Carlos R. - S/ 6490.00  │
│ • B001-000045 - María G. - S/ 1,200.00  │
│ • F001-000022 - Juan P. - S/ 3,500.00   │
└─────────────────────────────────────────┘
```

---

## 🔄 Flujo Completo Resumido

```
1. REGISTRO
   └─> Crear cuenta de usuario

2. EMPRESA
   └─> Crear empresa con credenciales SUNAT

3. MAESTROS
   ├─> Registrar clientes (DNI/RUC)
   └─> Registrar productos/servicios

4. DOCUMENTO
   ├─> Crear factura/boleta
   ├─> Agregar items (productos)
   └─> Guardar documento (estado: PENDING)

5. ENVÍO SUNAT
   ├─> Hacer clic en "Enviar a SUNAT"
   ├─> Sistema genera XML y firma
   ├─> Envía a SUNAT (estado: SENT)
   └─> Procesa respuesta (estado: ACCEPTED/REJECTED)

6. CONSULTA
   └─> Ver estado y descargar CDR
```

---

## ⚠️ Casos Comunes y Soluciones

### Problema: "Debe estar asociado a una empresa"

**Solución**:

1. Crear una empresa primero (`/companies/create`)
2. Tu usuario quedará automáticamente asociado

### Problema: "No puedo crear clientes/productos"

**Solución**:

1. Verificar que tengas una empresa asociada
2. Verificar que la empresa tenga `company_id` asignado
3. Si no, crear una nueva empresa

### Problema: "Documento rechazado por SUNAT"

**Solución**:

1. Revisar el mensaje de error en la respuesta SUNAT
2. Verificar que los datos del cliente sean correctos
3. Verificar que el RUC/DNI sea válido
4. Corregir el documento y volver a enviar

### Problema: "No puedo editar el documento"

**Solución**:

- Solo se pueden editar documentos en estado `PENDING`
- Si ya fue enviado a SUNAT, no se puede editar
- Si fue aceptado, crear una Nota de Crédito para anular

---

## 💡 Consejos y Mejores Prácticas

1. **Organización**:
    - Crea categorías de productos antes de crear muchos productos
    - Mantén los datos de clientes actualizados

2. **Documentos**:
    - Verifica todos los datos antes de enviar a SUNAT
    - Revisa que los totales sean correctos
    - Guarda el CDR después de que sea aceptado

3. **SUNAT**:
    - Usa el ambiente de pruebas primero (`e-beta.sunat.gob.pe`)
    - Verifica tus credenciales SOL antes de usar el sistema
    - Mantén tu certificado digital actualizado

4. **Seguridad**:
    - No compartas tus credenciales SOL
    - Protege tu certificado digital
    - Usa contraseñas seguras

---

## 📝 Checklist de Configuración Inicial

Antes de empezar a facturar, asegúrate de tener:

- [ ] Cuenta de usuario creada
- [ ] Empresa registrada con:
    - [ ] RUC válido
    - [ ] Credenciales SOL (usuario y contraseña)
    - [ ] Certificado digital (.pem)
    - [ ] Ubigeo correcto
- [ ] Al menos un cliente registrado
- [ ] Al menos un producto registrado
- [ ] Categorías de productos creadas (opcional pero recomendado)

---

## 🎯 Ejemplo Completo: Venta Real

**Escenario**: Vender 2 laptops y un servicio de instalación

```
1. Cliente ya existe: "Carlos Rodríguez" (DNI: 12345678)

2. Productos ya existen:
   - Laptop HP 15 (S/ 2,500.00)
   - Servicio de Instalación (S/ 200.00)

3. Crear Boleta:
   - Cliente: Carlos Rodríguez
   - Tipo: Boleta (03)
   - Serie: B001
   - Número: 1
   - Fecha: 15/11/2025

4. Agregar Items:
   - Laptop HP 15 × 2 = S/ 5,000.00
   - Servicio Instalación × 1 = S/ 200.00
   - Subtotal: S/ 5,200.00
   - IGV (18%): S/ 936.00
   - Total: S/ 6,136.00

5. Guardar documento

6. Enviar a SUNAT

7. Verificar aceptación

8. Descargar CDR
```

---

Esta guía te lleva paso a paso desde el registro hasta la emisión y envío de documentos electrónicos a SUNAT. ¡Sigue estos pasos y estarás facturando en minutos!
