<?php
// Panel de administración básico para generar y activar API keys tras pago manual
require __DIR__.'/functions.php';
$config = load_config();
$apiKeysFile = $config['API_KEYS_FILE'];

session_start();

$action = $_POST['action'] ?? null;
if ($action === 'login'){
    $pw = $_POST['password'] ?? '';
    if ($pw === $config['ADMIN_PASSWORD']){
        $_SESSION['admin'] = true;
    } else {
        $error = 'Password incorrecta';
    }
}

if (!empty($_GET['logout'])){ session_destroy(); header('Location: admin.php'); exit; }

if (empty($_SESSION['admin'])){
    // mostrar login
    ?>
    <!doctype html><html><head><meta charset="utf-8"><title>Admin - Login</title></head><body>
    <h2>Login administrador</h2>
    <?php if (!empty($error)) echo '<p style="color:red">'.htmlspecialchars($error).'</p>'; ?>
    <form method="post">
      <input type="hidden" name="action" value="login">
      <label>Password: <input type="password" name="password"></label>
      <button type="submit">Entrar</button>
    </form>
    </body></html>
    <?php
    exit;
}

// Admin actions: generar key, activar/desactivar
$keys = load_keys($apiKeysFile);
if ($action === 'generate'){
    $label = $_POST['label'] ?? 'cliente';
    $new = generate_api_key(48);
    $keys[$new] = ['label'=>$label, 'active'=>false, 'created'=>date('c')];
    save_keys($apiKeysFile, $keys);
    $notice = "API key generada: $new (DESACTIVADA — activa tras verificar pago)";
}

if ($action === 'toggle'){
    $k = $_POST['key'] ?? '';
    if (isset($keys[$k])){
        $keys[$k]['active'] = !($keys[$k]['active']);
        save_keys($apiKeysFile, $keys);
        $notice = 'Estado cambiado.';
    }
}

?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Admin - API Keys</title></head>
<body>
  <h1>Panel Admin - API Keys</h1>
  <p><a href="?logout=1">Cerrar sesión</a></p>
  <?php if (!empty($notice)) echo '<p style="color:green">'.htmlspecialchars($notice).'</p>'; ?>
  <section>
    <h2>Instrucciones de pago</h2>
    <p>Para activar una API key, el cliente debe pagar <strong><?php echo $config['PRICE_USD']; ?> USD</strong> en BTC o USDT a las siguientes direcciones:</p>
    <ul>
      <li>BTC: <code><?php echo htmlspecialchars($config['BTC_ADDRESS']); ?></code></li>
      <li>USDT: <code><?php echo htmlspecialchars($config['USDT_ADDRESS']); ?></code></li>
    </ul>
    <p>Proceso recomendado: El cliente envía comprobante de pago y el administrador valida la transacción en la blockchain. Si es correcta, activa la API key aquí (columna "Activar").</p>
  </section>

  <section>
    <h2>Generar nueva API Key</h2>
    <form method="post">
      <input type="hidden" name="action" value="generate">
      <label>Etiqueta (cliente): <input type="text" name="label" value="cliente"></label>
      <button type="submit">Generar</button>
    </form>
  </section>

  <section>
    <h2>API Keys</h2>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr><th>Key</th><th>Etiqueta</th><th>Creada</th><th>Activa</th><th>Acciones</th></tr>
      <?php foreach ($keys as $k => $meta): ?>
        <tr>
          <td><code><?php echo htmlspecialchars($k); ?></code></td>
          <td><?php echo htmlspecialchars($meta['label'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($meta['created'] ?? ''); ?></td>
          <td><?php echo $meta['active'] ? 'SI' : 'NO'; ?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="key" value="<?php echo htmlspecialchars($k); ?>">
              <button type="submit"><?php echo $meta['active'] ? 'Desactivar' : 'Activar'; ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>

</body>
</html>
