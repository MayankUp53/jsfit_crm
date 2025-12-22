<?php
/**
 * Audio playback pop-up for recorded calls.
 */
?>
<?php
$param = json_encode(['crtObjectId' => $crtObjectId, 'targetFormat' => 'mp3']);
$audioUrl = $url . urlencode($param);
?>
<html>
    <head>
        <title>Voice Log</title>
    </head>
    <body style="overflow-x:hidden">
        <audio id="<?= h($id) ?>" preload="auto" controls>
            <source src="<?= h($audioUrl) ?>" type="audio/mp3">
        </audio>
    </body>
</html>
