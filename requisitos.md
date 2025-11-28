okeya antes de eso quiero que verifique todos estos campos si cumple el sistema;ertificado digital válido para SUNAT BETA

No puedes usar certificados self-signed.

Debes usar un certificado de pruebas que SUNAT acepte.

Te puedo generar uno completamente válido si deseas.

El archivo debe ser:

Formato: .pfx / .p12

Longitud mínima de clave: 2048 bits

Contraseña obligatoria

Contiene certificado + llave privada

✅ 3. Firmar el XML antes de enviarlo

SUNAT no acepta XML sin firma.

Debes aplicar:

✔ Firma digital XAdES-BES

Para facturas

Para boletas

Para notas de crédito

Para notas de débito

NO se usa XML-DSIG simple.
SUNAT exige XAdES (AdES BES).

✅ 4. Estructura UBL correcta
✔ Versión y customización
UBLVersionID: 2.1
CustomizationID: 2.0

✔ Tipo de documento:

Factura: 01

Boleta: 03

Nota crédito: 07

Nota débito: 08

✔ Serie correcta:

Factura: F001 – F999

Boleta: B001 – B999

Notas: FC01, FD01, BC01, BD01

✔ Número correlativo sin saltos
✅ 5. Cálculos tributarios correctos

SUNAT valida:

✔ IGV calculado exactamente

(18% sobre el valor de venta)

✔ Totales coincidentes:

LineExtensionAmount

TaxInclusiveAmount

PayableAmount

TaxTotal

✔ Redondeo a 2 decimales siempre

Si un solo decimal difiere → RECHAZADO.

✅ 6. Reglas del emisor (tu empresa en beta)

Debe coincidir con los datos de SUNAT:

RUC: 20000000001

Razón social: "Empresa de Pruebas" o cualquier texto

Ubigeo válido

Dirección válida

"Nombre comercial" cualquiera

✅ 7. Reglas del cliente

Para facturas (01):

Obligatorio RUC

Nombre o razón social

No se puede usar DNI

Para boletas (03):

Puedes usar DNI

Puedes usar consumidor final (99999999)

✅ 8. Validaciones de SUNAT a nivel XML
✔ orden correcto de nodos

SUNAT es muy estricto en la secuencia UBL.

✔ namespaces obligatorios:
xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"

✔ incluir UBLExtensions aunque esté vacío

Si no, rechaza.

✅ 9. Envío en ZIP

SUNAT NO recibe el XML directo.

Debes enviar:

F001-00000001.xml → comprimir → F001-00000001.zip


Dentro:

Solo 1 XML

Sin carpetas

Sin BOM

✅ 10. Conexión SOAP correcta

Endpoint BETA:

https://e-beta.sunat.gob.pe/ol-ti-itcpfegem/billService


Operación:

sendBill


Parámetros:

Nombre del ZIP

Archivo ZIP en binario Base64

Firma digital ya aplicada dentro del XML

✅ 11. Validación de respuesta

SUNAT beta te devuelve:

CdrZip → CDP

Respuesta → "Proceso Exitoso"

Código 0