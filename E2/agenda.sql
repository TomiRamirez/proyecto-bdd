-- Guardar resultado en agenda.txt
\o agenda.txt

-- Agenda de la sucursal Santa Cruz para la semana del 6 al 12 de abril 2026
-- Agrupa por dia, hora y lugar (reservas de socios + eventos)

SELECT 
    CASE EXTRACT(ISODOW FROM r.fecha_inicio)
        WHEN 1 THEN 'Lunes'
        WHEN 2 THEN 'Martes'
        WHEN 3 THEN 'Miercoles'
        WHEN 4 THEN 'Jueves'
        WHEN 5 THEN 'Viernes'
        WHEN 6 THEN 'Sabado'
        WHEN 7 THEN 'Domingo'
    END AS dia,
    CAST(r.fecha_inicio AS DATE) AS fecha,
    to_char(r.fecha_inicio, 'HH24:MI') AS hora,
    l.nombre AS lugar,
    p.nombre_completo AS evento_o_socio

FROM public.reserva r
JOIN public.lugar l ON r.codigo_lugar = l.codigo_lugar
JOIN public.sucursal s ON l.codigo_sucursal = s.codigo_sucursal
JOIN public.persona p ON r.run_reservante = p.run

WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
  AND CAST(r.fecha_inicio AS DATE) >= '2025-04-06'
  AND CAST(r.fecha_inicio AS DATE) <= '2025-04-12'

UNION ALL

SELECT 
    CASE EXTRACT(ISODOW FROM e.fecha_evento)
        WHEN 1 THEN 'Lunes'
        WHEN 2 THEN 'Martes'
        WHEN 3 THEN 'Miercoles'
        WHEN 4 THEN 'Jueves'
        WHEN 5 THEN 'Viernes'
        WHEN 6 THEN 'Sabado'
        WHEN 7 THEN 'Domingo'
    END AS dia,
    e.fecha_evento AS fecha,
    '00:00' AS hora,
    l.nombre AS lugar,
    e.nombre AS evento_o_socio

FROM public.evento e
JOIN public.lugar l ON e.codigo_lugar = l.codigo_lugar
JOIN public.sucursal s ON l.codigo_sucursal = s.codigo_sucursal

WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
  AND e.fecha_evento >= '2025-04-06'
  AND e.fecha_evento <= '2025-04-12'

ORDER BY fecha, hora, lugar;

\o