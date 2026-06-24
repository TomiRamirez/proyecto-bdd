# Informe Entrega X - Bases de datos IIC2413

## Datos del Alumno
| **Apellidos**       | **Nombres**          | **Número de Alumno** |
|---------------------|----------------------|----------------------|
| Ramirez Padilla     | Tomas Ignacio        | 23200308             |


## 1. Descripción y análisis del problema

El desafío principal ademas de almacenar la información, es realizar un proceso de Data Cleaning (limpieza de datos) exhaustivo. Dado que los datos originales tienen muchos errores y con criterios de validación distintos a los de un sistema de bases de datos relacional,  estos archivos "sucios" en tienen que cumplir con una serie de requisitos como sus (llaves primarias, foráneas y dominios).
 
La solución se aborda mediante dos fases 
1) Fase de Limpieza (PHP - main.php): Se actúa como un filtro inicial. El programa procesa los archivos XXX.csv para corregir errores reparables (como formatos de correo o prefijos telefónicos) o descartar registros irreparables que comprometan la integridad de la tupla. El resultado son archivos XXXOK.csv y un registro detallado de acciones en XXXLOG.csv. 

2) Fase de Carga y Validación (SQL , carga.sql): Se define el esquema relacional. En esta etapa se generan identificadores internos (PK) y se establecen las relaciones (FK) entre entidades como sucursales, personas y pagos. El DBMS hace la validación final de restricciones, garantizando que la pérdida de información sea mínima y tenga consistencia.  Finalmente, el sistema debe ser capaz de responder a consultas sql , como la generación de agendas de sucursales, reportes de socios morosos y análisis de ingresos mensuales, permitiendo así una administración profesional y automatizada del club.

## 2. Solución aplicada

La solución se desarrolló en PHP, implementando una limpieza que prioriza la recuperación de datos sobre la eliminación. El proceso se divide en tres pilares fundamentales: funciones de utilidad global, validación mediante fuentes externas y lógica de negocio específica por archivo.


Funciones de Utilidad Centralizadas: Se implementaron funciones como calcularDV, arreglar_fecha y quitar_tildes para estandarizar datos de forma transversal en todos los archivos, evitando la duplicidad de lógica.

Validación Geográfica: El sistema carga un diccionario dinámico desde regiones_comunas.csv para verificar la existencia oficial de comunas y corregir automáticamente códigos de región inconsistentes.

Reparación de RUN: En lugar de descartar registros, se recalcula el dígito verificador para rescatar tuplas con errores de tipeo.

Normalización de Datos: Se corrigieron formatos de fecha a YYYY-MM-DD, se repararon precios escritos en texto y se estandarizaron números de teléfono y correos electrónicos.



## 2.1 Limpieza de datos con PHP

Se detallan las reglas de limpieza implementadas en el script main.php para transformar los archivos originales en registros válidos para la base de datos:

Eliminación por ausencia de precio: En el archivo sucursales_lugares.csv, se eliminaron los registros donde el campo precio era nulo o inexistente. Se determinó que el valor es un dato crítico para la operación del club y, al ser una restricción "No Nulo", no se podía procesar la tupla sin comprometer la integridad del negocio.

Integridad de registros dependientes: Para las personas con roles de beneficiario, adicional o invitado, se eliminaron aquellas que no contaban con un RUN de socio titular asociado. Esta medida asegura que toda persona no socia en el sistema DCColo tenga un respaldo que valide su acceso y relación con el club.

Normalización de formatos de fecha: Se estandarizaron todas las fechas al formato YYYY-MM-DD y, cuando fue requerido por el negocio, se incluyó la hora en formato HH:MM. En casos donde la fecha de nacimiento solo incluía el año, se completó el registro fijando por defecto el día 01 de enero para mantener la validez del dato.  

Validación y reparación de RUN: Se verificó el formato del run (sin puntos y con guion). Para evitar una pérdida masiva de datos, en lugar de eliminar los registros con dígitos verificadores erróneos, se implementó un algoritmo para recalcular y asignar el dígito correcto.  

Estandarización de números telefónicos: Aquellos números que presentaban formatos inválidos fueron reemplazados por el valor genérico 100000000. Esto permite cumplir con la restricción de formato sin descartar la información del socio.  

Conversión de valores numéricos: Se identificaron precios en el archivo de sucursales escritos en texto ("nueve mil"). El codigo transforma estas cadenas de texto a su valor entero correspondiente.

Corrección de errores de tipeo: Se realizó una limpieza de strings en el campo de parentesco, corrigiendo errores comunes de codificación o tipeo como "c-nyuge" por "Cónyuge". 

### 2.2 Carga de datos con Psql

El proceso en carga.sql utiliza tablas para transformar los datos planos (CSV) en un modelo relacional normalizado, asegurando la integridad referencial. La distribución se realizó de la siguiente manera:

Uso de Tablas Temporales : Se empleó CREATE TEMP TABLE y \copy para volcar, transformar y cruzar los archivos XXXOK.csv en memoria sin violar las llaves foráneas (FK) del esquema final.

Generación de Llaves Primarias (PK): Se crearon códigos deterministas usando funciones de texto (ej. extrayendo caracteres) para sucursales y lugares. Para las reservas sin código, se usó un identificador .

Jerarquía de Personas: Se pobló  (persona, socio, membresia, usuario). Mediante NOT EXISTS, se crearon registros automáticos para clientes y contactos que solo aparecían en eventos.

Descomposición lugares y eventos: El archivo de sucursales se dividió en region, comuna, sucursal, lugar y precio_lugar.

Procedimiento Almacenado (SP): Se programó sp_cargar_asistentes para separar los strings de asistentes (delimitados por punto y coma) e insertar un registro por persona en asistente_evento.

### 2.3 Consultas SQL


## 3. Referencias y bibliografía externa
