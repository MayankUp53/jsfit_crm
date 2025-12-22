<?php
/**
 * Default layout
 *
 * @var \App\View\AppView $this
 */

$cakeDescription = 'AmeyoJ CRM';
$currentUser = $this->request->getAttribute('identity');
$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');

// Set active menu items
$home_active = ($controller == 'Homes') ? 'active' : null;
$customers_active = ($controller == 'Customers') ? 'active' : null;
$contacts_active = ($controller == 'Contacts') ? 'active' : null;
$settings_active = ($controller == 'Settings') ? 'active' : null;
$users_active = ($controller == 'Users') ? 'active' : null;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <!-- CSS -->
    <?= $this->Html->css('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') ?>
    <?= $this->Html->css('https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css') ?>
    <?= $this->Html->css('theme.css') ?>
    <style>
        .btn_margin { margin: 10px 0; }
        a:link { color: #0000ff; }
        a:visited { color: #000080; }
        a:hover { color: #ff0000; }
        a:active { color: #ff8000; }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
    <div class="container-fluid">
        <?php if ($controller != 'Users' || $action != 'login'): ?>
            <!-- Navbar -->
            <div class="bs-docs-section clearfix">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="bs-component">
                            <nav class="navbar navbar-inverse">
                                <div class="container-fluid">
                                    <div class="navbar-header">
                                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2">
                                            <span class="sr-only">Toggle navigation</span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                        </button>
                                    </div>

                                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
                                        <ul class="nav navbar-nav">
                                            <li class="<?= $home_active ?>">
                                                <a href="<?= $this->Url->build('/homes') ?>">Home
                                                    <span class="sr-only">(current)</span>
                                                </a>
                                            </li>
                                            <li class="<?= $customers_active ?>">
                                                <a href="<?= $this->Url->build('/customers') ?>">顧客検索<span class="sr-only">(current)</span></a>
                                            </li>
                                            <li class="<?= $contacts_active ?>">
                                                <a href="<?= $this->Url->build('/contacts') ?>">対応履歴検索<span class="sr-only">(current)</span></a>
                                            </li>
                                            <?php if ($currentUser && $currentUser->auth != 0): ?>
                                                <li class="<?= $settings_active ?>">
                                                    <a href="<?= $this->Url->build('/settings') ?>">設定</a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($currentUser && $currentUser->auth == 99): ?>
                                                <li class="<?= $users_active ?>">
                                                    <a href="<?= $this->Url->build('/users') ?>">ユーザ管理</a>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <a href="<?= $this->Url->build('/users/logout') ?>">ログアウト</a>
                                            </li>
                                        </ul>
                                        <ul class="nav navbar-nav navbar-right">
                                            <?php if ($currentUser): ?>
                                                <li><a href="#" target="_blank"><?= h($currentUser->user_name) ?></a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?= $this->Flash->render() ?>

        <?= $this->fetch('content') ?>
    </div>

    <!-- JS -->
    <?= $this->Html->script('https://code.jquery.com/jquery-1.12.4.min.js') ?>
    <?= $this->Html->script('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js') ?>
    <?= $this->Html->script('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ?>
    <?= $this->Html->script('https://code.jquery.com/ui/1.12.1/i18n/jquery-ui-i18n.min.js') ?>
    <script>
        $(function() {
            // Japanese locale for datepicker
            if ($.datepicker && $.datepicker.regional['ja']) {
                $.datepicker.setDefaults($.datepicker.regional['ja']);
            }
            var dateFormat = 'yy-mm-dd';
            $(".datepicker").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                dateFormat: dateFormat,
            });
            var dateFormat2 = 'yymmdd';
            $(".datepicker2").datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                dateFormat: dateFormat2,
            });
        });
    </script>
    <?= $this->fetch('script') ?>
</body>
</html>
