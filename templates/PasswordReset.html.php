<?php
/**
 * HTML Email Template for resetting a user password (generated by GitHub Copilot)
 *
 * @var string $receiver
 * @var string $targetLink
 * @var DateTimeInterface $validUntil
 */
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Passwort zur&uuml;cksetzen</title>
  <style>
    <?php echo file_get_contents(__DIR__ . '/assets/style.css'); ?>
  </style>
</head>
<body>
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td align="center">
        <div class="container">
          <!-- Header -->
          <div class="header">
            <img src="https://www.wildeligabremen.de/wp-content/uploads/2023/05/cropped-Logo-mit-Schrift_30-Jahre-Kopie_2-e1683381765583.jpg" alt="Wilde Liga Bremen">
          </div>
          <!-- Content -->
          <div class="content">
            <h1>Passwort zur&uuml;cksetzen</h1>
            <p>Hey <?php echo htmlentities($receiver); ?>, nutze den folgenden Link um ein neues Passwort zu vergeben.</p>
            <a href="<?php echo $targetLink; ?>" class="cta-button">Neues Passwort setzen</a>
          </div>
          <!-- Footer -->
          <div class="footer">
            <p>Der Link ist g&uuml;ltig bis: <?php echo $validUntil->format('d.m.Y H:i'); ?> Uhr.</p>
            <p>Bitte leite diese E-Mail nicht an eine andere Person weiter.</p>
            <p>Wenn du diese E-Mail wiederholt bekommst ohne sie selbst angefordert zu haben, melde dich bitte beim Admin-Team.</p>
          </div>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
