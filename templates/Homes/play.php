<?php
/**
 * Audio playback pop-up for recorded calls.
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
  <style>body{margin:0;padding:8px;font-family:sans-serif}</style>
</head>
<body>
  <audio controls style="width:100%">
    <source src="<?= h($audioUrl) ?>" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>
</body>
</html>
