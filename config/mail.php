<?php
require_once __DIR__ . '/bootstrap.php';
if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('MAIL_HOST', 'mail.kflrobotics.com');
define('MAIL_PORT', 465); // SMTPS
define('MAIL_USERNAME', 'no-reply@kflrobotics.com');
define('MAIL_FROM', 'no-reply@kflrobotics.com');
define('MAIL_FROM_NAME', 'KFL Robotics');

function sendMail(string $to, string $subject, string $body): bool
{
    if (!class_exists(PHPMailer::class)) {
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password = env('MAIL_PASSWORD');
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465 için

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function buildKflEmailHtml(
    string $title,
    string $name,
    string $contentText,
    ?string $ctaText = null,
    ?string $ctaUrl = null
): string {

    $safeTitle   = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeContent = nl2br(htmlspecialchars($contentText, ENT_QUOTES, 'UTF-8'));

    $btnHtml = '';
    if ($ctaText && $ctaUrl) {
        $btnHtml = '
        <tr>
          <td align="center" style="padding:18px 0 6px;">
            <a href="'.htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8').'"
               target="_blank"
               style="
                 display:inline-block;
                 padding:12px 26px;
                 border-radius:12px;
                 background:#14b8a6;
                 color:#ffffff;
                 font-weight:800;
                 text-decoration:none;
                 font-family:Arial,Helvetica,sans-serif;
                 box-shadow:0 10px 22px rgba(20,184,166,0.25);
               ">
              '.htmlspecialchars($ctaText, ENT_QUOTES, 'UTF-8').'
            </a>
          </td>
        </tr>';
    }

    return '
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>'.$safeTitle.'</title>
</head>

<body style="margin:0;padding:0;background:#f6f8fb;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f8fb;">
<tr>
<td align="center" style="padding:32px 12px;">

<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;">
  <!-- LOGO -->
  <tr>
    <td align="center" style="padding:10px 0 18px;">
      <div style="
        font-family:Arial,Helvetica,sans-serif;
        font-weight:900;
        letter-spacing:3px;
        font-size:28px;
        color:#14b8a6;
      ">
        KFL ROBOTICS
      </div>
    </td>
  </tr>

  <!-- CARD -->
  <tr>
    <td style="
      background:#ffffff;
      border-radius:18px;
      border:1px solid rgba(15,23,42,0.10);
      box-shadow:0 18px 50px rgba(2,6,23,0.10);
    ">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:26px 26px 12px;">
            <div style="
              font-family:Arial,Helvetica,sans-serif;
              font-size:20px;
              font-weight:900;
              color:#0f172a;
            ">
              '.$safeTitle.'
            </div>
          </td>
        </tr>

        <tr>
          <td style="padding:0 26px;">
            <div style="height:1px;background:rgba(15,23,42,0.08);"></div>
          </td>
        </tr>

        <tr>
          <td align="center" style="padding:10px 26px 22px;">
            <div style="
              font-family:Arial,Helvetica,sans-serif;
              margin:10px 0 12px;
              font-size:16px;
              font-weight:800;
              color:#0f172a;
            ">
              Merhaba, '.$safeName.'.
            </div>

            <div style="
              font-family:Arial,Helvetica,sans-serif;
              max-width:520px;
              font-size:15px;
              line-height:1.75;
              color:#475569;
            ">
              '.$safeContent.'
            </div>
          </td>
        </tr>

        '.$btnHtml.'
      </table>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td align="center" style="
      padding:16px 10px 0;
      font-family:Arial,Helvetica,sans-serif;
      font-size:12px;
      color:#64748b;
    ">
      © '.date('Y').' KFL Robotics • Bu e-posta otomatik gönderilmiştir. Lütfen yanıt vermeyin.
    </td>
  </tr>
</table>

</td>
</tr>
</table>
</body>
</html>';
}




function sendKflMail(
    string $to,
    string $subject,
    string $title,
    string $name,
    string $contentText,
    ?string $ctaText = null,
    ?string $ctaUrl = null
): bool {
    $html = buildKflEmailHtml($title, $name, $contentText, $ctaText, $ctaUrl);
    return sendMail($to, $subject, $html);
}
