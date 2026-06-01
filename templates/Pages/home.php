<?php
/**
 * @var \App\View\AppView $this
 */
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

$this->disableAutoLayout();

if (!Configure::read('debug')) {
    throw new NotFoundException('ページが見つかりません。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CakePHP 開発確認</title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>
</head>
<body>
    <main class="container" style="padding-top: 4rem;">
        <h1>CakePHP 開発確認</h1>
        <p>このページはデバッグ時のみ表示されます。</p>
        <p><?= $this->Html->link('ホームへ戻る', '/') ?></p>
    </main>
</body>
</html>
