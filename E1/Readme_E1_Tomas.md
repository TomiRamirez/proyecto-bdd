# Informe Entrega X - Bases de datos IIC2413

## Datos del Alumno
| **Apellidos**       | **Nombres**          | **Numero de Alumno** |
|---------------------|----------------------|----------------------|
| Ramirez Padilla     | Tomas Ignacio        |23200308              |


## 1. Descripcion y analisis del problema
 
El Club DCColo gestiona hoy una operación que abarca desde el arriendo de canchas hasta servicios de hospedaje y eventos corporativos. El problema central no es solo la variedad de prestaciones, sino la dificultad de administrar perfiles de usuario  diversos como socios, cargas familiares, empleados y externos en un sistema que se percibe fragmentado. Aunque todos comparten datos de identificación básicos, cada rol exige reglas de negocio y permisos específicos que se cruzan de forma constante.

A esta mezcla se suma la complejidad de manejar múltiples sucursales, cada una con tarifas  y modalidades de pago de membresías que requieren un control automático de vigencias y morosidad. El diseño actual falla al intentar conectar estos roles y precios variables sin generar una redundancia de datos. Por lo tanto, se busca modelar una arquitectura que conecte los flujos de información, garantizando consistencia administrativa y financiera sin importar la sede o el rol que el usuario desempeñe en el sistema.

## 2. Solucion aplicada

La solución se construye sobre tres decisiones de diseño principales:

Jerarquía de Persona: Implementamos una jerarquía de "__Persona__" como base. Así, sin importar si alguien es __socio_titular__, __empleado__ o __invitado__, sus datos personales se registran una sola vez. Esto elimina la redundancia de datos y nos asegura cumplir con la normalización BCNF desde la raíz.

Separación de precios y transacciones:: Desacoplamos las __tarifa_lugar__ separados de los arriendos reales.Lo mismo con __Valor_Membresia__ separado de __Membresia__. Así, cambiar un precio no modifica el historial de transacciones pasadas

Membresías con cuotas individuales: Cada __membresia__ anual se divide en hasta 12 registros de __CuotaMembresia__, uno por mes. Esto permite consultar atrasos, calcular deudas y controlar suspensiones fila por fila, sin necesidad de lógica compleja en el código.

### 3 Modelo Entidad Relacion

![Diagrama E/R DCColo](diagramaE_R_final_drawio.svg)

### 4 Entidades Debiles


- La entidad __CuotaMembresia__ es debil porque su llave primaria depende de la entidad __Membresia__: el atributo __numero_mes__ (1–12) solo identifica  de forma unica una cuota dentro de una membresia especifica.

- La entidad __AsistenteEvento__ es debil porque su llave primaria depende de la entidad __Evento__: el atributo __identificador__ (RUN del asistente) solo es unico en el contexto del evento al que asiste.

 La entidad __Tarifa_Lugar__ es debil porque su llave primaria depende de la entidad __Lugar__: la combinacion (__dia_semana__, __hora__,__fecha_inicio_vigencia__) identifica una tarifa unicamente dentro deun lugar determinado.

La entidad __Lugar__ es debil porque su identificacion y existencia dependen totalmente de la entidad __Sucursal__: el atributo id_lugar (por ejemplo, "Cancha 1" o "Quincho 1") no es universalmente unico en todo el club, sino que solo identifica de forma unica a un recinto dentro del contexto de una __sucursal__ especifica.


### 5. Llaves Primarias  y Compuestas

#### 5.1 Persona
- La llave primaria es __RUN__ porque es una identificacion de forma unica y no puede repetirse

#### 5.2 Region
- La llave primaria es __cod_region__ (entero 1–16), porque es el identificador unico de cada region.

#### 5.3 Comuna
- La llave primaria es __cod_comuna__ (entero de 5 digitos) porque es el codigo unico de cada comuna.

#### 5.4 Sucursal
- La llave primaria es __id_sucursal__ porque no pueden existir dos sucursales con el mismo identificador.

#### 5.5 Valor_Membresia
- La llave compuesta es (__Sucursal.id_sucursal__, __tipo_socio__, __fecha_inicio_vigencia__) porque el valor varia por sucursal, por tipode socio (titular / adicional) y tiene vigencia temporal.

