-- Guardamos el resultado en ingresoporsucursal.txt
\o ingresoporsucursal.txt

WITH ingresos AS (
    -- Ingresos por membresias 2025
    SELECT 
        s.codigo_sucursal_base AS cod_suc,
        SUM(pc.monto_pagado) AS monto
    FROM public.pago_cuota pc
    JOIN public.socio s ON pc.id_socio = s.id_socio
    WHERE EXTRACT(YEAR FROM pc.fecha_pago) = 2025
    GROUP BY s.codigo_sucursal_base

    UNION ALL

    -- Ingresos por reservas 2025
    SELECT 
        l.codigo_sucursal,
        SUM(pr.monto)
    FROM public.pago_reserva pr
    JOIN public.reserva r ON pr.codigo_reserva = r.codigo_reserva
    JOIN public.lugar l ON r.codigo_lugar = l.codigo_lugar
    WHERE EXTRACT(YEAR FROM pr.fecha_pago) = 2025
    GROUP BY l.codigo_sucursal

    UNION ALL

    -- Ingresos por eventos 2025
    SELECT 
        e.codigo_sucursal,
        SUM(pe.monto)
    FROM public.pago_evento pe
    JOIN public.evento e ON pe.codigo_evento = e.codigo_evento
    WHERE EXTRACT(YEAR FROM pe.fecha_pago) = 2025
    GROUP BY e.codigo_sucursal
),
totales_sucursal AS (
    SELECT cod_suc, SUM(monto) AS ingreso_total
    FROM ingresos
    GROUP BY cod_suc
),
total_club AS (
    SELECT SUM(ingreso_total) AS total FROM totales_sucursal
)
SELECT 
    suc.nombre AS sucursal,
    CASE 
        WHEN pg.nombre_completo IS NOT NULL THEN pg.nombre_completo 
        ELSE 'Sin gerente' 
    END AS gerente,
    ts.ingreso_total,
    ROUND((ts.ingreso_total * 100.0 / tc.total), 2) AS porcentaje_total
FROM totales_sucursal ts
JOIN public.sucursal suc ON ts.cod_suc = suc.codigo_sucursal
CROSS JOIN total_club tc
LEFT JOIN public.persona_cargo pcg ON pcg.codigo_sucursal = suc.codigo_sucursal
    AND pcg.id_cargo = (SELECT id_cargo FROM public.cargo WHERE nombre = 'Gerente' LIMIT 1)
    AND (pcg.fecha_termino IS NULL OR pcg.fecha_termino >= '2025-12-31')
LEFT JOIN public.persona pg ON pcg.run_persona = pg.run
ORDER BY ts.ingreso_total DESC;

\o
