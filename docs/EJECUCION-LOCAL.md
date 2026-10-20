# ToyCommerce en minikeds_dev2

Descargado de https://github.com/MiguelAHR/ToyCommerce, rama main, el2026-10-18. Copia local independiente del Mini Kids anterior y de su VPS.

- Carpeta: `C:/xampp/htdocs/minikeds_dev2`; aplicación dentro de `wordpress`.
- Sitio: http://localhost:8082/wordpress/ . La raíz http://localhost:8082/ redirige a esa dirección.
- Administrador: http://localhost:8082/wordpress/wp-admin/ . Usuario `admin`; contraseña nueva privada en `local-private/access-local.txt`. La contraseña documentada por el repositorio no coincidía con el volcado y se reemplazó solo en esta base local.
- MySQL: base independiente `minikeds_dev2`, 52 tablas importadas desde database/wordpress-sanitized.sql. Conexión en local-private/config.json; wp-config.php carga ese archivo privado. No publicar ni compartirlo.
- Tema Blocksy y plugins del repositorio conservados. Se añadieron router/arranque local y mu-plugin de aislamiento: correo suprimido y solo transferencia/contraentrega disponibles, sin cobros externos.
- URLs antiguas migradas preservando datos serializados. Se conserva /wordpress para enlaces que el código original tiene escritos con esa subcarpeta.
- La BD trae17 productos publicados aunque el README describe7. Se mantuvo su catálogo importado.

## Volver a iniciar

Encender MySQL de XAMPP. En PowerShell, dentro del proyecto:

```powershell
powershell -ExecutionPolicy Bypass -File tools/start-local.ps1
```

Si8082 ya está escuchando, el servidor está encendido; no arrancar otro. La sesión inicial se ejecuta sin ventana, vinculada exclusivamente a localhost. Logs en tools/server-local*.log.

No ejecutar otra vez setup-local/configure-local/finalize-local sobre contenido editado. Son herramientas de preparación inicial, no mantenimiento. El arranque normal no requiere importar SQL de nuevo.

Se comprobó portada con16 imágenes y ninguna rota, catálogo, ficha de producto y acceso administrativo. El router bloquea SQL, configuración privada y rutas ajenas a WordPress. No se modificaron WordPress base, temas distribuidos ni plugins de terceros. No se publicó esta copia ni se subieron cambios a GitHub.
