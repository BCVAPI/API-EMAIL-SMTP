# API-EMAIL-SMTP

Sistema de envío masivo de correos (bulk) en PHP usando la función mail() (sin SMTP).

IMPORTANTE: El uso de este sistema debe cumplir con las leyes anti-spam y las políticas de los proveedores de correo (CAN-SPAM, GDPR, etc.). Sólo envíe correos a destinatarios con consentimiento explícito. El mantenedor del repositorio no se hace responsable de usos indebidos.

Contenido del repositorio:
- src/index.php        -> Interfaz web (formulario tipo webmail)
- src/send.php         -> Procesa envíos desde formulario
- src/api.php          -> API JSON para envíos (requiere API key activa)
- src/admin.php        -> Panel admin para generar/activar API keys (login con password del config)
- src/config.php       -> Configuración (direcciones BTC/USDT, admin password — cámbialo)
- src/functions.php    -> Funciones auxiliares (envío con adjuntos usando mail())
- src/keys.json        -> Almacén simple de API keys y estado
- assets/style.css     -> Estilos CSS corporativos sencillos
- README.md            -> Documentación e instrucciones de despliegue
- .gitignore

Instalación rápida en VPS (resumen):
1) Copia los archivos a tu VPS (por ejemplo en /var/www/html/email/). Revisa src/config.php y pon las direcciones BTC/USDT y cambia el ADMIN_PASSWORD.
2) Asegura permisos: `chown -R www-data:www-data /var/www/html/email` y `chmod 700 src/keys.json`.
3) Asegura PHP mail(): configura sendmail o postfix local en la VPS para entregar correos.
4) Accede a /src/admin.php, genera una API key y sigue el proceso de pago (ver README).

Lee README.md para detalles de configuración, seguridad y recomendaciones de envío responsable.
