<?php
// Página pública: formulario tipo webmail sencillo
$config = include __DIR__.'/config.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WebMail - Envío Masivo</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="container">
    <header><h1>WebMail - Envío Masivo</h1></header>
    <section class="card">
      <form action="/src/send.php" method="post" enctype="multipart/form-data">
        <label>FROM email
          <input type="email" name="from_email" required placeholder="desde@ejemplo.com">
        </label>
        <label>FROM name
          <input type="text" name="from_name" required placeholder="Empresa S.A.">
        </label>
        <label>TO (emails separados por coma o salto de línea)
          <textarea name="to_list" rows="4" required placeholder="cliente1@ejemplo.com, cliente2@ejemplo.com"></textarea>
        </label>
        <label>CC
          <input type="text" name="cc" placeholder="cc@ejemplo.com">
        </label>
        <label>BCC
          <input type="text" name="bcc" placeholder="bcc@ejemplo.com">
        </label>
        <label>Asunto
          <input type="text" name="subject" required>
        </label>
        <label>Mensaje (HTML)
          <textarea name="message" rows="10" required></textarea>
        </label>
        <label>Adjuntos (puedes subir varios)
          <input type="file" name="attachments[]" multiple>
        </label>
        <label>Llave de seguridad (API key)
          <input type="text" name="api_key" required placeholder="Tu API key">
        </label>
        <div class="actions">
          <button type="submit">Enviar masivo</button>
        </div>
      </form>
    </section>
    <footer>
      <p>Para obtener una API key y activar acceso a la API, visita /src/admin.php</p>
    </footer>
  </div>
</body>
</html>
