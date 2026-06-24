-- Guardamos el resultado en ingresomensual.txt
\o ingresomensual.txt


SELECT
    tipo_ingreso,
    concepto,
    SUM(monto) AS total_monto

FROM (


    --  Cuotas de membresias pagadas este mes por socios de Santa Cruz
    SELECT
        'Ingresos Recibidos'    AS tipo_ingreso,
        'Membresias'            AS concepto,
        pc.monto_pagado         AS monto
    FROM public.pago_cuota pc
    JOIN public.socio s ON pc.id_socio = s.id_socio
    WHERE s.codigo_sucursal_base = 'SANTACRUZ'
      AND EXTRACT(MONTH FROM pc.fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR  FROM pc.fecha_pago) = EXTRACT(YEAR  FROM CURRENT_DATE)

    UNION ALL

    --  Reservas ejecutadas pagadas este mes en Santa Cruz
    SELECT
        'Ingresos Recibidos',
        'Reservas ejecutadas',
        pr.monto
    FROM public.pago_reserva pr
    JOIN public.reserva r  ON pr.codigo_reserva = r.codigo_reserva
    JOIN public.lugar l    ON r.codigo_lugar    = l.codigo_lugar
    JOIN public.sucursal s ON l.codigo_sucursal = s.codigo_sucursal
    WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
      AND r.estado = 'ejecutada'
      AND EXTRACT(MONTH FROM pr.fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR  FROM pr.fecha_pago) = EXTRACT(YEAR  FROM CURRENT_DATE)

    UNION ALL

    -- Eventos pagados este mes en Santa Cruz
    SELECT
        'Ingresos Recibidos',
        'Eventos',
        pe.monto
    FROM public.pago_evento pe
    JOIN public.evento e ON pe.codigo_evento = e.codigo_evento
    JOIN public.sucursal s ON e.codigo_sucursal = s.codigo_sucursal
    WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
      AND EXTRACT(MONTH FROM pe.fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR  FROM pe.fecha_pago) = EXTRACT(YEAR  FROM CURRENT_DATE)

    UNION ALL


    -- Cuotas del mes actual aun no pagadas por socios de Santa Cruz
    SELECT
        'Ingresos Esperados',
        'Membresias',
        c.monto_total
    FROM public.cuota c
    JOIN public.membresia m ON c.id_membresia = m.id_socio
    JOIN public.socio s     ON m.id_socio     = s.id_socio
    WHERE s.codigo_sucursal_base = 'SANTACRUZ'
      AND c.mes = EXTRACT(MONTH FROM CURRENT_DATE)
      AND c.estado <> 'pagada'
      -- Solo si no hay pago registrado para ese socio este mes
      AND NOT EXISTS (
          SELECT 1 FROM public.pago_cuota pc
          WHERE pc.id_socio = s.id_socio
            AND EXTRACT(MONTH FROM pc.fecha_pago) = EXTRACT(MONTH FROM CURRENT_DATE)
            AND EXTRACT(YEAR  FROM pc.fecha_pago) = EXTRACT(YEAR  FROM CURRENT_DATE)
      )

    UNION ALL

    -- Reservas en estado "reservada" este mes en Santa Cruz (aun no ejecutadas ni pagadas)
    SELECT
        'Ingresos Esperados',
        'Reservas ejecutadas',
        CASE 
            WHEN pl.monto IS NOT NULL THEN pl.monto 
            ELSE 0 
        END
    FROM public.reserva r
    JOIN public.lugar l    ON r.codigo_lugar    = l.codigo_lugar
    JOIN public.sucursal s ON l.codigo_sucursal = s.codigo_sucursal
    LEFT JOIN public.precio_lugar pl ON l.codigo_lugar = pl.codigo_lugar
    WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
      AND r.estado = 'reservada'
      AND EXTRACT(MONTH FROM r.fecha_inicio) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR  FROM r.fecha_inicio) = EXTRACT(YEAR  FROM CURRENT_DATE)
      AND NOT EXISTS (
          SELECT 1 FROM public.pago_reserva pr
          WHERE pr.codigo_reserva = r.codigo_reserva
      )

    UNION ALL

    -- Eventos de este mes en Santa Cruz sin pago registrado
    SELECT
        'Ingresos Esperados',
        'Eventos',
        0   -- sin precio base definido en la tabla evento
    FROM public.evento e
    JOIN public.sucursal s ON e.codigo_sucursal = s.codigo_sucursal
    WHERE UPPER(TRIM(s.nombre)) = UPPER(TRIM('Santa Cruz'))
      AND EXTRACT(MONTH FROM e.fecha_evento) = EXTRACT(MONTH FROM CURRENT_DATE)
      AND EXTRACT(YEAR  FROM e.fecha_evento) = EXTRACT(YEAR  FROM CURRENT_DATE)
      AND NOT EXISTS (
          SELECT 1 FROM public.pago_evento pe
          WHERE pe.codigo_evento = e.codigo_evento
      )

) AS datos

GROUP BY tipo_ingreso, concepto
ORDER BY tipo_ingreso DESC, concepto;

\o