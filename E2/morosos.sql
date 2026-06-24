-- Guardamos el  resultado en morosos.txt
\o morosos.txt

SELECT 
    p.nombre_completo,
    p.run,
    suc.nombre AS sucursal,
    s.tipo_socio,
    COUNT(c.id_cuota) AS cuotas_atrasadas,
    SUM(c.monto_total) AS monto_adeudado
FROM public.cuota c
JOIN public.membresia m ON c.id_membresia = m.id_socio
JOIN public.socio s ON m.id_socio = s.id_socio
JOIN public.persona p ON s.run_persona = p.run
JOIN public.sucursal suc ON s.codigo_sucursal_base = suc.codigo_sucursal
WHERE c.estado = 'atrasado'
GROUP BY p.nombre_completo, p.run, suc.nombre, s.tipo_socio
ORDER BY suc.nombre, monto_adeudado DESC, p.nombre_completo;

\o
