<?php
/**
 * Audio playback view for recorded calls (used in modal iframe).
 */
// Expected variables: $url (base API endpoint), $crtObjectId
$audioUrl = $url . urlencode(json_encode(['crtObjectId' => $crtObjectId, 'targetFormat' => 'mp3']));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>録音再生</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { margin:0; padding:8px; font-family:sans-serif; }
    audio { width:100%; }
    .actions { margin-top:8px; text-align:right; }
    .btn {
      display:inline-block;
      padding:4px 8px;
      font-size:12px;
      border:1px solid #ccc;
      background:#f8f8f8;
      color:#333;
      text-decoration:none;
      border-radius:3px;
    }
  </style>
</head>
<body>
  <audio controls>
    <source src="<?= h($audioUrl) ?>" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

  <div class="actions">
    <a class="btn" href="<?= h($audioUrl) ?>" target="_blank" rel="noopener">Download</a>
  </div>
</body>
</html>
