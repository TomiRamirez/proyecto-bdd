-- 2.4 Consultas similares a E2 

-- 1) Despliegue de la agenda de una sucursal para una semana cualquiera.
CREATE OR REPLACE VIEW vista_agenda_sucursal AS
SELECT 
    l.codigo_sucursal,
    EXTRACT(DOW FROM r.fecha_inicio) AS dia_semana,
    CAST(r.fecha_inicio AS DATE) AS fecha,
    CAST(r.fecha_inicio AS TIME) AS hora,
    l.nombre AS lugar,
    p.nombre_completo AS actividad_o_socio
FROM reserva r
JOIN lugar l ON r.codigo_lugar = l.codigo_lugar
JOIN persona p ON r.run_reservante = p.run
UNION
SELECT 
    e.codigo_sucursal,
    EXTRACT(DOW FROM e.fecha_evento) AS dia_semana,
    e.fecha_evento AS fecha,
    CAST('00:00:00' AS TIME) AS hora,
    l.nombre AS lugar,
    e.nombre AS actividad_o_socio
FROM evento e
JOIN lugar l ON e.codigo_lugar = l.codigo_lugar
ORDER BY dia_semana, hora, lugar;


-- 2) Ingreso mensual (mes actual) por concepto de membresias, reservas ejecutadas y eventos
CREATE OR REPLACE VIEW vista_ingresos_mes_actual AS
SELECT 
    'Membresias' AS concepto,
    SUM(monto_pagado) AS recibidos,
    SUM(monto_base + monto_adicional - monto_pagado) AS esperados
FROM pago_cuota
WHERE EXTRACT(MONTH FROM fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
UNION
SELECT 
    'Reservas' AS concepto,
    SUM(monto) AS recibidos,
    0 AS esperados
FROM pago_reserva
WHERE EXTRACT(MONTH FROM fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
UNION
SELECT 
    'Eventos' AS concepto,
    SUM(monto) AS recibidos,
    0 AS esperados
FROM pago_evento
WHERE EXTRACT(MONTH FROM fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE);


-- 3) Socios con cuotas atrasadas (membresías y adicionales)
CREATE OR REPLACE VIEW vista_socios_morosos AS
SELECT 
    p.nombre_completo,
    p.run,
    s.codigo_sucursal_base AS sucursal,
    SUM(pc.monto_base + pc.monto_adicional - pc.monto_pagado) AS monto_adeudado,
    COUNT(pc.id_pago_cuota) AS numero_cuotas_atrasadas
FROM socio s
JOIN persona p ON s.run_persona = p.run
JOIN pago_cuota pc ON s.id_socio = pc.id_socio
WHERE pc.monto_pagado < (pc.monto_base + pc.monto_adicional)
GROUP BY p.nombre_completo, p.run, s.codigo_sucursal_base;


-- 4) Listado de beneficiarios-hijos que cumplen 29 años
CREATE OR REPLACE VIEW vista_beneficiarios_29_anios AS
SELECT 
    p_hijo.run AS run_beneficiario,
    p_hijo.nombre_completo AS nombre_beneficiario,
    p_hijo.email AS correo_beneficiario,
    p_hijo.telefono_celular AS telefono_beneficiario,
    p_titular.run AS run_titular,
    p_titular.nombre_completo AS nombre_titular
FROM relacion_socio rs
JOIN socio s_hijo ON rs.id_socio_dependiente = s_hijo.id_socio
JOIN persona p_hijo ON s_hijo.run_persona = p_hijo.run
JOIN socio s_titular ON rs.id_socio_titular = s_titular.id_socio
JOIN persona p_titular ON s_titular.run_persona = p_titular.run
WHERE rs.parentesco = 'hijo'
  AND (EXTRACT(YEAR FROM CURRENT_DATE) - EXTRACT(YEAR FROM p_hijo.fecha_nacimiento)) = 28;


-- 5) Reporte año 2025 de todas las sucursales ordenado por ingreso
CREATE OR REPLACE VIEW vista_reporte_sucursales_2025 AS
SELECT 
    s.nombre AS nombre_sucursal,
    p.nombre_completo AS gerente_a_cargo,
    SUM(pc.monto_pagado) AS ingresos_totales,
    (SUM(pc.monto_pagado) * 100) / (SELECT SUM(monto_pagado) FROM pago_cuota WHERE EXTRACT(YEAR FROM fecha_pago) = 2025) AS porcentaje_total
FROM sucursal s
JOIN socio so ON s.codigo_sucursal = so.codigo_sucursal_base
JOIN pago_cuota pc ON so.id_socio = pc.id_socio
JOIN persona_cargo pcargo ON s.codigo_sucursal = pcargo.codigo_sucursal
JOIN cargo c ON pcargo.id_cargo = c.id_cargo
JOIN persona p ON pcargo.run_persona = p.run
WHERE c.nombre = 'gerente'
  AND EXTRACT(YEAR FROM pc.fecha_pago) = 2025
GROUP BY s.nombre, p.nombre_completo
ORDER BY ingresos_totales DESC;
