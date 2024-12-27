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
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    img {
      display: block;
      max-width: 100%;
      height: auto;
    }
    .container {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }
    .header {
      text-align: center;
      padding: 40px 0;
    }
    .content {
      background-color: #ffffff;
      padding: 20px;
      text-align: center;
    }
    .cta-button {
      background-color: #28a745;
      color: #ffffff;
      padding: 15px 25px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 5px;
      display: inline-block;
      margin-top: 20px;
    }
    .footer {
      text-align: center;
      padding: 20px;
      font-size: 12px;
      color: #888888;
    }
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
            <p>Hey Alice! Nutze den Link um ein neues Passwort zu vergeben.</p>
            <a href="https://www.example.com" class="cta-button">Neues Passwort setzen</a>
          </div>
          <!-- Footer -->
          <div class="footer">
            <p>Der Link ist g&uuml;ltig bis: 31.12.2024 15:30 Uhr.</p>
            <p>Bitte leite diese E-Mail nicht an eine andere Person weiter.</p>
            <p>Wenn du keine Passwort R&uuml;cksetzung angefordert hast, kannst du diese E-Mail bedenkenlos l&ouml;schen.</p>
          </div>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