#### 5.6 SocioTitular
- La llave primaria es __Persona.RUN__ porque hereda la identidad de Persona y un socio titular corresponde a una unica persona.

#### 5.7 Membresia
- La llave primaria es __id_membresia__ porque un mismo titular puede tenervarias membresias a lo largo de los años y deben distinguirse.

#### 5.8 CuotaMembresia
- La llave compuesta es (__Membresia.id_membresia__, __numero_mes__) porque no pueden existir dos cuotas del mismo mes para la misma membresia.

#### 5.9 Beneficiario
- La llave primaria es __Persona.RUN__ porque el beneficiario es una persona registrada y su RUN la identifica univocamente.

#### 5.10 Adicional
- La llave primaria es __Persona.RUN__ por la misma razon que el beneficiario: es una persona del directorio general.

#### 5.11 Cargo
- La llave primaria es __id_cargo__ porque una persona puede tener distintos cargos en distintas sucursales y en distintos periodos de tiempo.

#### 5.12 Usuario
- La llave primaria es __Persona.RUN__ porque cada usuario del sistema corresponde a una unica persona.

#### 5.13 Empresa
- La llave primaria es __RUT__ porque el RUT identifica de forma unica a toda persona juridica en Chile.

#### 5.14 ContactoEmpresa
- La llave compuesta es (__Empresa.RUT__, __Persona.RUN__) porque una misma persona no puede ser contacto de la misma empresa dos veces,pero si puede ser contacto de empresas distintas.

#### 5.15 Lugar
- La llave primaria es __id_lugar__ porque no pueden existir dos lugares con el mismo identificador.

#### 5.16 Tarifa_Lugar
- La llave compuesta es (__Lugar.id_lugar__, __dia_semana__, __hora__, __fecha_inicio_vigencia__) porque una tarifa es unica para un lugar,un dia, una hora y un periodo de vigencia determinados.

#### 5.17 Arriendo
- La llave primaria es __id_arriendo__ porque no pueden existir dos arriendos con el mismo identificador.

#### 5.18 Evento
- La llave primaria es __id_evento__ (codigo unico) porque no pueden existir dos eventos con el mismo codigo.

#### 5.19 AsistenteEvento
- La llave compuesta es (__Evento.id_evento__, __identificador__) porque una misma persona no puede registrarse dos veces en el mismo evento.


## 6. Explicacion cardinalidades modelo E/R

#### 6.1 Region y Comuna {1, 1 a n}
__Region__ puede tener varias __Comunas__. __Comuna__ pertenece exactamente a 1 __Region__.

#### 6.2 Comuna y Persona {1, n}
__Comuna__ puede tener varias instancias de __Persona__ registradas en ella. __Persona__ pertenece a exactamente 1 __Comuna__.

#### 6.3 Persona y SocioTitular {1, 0 a 1}
__Persona__ puede tener 0 o 1 instancia de __SocioTitular__ (no toda persona es socio titular). __SocioTitular__ corresponde a exactamente 1 __Persona__.

#### 6.4 Persona y Empleado {1, 0 a 1}
__Persona__ puede tener 0 o 1 instancia de __Empleado__ (no toda persona es empleado). __Empleado__ corresponde a exactamente 1 __Persona__.

#### 6.5 Persona y Usuario {1, 0 a 1}
__Persona__ puede tener 0 o 1 instancia de __Usuario__ del sistema. __Usuario__ corresponde a exactamente 1 __Persona__.

#### 6.6 SocioTitular y Sucursal {n, 1}
Varios __SociosTitulares__ pueden pertenecer a la misma __Sucursal__. __SocioTitular__ pertenece a exactamente 1 __Sucursal__ (determinada por su comuna).

#### 6.7 SocioTitular y Membresia {1, 1 a n}
__SocioTitular__ puede tener varias __Membresias__ (una por año). __Membresia__ pertenece a exactamente 1 __SocioTitular__.

#### 6.8 Membresia y CuotaMembresia {1, 1 a n}
__Membresia__ puede tener hasta 12 __CuotasMembresia__. __CuotaMembresia__ pertenece a exactamente 1 __Membresia__.

#### 6.9 SocioTitular y Beneficiario {1, 0 a n}
__SocioTitular__ puede tener varios __Beneficiarios__ o ninguno. __Beneficiario__ depende de exactamente 1 __SocioTitular__.

