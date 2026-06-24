-- Guardamos el resultado en finbeneficiario.txt
\o finbeneficiario.txt

SELECT 
    pb.run AS beneficiario_run,
    pb.nombre_completo AS beneficiario_nombre,
    pb.email AS beneficiario_correo,
    pb.telefono_celular AS beneficiario_telefono,
    pt.run AS titular_run,
    pt.nombre_completo AS titular_nombre,
    pt.email AS titular_correo,
    pt.telefono_celular AS titular_telefono
FROM public.relacion_socio rs
JOIN public.socio sb ON rs.id_socio_dependiente = sb.id_socio
JOIN public.socio st ON rs.id_socio_titular = st.id_socio
JOIN public.persona pb ON sb.run_persona = pb.run
JOIN public.persona pt ON st.run_persona = pt.run
JOIN public.membresia m ON st.id_socio = m.id_socio
WHERE rs.parentesco = 'hijo'
  AND AGE(
      m.fecha_fin, 
      CASE 
          WHEN pb.fecha_nacimiento IS NOT NULL THEN pb.fecha_nacimiento 
          ELSE (SELECT n.nac FROM public.naci n WHERE n.run = pb.run LIMIT 1) 
      END
  ) >= INTERVAL '29 years'
ORDER BY pt.nombre_completo, pb.nombre_completo;

\o
