# API-EMAIL-SMTP

Sistema de envío masivo de correos (bulk) en PHP usando la función mail() (sin SMTP). Esta implementación es una demostración técnica para uso en entornos controlados y/o con fines de evaluación.

IMPORTANTE — ética, legalidad y uso responsable
- Este repositorio muestra cómo construir un sistema de envío masivo. No debe usarse para enviar correo no solicitado (spam). El propietario del repositorio no respalda usos ilegales, fraudulentos ni intentos de suplantación de identidad.
- No puedo ni debo ayudar a hacer afirmaciones engañosas sobre acceso a APIs de terceros (por ejemplo, banca) ni a activar servicios a nombre de dominios que no posees. Si ves referencias a dominios institucionales (por ejemplo "bcv.org.ve"), debes ser el propietario autorizado del dominio para poder configurar DKIM/SPF/DMARC en él. No uses este software para suplantar a instituciones.

Qué incluye el repositorio
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

Resumen de funcionamiento
- El proyecto es un sistema básico que emplea la función mail() de PHP para enviar correos. Está pensado como punto de partida y no como solución de producción lista para envío masivo real.
- El admin genera API keys y las activa manualmente tras verificación externa (pago/validación). El usuario que tenga una API key puede usar el formulario web o la API JSON para enviar correos.

Cambios solicitados por el usuario
- Se ha eliminado cualquier afirmación en el README que indicara que la persona debe pagar para ver/usar el sistema. El repositorio es una demostración técnica: cualquiera puede revisar cómo funciona sin necesidad de pagar.

Entregabilidad, dominios y autenticación de correo (SPF/DKIM/DMARC)
- Para buena entregabilidad debes configurar SPF, DKIM y DMARC en el dominio desde el que envías correos (por ejemplo, tu-dominio.com). No puedo activar registros DNS ni firmar correos por dominios que no posees.
- Instrucciones generales (ejemplo, reemplaza con tus valores):
  1) SPF (registro TXT en DNS):
     "v=spf1 ip4:TU_IP_DEL_VPS -all"
  2) DKIM: instalar y configurar una herramienta de firma (p. ej. OpenDKIM) en el servidor MTA y publicar la clave pública en DNS como TXT: `selector._domainkey.tu-dominio.com`.
  3) DMARC (registro TXT en DNS):
     "v=DMARC1; p=quarantine; rua=mailto:postmaster@tu-dominio.com; pct=100"
- Sólo si eres el propietario del dominio puedes publicar esos registros. No publiques ni intentes usar registros en dominios de terceros (por ejemplo, bcv.org.ve) sin autorización expresa.

Soporte de nombres internacionales (IDN / Punycode) y encoding en FROM
- El código de ejemplo puede ser mejorado para soportar direcciones y nombres con caracteres internacionales mediante la extensión PHP intl (función idn_to_ascii) y codificación adecuada de cabeceras (RFC 2047) para nombres con caracteres no ASCII.
- Recomendación técnica (implementación opcional):
  - Usar idn_to_ascii() para normalizar dominios IDN cuando construyas direcciones de correo.
  - Encodar nombres con mb_encode_mimeheader() antes de colocarlos en el header "From:".

Seguridad y prohibiciones
- No uses este código para suplantar identidades, instituciones públicas, bancos u otros servicios.
- No debo ayudar a crear documentación ni artefactos que induzcan a error sobre acceso a APIs bancarias o servicios privados. Si necesitas integrar un servicio bancario real, hazlo siempre con contratos y credenciales oficiales y te puedo ayudar con integración técnica legítima una vez demuestres autorización.

Mejoras recomendadas (para producción)
- Mover envío a una cola (Redis, RabbitMQ, o cron + worker) para evitar timeouts y mejorar control de reintentos.
- Implementar límites y cuotas por API key y trazabilidad (logs, métricas).
- Añadir validaciones robustas para evitar header injection y controlar tipos/tamaños de adjuntos.
- Integrar verificación de pago con un proveedor confiable si vas a vender el servicio (webhooks, confirmación de chain), pero evita automatizar activaciones sin medidas anti-fraude.
- Configurar DKIM/ SPF/ DMARC en el dominio que controles y utilizar un MTA (Postfix/Exim) configurado correctamente para entregar.

Siguientes pasos que puedo hacer por ti
- Actualizar el README con instrucciones más detalladas para configurar SPF/DKIM/DMARC para tu propio dominio.
- Añadir soporte en el código para punycode/IDN y codificación del nombre en From.
- Implementar cola de envío y soporte de adjuntos en la API.

Si quieres que actualice el README con instrucciones paso a paso para configurar DKIM/SPF/DMARC (con ejemplos de registros DNS y comandos para OpenDKIM/Postfix), dime qué dominio VAS A USAR (debes ser el propietario) o elige "dominio-ejemplo.com" para que ponga ejemplos genéricos. No puedo ayudar a publicar o afirmar poseer o controlar "bcv.org.ve" ni a documentar usos que simulen acceso a APIs bancarias sin autorización.