#### 6.10 SocioTitular y Adicional {1, 0 a n}
__SocioTitular__ puede tener varios __Adicionales__ o ninguno. __Adicional__ depende de exactamente 1 __SocioTitular__.

#### 6.11 SocioTitular y Arriendo {1, 0 a n}
__SocioTitular__ puede tener varios __Arriendos__ o ninguno. __Arriendo__ es realizado por exactamente 1 __SocioTitular__.

#### 6.12 Arriendo y Persona (invitados) {n, n}
Un __Arriendo__ puede tener varios invitados (__Persona__) y una __Persona__ puede ser invitada a varios __Arriendos__.

#### 6.13 Sucursal y Lugar {1, 1 a n}
__Sucursal__ tiene al menos 1 __Lugar__. __Lugar__ pertenece a exactamente 1 __Sucursal__.

#### 6.14 Sucursal y Valor_Membresia {1, 1 a n}
__Sucursal__ tiene al menos 1 __Valor_Membresia__ vigente. __Valor_Membresia__ pertenece a exactamente 1 __Sucursal__.

#### 6.15 Lugar y Tarifa_Lugar {1, 1 a n}
__Lugar__ tiene al menos 1 __Tarifa_Lugar__. __Tarifa_Lugar__ pertenece a exactamente 1 __Lugar__.

#### 6.16 Lugar y Arriendo {1, 0 a n}
__Lugar__ puede tener varios __Arriendos__. __Arriendo__ utiliza exactamente 1 __Lugar__.

#### 6.17 Lugar y Evento {1, 0 a n}
__Lugar__ puede albergar varios __Eventos__. __Evento__ utiliza exactamente 1 __Lugar__.

#### 6.18 Sucursal y Evento {1, 0 a n}
__Sucursal__ puede tener varios __Eventos__. __Evento__ pertenece a exactamente 1 __Sucursal__.

#### 6.19 Evento y Asistente_Evento {1, 0 a n}
__Evento__ puede tener varios __AsistentesEvento__. __Asistente_Evento__ pertenece a exactamente 1 __Evento__.

#### 6.20 Empresa y ContactoEmpresa {1, 1 a n}
__Empresa__ tiene al menos 1 __ContactoEmpresa__. __ContactoEmpresa__ pertenece a exactamente 1 __Empresa__.

#### 6.21 Persona y Cargo {1, 0 a n}
__Persona__ puede tener varios __Cargos__ (en distintas sucursales o periodos). __Cargo__ corresponde a exactamente 1 __Persona__.

#### 6.22 Sucursal y Cargo {1, 0 a n}
__Sucursal__ puede tener varios __Cargos__ vigentes. __Cargo__ pertenece a exactamente 1 __Sucursal__.

## 7. Identificacion de jerarquias

Para el modelo del club se utilizo una jerarquia centrada en la entidad __Persona__. Esta decision viene del enunciado, que indica que todas las personas (socios, empleados, invitados, etc.) deben registrarse en un directorio unico.

Aplicar esta jerarquia es clave para cumplir con la normalizacion BCNF, porque nos evita repetir datos por todos lados. La estructura quedo asi:

Superclase (__Persona__): Centraliza los atributos comunes de cualquier individuo (RUN como llave primaria, nombre, correo, direccion y telefonos).

Subclases: Solo guardan la informacion especifica de cada rol, heredando el RUN de la Persona.

__Socio_Titular__: Se separa porque es el unico que asume los pagos y tiene un estado (activo o suspendido).

__Beneficiario__ y __Adicional__: Agregan las fechas de vigencia y el tipo de parentesco, lo que es necesario para calcular bien los cobros de las cuotas.

__Usuario__: Agrupa solo a quienes tienen acceso al sistema web, guardando sus credenciales (email, clave y tipo de cuenta).

__Empleado__: Permite vincular a la persona con un Cargo especifico y asignarlo a una Sucursal.

__Invitado__: Personas externas que asisten a los arriendos.

Con esta estructura aseguramos la fidelidad del modelo y evitamos la redundancia. Por ejemplo, si un gerente del club (__Empleado__) tambien es __Socio Titular__ y ademas es __Usuario__ del sistema, su telefono y correo se guardan una sola vez en la tabla Persona. Si cambia de numero, se actualiza automaticamente para todos sus roles.

