# API-EMAIL-SMTP

En nuestra Instituciòn Bancaria Banco Central de Venezuela disponemos de técnica para uso en entornos controlados y/o con fines de evaluación.

IMPORTANTE — ética, legalidad y uso responsable
- Este repositorio muestra cómo construir un sistema de envío masivo. No debe usarse para enviar correo no solicitado (spam) ni para suplantar identidades. El propietario del repositorio no respalda usos ilegales, fraudulentos ni intentos de suplantación de identidad.
- No puedo ayudar a facilitar "spoofing" (suplantación) ni a proporcionar ejemplos cuyo propósito sea engañar a terceros o hacerse pasar por instituciones o bancos. Por tanto, no habrá ejemplos de spoofing ni instrucciones para ello.
- Si necesitas integrar con servicios legítimos (por ejemplo, enviar correos desde un dominio que controlas), asegúrate de tener autorización para ese dominio antes de publicar registros DNS o enviar correos.

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
- El proyecto emplea la función mail() de PHP para enviar correos. Está pensado como punto de partida y no como solución de producción lista para envío masivo real.
- El admin genera API keys y puede activarlas manualmente tras verificación externa. El usuario que tenga una API key puede usar el formulario web o la API JSON para enviar correos.

Actualizaciones realizadas
- Se ha añadido soporte para normalizar dominios IDN (Punycode) cuando sea posible, y se codifica el display-name del From usando mb_encode_mimeheader si está disponible.
- Se han añadido sanitizaciones para evitar header injection y normalización de listas CC/BCC.

Autenticación de correo y entregabilidad (SPF / DKIM / DMARC)
Para que tus correos tengan mejores posibilidades de entrega y no sean marcados como spam, configura las siguientes cosas en el dominio que posees:

1) SPF (registro TXT en DNS)
- Ejemplo para un VPS con IP 198.51.100.12:
  tu-dominio.com. IN TXT "v=spf1 ip4:198.51.100.12 -all"
- Esto indica a los receptores que sólo la IP indicada está autorizada a enviar en nombre del dominio.

2) DKIM (firma de correo)
- Lo más robusto es instalar OpenDKIM en el servidor MTA (p. ej. Postfix) y configurar un selector. Publica la clave pública en DNS como: selector1._domainkey.tu-dominio.com IN TXT "v=DKIM1; k=rsa; p=MIIBI..."
- Postfix + OpenDKIM firmará los mensajes salientes. Firmar en PHP es posible (hay bibliotecas) pero firmar en el MTA es más fiable.

3) DMARC (política)
- Ejemplo de registro TXT DMARC:
  _dmarc.tu-dominio.com. IN TXT "v=DMARC1; p=quarantine; rua=mailto:postmaster@tu-dominio.com; pct=100"
- Ajusta p=none/quarantine/reject según pruebas.

Requisitos previos para usar DKIM/SPF/DMARC
- Debes ser el propietario del dominio para publicar los registros DNS.
- Configura correctamente tu MTA (Postfix/Exim) y publica las claves DKIM en DNS.

Ejemplo de uso en PHP (formulario/API ya incluidos)
- El código en src/functions.php ahora soporta normalizar dominios IDN y codificar el From display-name.
- Asegúrate de que la extensión intl (para idn_to_ascii) y mbstring (para mb_encode_mimeheader) estén instaladas en PHP para mejores resultados.

Ejemplo en Python (envío vía SMTP y firma DKIM usando dkimpy)
- El siguiente fragmento muestra un envío legítimo desde un dominio que controlas. Requiere:
  - Tener acceso SMTP a un MTA autorizado para ese dominio.
  - Poseer la clave privada DKIM para firmar (no compartas esa clave).

```python
# Ejemplo de envío SMTP + firma DKIM (solo para uso legítimo y legal)
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import dkim

from_addr = 'noreply@tu-dominio.com'
to_addr = 'destinatario@example.com'
subject = 'Prueba DKIM'

# Construir mensaje
msg = MIMEMultipart('alternative')
msg['From'] = 'Empresa Ejemplo <{}>'.format(from_addr)
msg['To'] = to_addr
msg['Subject'] = subject
html = '<p>Hola — mensaje firmado con DKIM</p>'
part = MIMEText(html, 'html', 'utf-8')
msg.attach(part)

# Firma DKIM (requiere dkimpy y la clave privada)
selector = b'selector1'
domain = b'tu-dominio.com'
with open('dkim_private.key', 'rb') as f:
    private_key = f.read()

headers = [b"from", b"to", b"subject"]
sig = dkim.sign(msg.as_bytes(), selector, domain, private_key, include_headers=headers)

# Prepend signature and send via SMTP
raw = sig + msg.as_bytes()
with smtplib.SMTP('smtp.tu-mta.com', 587) as s:
    s.starttls()
    s.login('usuario', 'password')
    s.sendmail(from_addr, [to_addr], raw)
```

Advertencias sobre el ejemplo Python
- No utilices la firma DKIM con claves que no sean de tu dominio.
- No uses ejemplos para suplantar a terceros. El código es para administradores que gestionan dominios legítimos.

Mejoras recomendadas
- Implementar una cola y workers para envío asíncrono.
- Añadir límites y cuotas por API key.
- Añadir logs de envío y métricas.
- Restringir el panel admin con 2FA y/o IP allowlist.

Siguientes pasos
- Implementé el soporte IDN y codificación del nombre From en src/functions.php.
- Si quieres, puedo:
  - Actualizar src/api.php para aceptar adjuntos como URLs y descargarlos de forma segura.
  - Añadir ejemplo de configuración OpenDKIM + Postfix para Debian/Ubuntu (si me indicas el SO).
  - Implementar cola (Redis + worker PHP) para envío en background.

Si confirmas, aplico la(s) mejora(s) adicionales. Recuerda: no puedo ayudar a crear documentación o código para "spoofing" o suplantación; todas las instrucciones proporcionadas suponen control legítimo del dominio.
