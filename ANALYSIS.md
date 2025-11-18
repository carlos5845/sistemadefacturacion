# Análisis del Proyecto: Sistema de Facturación Electrónica

Este documento detalla el análisis de la estructura y los módulos principales del proyecto, que es una aplicación de facturación electrónica con una arquitectura multi-empresa.

### 1. Arquitectura Multi-empresa (Multi-tenant)
El sistema está construido sobre un modelo central, `Company` (Empresa). Cada empresa funciona como una unidad aislada que posee sus propios recursos:
- **Usuarios** (`User`)
- **Clientes** (`Customer`)
- **Productos** (`Product`)
- **Documentos** (`Document`, es decir, facturas/boletas)

### 2. Autenticación y Seguridad
- Se utiliza **Laravel Fortify** para gestionar la autenticación (inicio de sesión, registro, restablecimiento de contraseña).w
- Las vistas de autenticación se renderizan con **Inertia.js y React**, no con las plantillas Blade tradicionales de Laravel.
- Está habilitada la **autenticación de dos factores (2FA)** para mayor seguridad.

### 3. Integración con SUNAT (Facturación Electrónica)
Este es el módulo de negocio principal. La aplicación está preparada para generar y enviar documentos electrónicos a la SUNAT (entidad tributaria de Perú).
- **Credenciales:** El modelo `Company` almacena las credenciales del "Usuario SOL" necesarias para la comunicación con la SUNAT.
- **Gestión de Documentos:** El modelo `Document` almacena toda la información de la factura, incluyendo el XML generado, el XML firmado, el código hash y la respuesta de la SUNAT.
- **Procesamiento Asíncrono:** Se utiliza un job (`SendDocumentToSunat`) para enviar los documentos en segundo plano. Esto mejora el rendimiento y la experiencia del usuario, ya que no tienen que esperar la respuesta de la SUNAT en tiempo real.
- **Lógica de Negocio:** El directorio `app/Services/Sunat` contiene la lógica para interactuar con los servicios de la SUNAT (generación de XML, firma digital, etc.).

### 4. Gestión de Datos (Modelos Principales)
La aplicación cuenta con un conjunto de modelos bien definidos para gestionar la información:
- `Customer`: Gestión de clientes.
- `Product` y `ProductCategory`: Gestión de productos y sus categorías.
- `Warehouse` e `InventoryStock`: Control básico de almacenes y stock.

### 5. Frontend con React
- La interfaz de usuario está construida con **React**.
- **Inertia.js** actúa como puente para conectar el backend de Laravel con el frontend de React de una manera fluida, casi como si fuera una aplicación de una sola página (SPA).
- Los componentes y páginas de React se encuentran en el directorio `resources/js`.

### 6. Roles y Permisos
- Se utiliza el paquete `spatie/laravel-permission`.
- El modelo `User` tiene el trait `HasRoles`, lo que indica que el sistema permite asignar roles (ej. "administrador", "vendedor") y permisos específicos a los usuarios para controlar el acceso a diferentes funcionalidades.

En resumen, el proyecto es una aplicación robusta y bien estructurada para la facturación electrónica, con un enfoque claro en la arquitectura multi-empresa y la integración con servicios externos como la SUNAT.