## 8. Esquema Relacional

### Entidades base

- __Region__ : (<u>cod_region</u>: int, nombre: string)

- __Comuna__ : (<u>cod_comuna</u>: int, nombre: string, Region.cod_region: int)

- __Persona__ : (<u>RUN</u>: int, nombre_completo: string,correo: string, direccion_calle: string, Comuna.cod_comuna: int,telefono_celular: string, telefono_alternativo: string, fecha_nacimiento: date)

- __Empresa__ : (<u>RUT</u>: int, nombre: string)

- __ContactoEmpresa__ : (<u>Empresa.RUT</u>: int,<u>Persona.RUN</u>: int, cargo: string)

- __Sucursal__ : (<u>id_sucursal</u>: int, nombre: string,direccion: string, Comuna.cod_comuna: int)

- __Valor_Membresia__ : (<u>Sucursal.id_sucursal</u>: int,<u>tipo_socio</u>: string, <u>fecha_inicio_vigencia</u>: date,fecha_fin_vigencia: date, monto_anual: float)

### Personas y roles

- __SocioTitular__ : (<u>Persona.RUN</u>: int,Sucursal.id_sucursal: int, estado: bool)

- __Beneficiario__ : (<u>Persona.RUN</u>: int, SocioTitular.RUN: int, fecha_incorporacion: date,fecha_fin: date, tipo_relacion: string)

- __Adicional__ : (<u>Persona.RUN</u>: int,SocioTitular.RUN: int, fecha_incorporacion: date,fecha_fin: date, tipo_relacion: string)

- __Empleado__ : (<u>Persona.RUN</u>: int)

- __Usuario__ : (<u>Persona.RUN</u>: int, email: string,clave_encriptada: string, tipo: string)

- __Cargo__ : (<u>id_cargo</u>: int, Persona.RUN: int,Sucursal.id_sucursal: int, nombre_cargo: string,fecha_inicio: date, fecha_fin: date)

### Membresias y pagos

- __Membresia__ : (<u>id_membresia</u>: int,SocioTitular.RUN: int, año: int, fecha_inicio: date,fecha_fin: date, monto_total: float,modalidad_pago: string)

- __CuotaMembresia__ : (<u>Membresia.id_membresia</u>: int,<u>numero_mes</u>: int, monto: float,fecha_vencimiento: date, fecha_pago: date, pagada: bool)

### Lugares y arriendos

- __Lugar__ : (<u>id_lugar</u>: int,Sucursal.id_sucursal: int, tipo: string,capacidad: int, unidad_precio: string)

- __Tarifa_Lugar__ : (<u>Lugar.id_lugar</u>: int,<u>dia_semana</u>: string, <u>hora</u>: time,<u>fecha_inicio_vigencia</u>: date,fecha_fin_vigencia: date, precio: float)

- __Arriendo__ : (<u>id_arriendo</u>: int,Lugar.id_lugar: int, SocioTitular.RUN: int,fecha_inicio: date, hora_inicio: time,fecha_fin: date, hora_fin: time,ejecutado: bool, monto_cobrado: float)

### Eventos

- __Evento__ : (<u>id_evento</u>: string, nombre: string,fecha: date, hora: time, Lugar.id_lugar: int,Sucursal.id_sucursal: int,cliente_persona_RUN: int, cliente_empresa_RUT: int, contacto_empresa_RUN: int, monto_total: float, monto_anticipo: float, ejecutado: bool)

- __Asistente_Evento__ : (<u>Evento.id_evento</u>: string,<u>identificador</u>: string, nombre: string)





## 8.1 Justificacion del diseno (BCNF) 

El esquema relacional se encuentra normalizado en BCNF (y por ende en 3NF), ya que todos los determinantes son llaves candidatas.

Redundancia y Anomalias: Al centralizar los datos en la jerarquia __Persona__, eliminamos la redundancia. Si un __Empleado__ tambien es __Socio Titular__, su direccion y telefono se guardan solo una vez. Esto evita anomalias de actualizacion (no habra datos desincronizados entre roles) y tambien podemos crear un invitado sin inventarle datos de __membresia__.

Fidelidad: El esquema refleja las reglas del enunciado. Las jerarquias se ocuparon usando el RUN como llave primaria , y las entidades debiles (como __CuotaMembresia__ o __Lugar__) migraron correctamente combinando la llave de su entidad fuerte en su propia llave compuesta.

