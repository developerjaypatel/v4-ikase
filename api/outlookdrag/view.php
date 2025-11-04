<?php
include_once('config.php');

$id = intval($_GET['id'] ?? 0);

// Fetch the email record
$stmt = $conn->prepare("SELECT * FROM emails WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    die("Email not found");
}

$row = $res->fetch_assoc();
$messageRaw = $row['message'];

// Function to decode MIME base64 parts
function decodeMimeMessage($raw)
{
    $text = '';
    $html = '';

    if (preg_match_all('/Content-Type:\s*([^;]+).*?Content-Transfer-Encoding:\s*(.+?)\s+(.+?)(?=(--|\Z))/si', $raw, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $part) {
            $contentType = strtolower(trim($part[1]));
            $encoding = strtolower(trim($part[2]));
            $content = trim($part[3]);

            if ($encoding === 'base64') {
                $decoded = base64_decode($content);
            } elseif ($encoding === 'quoted-printable') {
                $decoded = quoted_printable_decode($content);
            } else {
                $decoded = $content;
            }

            if (strpos($contentType, 'text/html') !== false) {
                $html = $decoded;
            } elseif (strpos($contentType, 'text/plain') !== false) {
                $text = $decoded;
            }
        }
    } else {
        $text = $raw;
    }

    // Decode HTML entities for plain text
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return ['html' => $html, 'text' => $text];
}

$decoded = decodeMimeMessage($messageRaw);
$htmlMessage = $decoded['html'];
$plainMessage = $decoded['text'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>View Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .email-body {
            overflow-x: auto;
            max-height: 600px;
            background: #fff;
        }

        .email-body img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Subject:</h5>
                <p><strong><?= htmlspecialchars($row['subject']) ?></strong></p>

                <?php if (!empty($row['sender'])): ?>
                    <p><strong>From:</strong> <?= htmlspecialchars($row['sender']) ?></p>
                <?php endif; ?>

                <?php if (!empty($row['recipient'])): ?>
                    <p><strong>To:</strong> <?= htmlspecialchars($row['recipient']) ?></p>
                <?php endif; ?>

                <h6>Message:</h6>

                <div class="mb-2">
                    <label for="viewMode" class="form-label">View as:</label>
                    <select id="viewMode" class="form-select" style="width: 200px;">
                        <option value="plain" selected>Plain Text</option>
                        <option value="html">HTML</option>
                    </select>
                </div>

                <div class="border p-3 email-body">
                    <div id="plainTextView"><pre><?= htmlspecialchars($plainMessage) ?></pre></div>
                    <div id="htmlView" style="display: none;"><?= $htmlMessage ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropdown = document.getElementById('viewMode');
        const plainView = document.getElementById('plainTextView');
        const htmlView = document.getElementById('htmlView');

        dropdown.addEventListener('change', function () {
            if (this.value === 'plain') {
                plainView.style.display = 'block';
                htmlView.style.display = 'none';
            } else {
                plainView.style.display = 'none';
                htmlView.style.display = 'block';
            }
        });
    </script>
</body>

</html>
