# Informe Entrega 3 - Bases de datos IIC2413

## Datos del Alumno
| **Apellidos**       | **Nombres**          | **Número de Alumno** |
|---------------------|----------------------|----------------------|
| Ramirez Padilla     | Tomas Ignacio        |23200308              |


## 1. Descripción y análisis del problema
 
    Se nos pide implementar un portal web que cumpla con las necesidades de los usuarios de un club social, el cual tiene diferentes sucursales, actividades, eventos, etc. Para esto, se nos entrega un esquema de base de datos y un conjunto de requerimientos funcionales y no funcionales. El objetivo es crear un portal web que permita a los usuarios realizar acciones como: 
    - Iniciar sesión y cerrar sesión
    - Registrarse en el portal
    - Ver los eventos y actividades que se realizan en el club
    - Reservar lugares y actividades
    - Pagar cuotas y reservas
    - Ver el perfil del usuario y sus reservas
    - Ver los eventos y actividades

## 2. Solución aplicada

    Para resolver el problema planteado, se construyó una aplicación web utilizando PHP y PostgreSQL. Las principales implementaciones fueron:

    - Autenticación y Sesiones ("login.php", "procesar_login.php"): Se desarrolló un sistema de inicio de sesión que distingue entre roles (Socio, Administrativo, Administrador) y redirige a un panel centralizado. Se añadió también un registro en "accesos.log" para monitorear los intentos de conexión.

    - Control de Acceso a Datos ("ver_eventos.php", "mis_arriendos.php"): Se incorporó lógica de filtrado. Si el usuario logueado es un Socio, las consultas solo retornan las reservas y eventos donde su RUN coincida como cliente. Los roles administrativos, en cambio, pueden visualizar todos los registros.

    - Manejo de Transacciones ("crear_evento.php", "procesar_registro.php"): Para mantener la consistencia de la base de datos, los formularios que afectan múltiples tablas se ejecutan dentro de transacciones de base de datos, revirtiendo los cambios si ocurre algún error de integridad.

    - Gestión de Vistas SQL ("vistas_consultas.sql"): Se modularizó la lógica de negocio compleja creando vistas directamente en PostgreSQL para facilitar los reportes de ingresos, agendas de sucursales, listados de morosos y control de beneficiarios.

## 3. Referencias y bibliografía externa