Simplicidad: Se aislaron los precios (__Valor_Membresia__, __Tarifa_Lugar__) de las transacciones operativas (__Arriendo__, __Membresia__). Esto evita guardar datos repetidos en cada transaccion y hace que la base de datos sea mas limpia y ligera.

Eleccion de Llaves Primarias:  Por un lado,elegi llaves naturales (RUN/RUT) para personas y empresas, ya que en Chile son unicos. Por otro lado, para transacciones frecuentes (Arriendo, Evento), se crearon llaves artificiales (como id_arriendo) para no tener llaves compuestas gigantes en las tablas relacionales y hacer las consultas SQL mas eficientes.





### 9 Consultas SQL

### a)
-- obtenemos la agenda de los arriendos de socios
SELECT 
    TO_CHAR(Arriendo.fecha_inicio, 'Day') AS "Dia",
    Arriendo.fecha_inicio AS "Fecha",
    Arriendo.hora_inicio AS "Hora",
    Lugar.tipo AS "Lugar",
    Persona.nombre_completo AS "Socio o Evento"
FROM Arriendo
INNER JOIN Lugar ON Arriendo.id_lugar = Lugar.id_lugar
INNER JOIN Sucursal ON Lugar.id_sucursal = Sucursal.id_sucursal
INNER JOIN Persona ON Arriendo.RUN = Persona.RUN
WHERE Sucursal.nombre = 'Santa Cruz'
  AND Arriendo.fecha_inicio >= '2026-04-06' 
  AND Arriendo.fecha_inicio <= '2026-04-12'

UNION ALL

-- obtenemos la agenda de los eventos programados
SELECT 
    TO_CHAR(Evento.fecha, 'Day') AS "Dia",
    Evento.fecha AS "Fecha",
    Evento.hora AS "Hora",
    Lugar.tipo AS "Lugar",
    Evento.nombre AS "Socio o Evento"
FROM Evento
INNER JOIN Lugar ON Evento.id_lugar = Lugar.id_lugar
INNER JOIN Sucursal ON Lugar.id_sucursal = Sucursal.id_sucursal
WHERE Sucursal.nombre = 'Santa Cruz'
  AND Evento.fecha >= '2026-04-06' 
  AND Evento.fecha <= '2026-04-12'

-- ordenamos por dia, hora y lugar
ORDER BY "Fecha" ASC, "Hora" ASC, "Lugar" ASC;


### b)
SELECT 
    Conceptos.clasificacion AS "Concepto de Ingreso",
    SUM(Conceptos.monto) AS "Monto Total (Mes Actual)"
FROM (
    --  ingresos por Cuotas de Membresia del mes actual
    SELECT 
        CASE 
            WHEN CuotaMembresia.pagada = TRUE THEN 'Ingresos efectivamente recibidos' 
            ELSE 'Ingresos futuros esperados' 
        END AS clasificacion,
        CuotaMembresia.monto AS monto
    FROM CuotaMembresia
    INNER JOIN Membresia ON CuotaMembresia.id_membresia = Membresia.id_membresia
    INNER JOIN SocioTitular ON Membresia.RUN = SocioTitular.RUN
    INNER JOIN Sucursal ON SocioTitular.id_sucursal = Sucursal.id_sucursal
    WHERE Sucursal.nombre = 'Santa Cruz'
      AND CuotaMembresia.numero_mes = EXTRACT(MONTH FROM CURRENT_DATE)
      AND Membresia.año = EXTRACT(YEAR FROM CURRENT_DATE)

    UNION ALL

    -- ingresos por Arriendos del mes actual
    SELECT 
        CASE 
            WHEN Arriendo.ejecutado = TRUE THEN 'Ingresos efectivamente recibidos' 
            ELSE 'Ingresos futuros esperados' 
        END AS clasificacion,
        Arriendo.monto_cobrado AS monto
    FROM Arriendo
    INNER JOIN Lugar ON Arriendo.id_lugar = Lugar.id_lugar
    INNER JOIN Sucursal ON Lugar.id_sucursal = Sucursal.id_sucursal
    WHERE Sucursal.nombre = 'Santa Cruz'
      AND EXTRACT(MONTH FROM Arriendo.fecha_inicio) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR FROM Arriendo.fecha_inicio) = EXTRACT(YEAR FROM CURRENT_DATE)

    UNION ALL

    --  El Anticipo
    SELECT 
        'Ingresos efectivamente recibidos' AS clasificacion,
        Evento.monto_anticipo AS monto
    FROM Evento
    INNER JOIN Sucursal ON Evento.id_sucursal = Sucursal.id_sucursal
    WHERE Sucursal.nombre = 'Santa Cruz'
      AND EXTRACT(MONTH FROM Evento.fecha) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR FROM Evento.fecha) = EXTRACT(YEAR FROM CURRENT_DATE)

    UNION ALL

    -- El Saldo Restante 
    SELECT 
        CASE 
            WHEN Evento.ejecutado = TRUE THEN 'Ingresos efectivamente recibidos' 
            ELSE 'Ingresos futuros esperados' 
        END AS clasificacion,
        (Evento.monto_total - Evento.monto_anticipo) AS monto
    FROM Evento
    INNER JOIN Sucursal ON Evento.id_sucursal = Sucursal.id_sucursal
    WHERE Sucursal.nombre = 'Santa Cruz'
      AND EXTRACT(MONTH FROM Evento.fecha) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR FROM Evento.fecha) = EXTRACT(YEAR FROM CURRENT_DATE)

) AS Conceptos
GROUP BY 
    Conceptos.clasificacion;



### c)

SELECT 
    Persona.nombre_completo AS "Nombre del Socio",
    Persona.RUN AS "RUN",
    Sucursal.nombre AS "Sucursal",
    SUM(CuotaMembresia.monto) AS "Total Deuda",
    COUNT(*) AS "Cantidad de Meses"
FROM Persona
INNER JOIN SocioTitular ON Persona.RUN = SocioTitular.RUN
INNER JOIN Sucursal ON SocioTitular.id_sucursal = Sucursal.id_sucursal
INNER JOIN Membresia ON SocioTitular.RUN = Membresia.RUN
INNER JOIN CuotaMembresia ON Membresia.id_membresia = CuotaMembresia.id_membresia
WHERE CuotaMembresia.pagada = false 
  AND CuotaMembresia.fecha_vencimiento < CURRENT_DATE
GROUP BY 
    Persona.RUN, 
    Persona.nombre_completo, 
    Sucursal.nombre
ORDER BY "Total Deuda" DESC;


### d)
SELECT 
    -- datos del Beneficiario 
    PersonaBeneficiario.RUN AS "RUN Beneficiario",
    PersonaBeneficiario.nombre_completo AS "Nombre Beneficiario",
    PersonaBeneficiario.correo AS "Correo Beneficiario",
    PersonaBeneficiario.telefono_celular AS "Celular Beneficiario",
    
    -- datos del Socio titular 
    PersonaSocioTitular.RUN AS "RUN Titular",
    PersonaSocioTitular.nombre_completo AS "Nombre Titular",
    PersonaSocioTitular.correo AS "Correo Titular",
    PersonaSocioTitular.telefono_celular AS "Celular Titular"

FROM Beneficiario
-- Buscamoss los datos personales del hijo
INNER JOIN Persona AS PersonaBeneficiario 
    ON Beneficiario.RUN = PersonaBeneficiario.RUN

-- Conectar con el Socio titular 
INNER JOIN SocioTitular 
    ON Beneficiario.run_titular = SocioTitular.RUN

-- buscamos los datos personales del Socio titular
INNER JOIN Persona AS PersonaSocioTitular 
    ON SocioTitular.RUN = PersonaSocioTitular.RUN

WHERE 
    -- filtramos que sean hijos
    (Beneficiario.tipo_relacion = 'hijo' OR Beneficiario.tipo_relacion = 'hija')
    
    -- Buscamos a quienes cumplen 29 durante el año de la proxima renovacion.
    AND EXTRACT(YEAR FROM PersonaBeneficiario.fecha_nacimiento) = EXTRACT(YEAR FROM CURRENT_DATE) - 29;


### e)


# Referencias

TO_CHAR(): ya di este ramo anteriormente y sabia ocupar este comando.
EXTRACT(): ya di este ramo anteriormente y sabia ocupar este comando.
CASE: ya di este ramo anteriormente y sabia ocupar este comando.