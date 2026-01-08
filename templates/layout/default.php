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
                                            <?php if ($currentUser): ?>
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

    <!-- Global Voice Log Modal -->
    <div class="modal fade" id="voiceLogModal" tabindex="-1" aria-labelledby="voiceLogModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- Bootstrap 3 close button -->
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="voiceLogModalLabel">録音再生</h5>
                </div>
                <div class="modal-body p-0">
                    <iframe id="voiceLogFrame" src="" width="100%" height="80" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <?= $this->Html->script('https://code.jquery.com/jquery-1.12.4.min.js') ?>
    <?= $this->Html->script('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js') ?>
    <?= $this->Html->script('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ?>
    <?= $this->Html->script('https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js') ?>
    <?= $this->Html->script('https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js') ?>
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

        // Accessibility helpers for modal
        (function() {
            var $modal = $('#voiceLogModal');
            $modal.on('shown.bs.modal', function() {
                // Ensure assistive tech can access modal when shown
                $modal.attr('aria-hidden', 'false');
                // Move focus to modal for screen readers
                $modal.attr('tabindex', '-1').focus();
            });
            $modal.on('hidden.bs.modal', function() {
                // Restore aria-hidden and clear iframe
                $modal.attr('aria-hidden', 'true');
                var iframe = document.getElementById('voiceLogFrame');
                if (iframe) iframe.src = '';
                // Move focus away from hidden modal
                document.activeElement && document.activeElement.blur();
            });
        })();

        // Global function to open voice log modal with given IDs
        function openVoiceLogModal(callId, crtObjectId) {
            try {
                var url = '<?= $this->Url->build(['controller' => 'Homes', 'action' => 'play']) ?>' + '/' + encodeURIComponent(callId) + '/' + encodeURIComponent(crtObjectId);
                var iframe = document.getElementById('voiceLogFrame');
                iframe.src = url;

                var modalEl = document.getElementById('voiceLogModal');
                // Use Bootstrap 3 jQuery plugin for modal
                $(modalEl).modal('show');

                // Clear iframe on modal hide (defensive in case plugin doesn't fire)
                var clearSrc = function() { iframe.src = ''; };
                if (modalEl.addEventListener) {
                    modalEl.addEventListener('hidden.bs.modal', clearSrc, { once: true });
                } else {
                    $(modalEl).one('hidden.bs.modal', clearSrc);
                }
            } catch (e) { console.error(e); }
        }
    </script>
    <?= $this->fetch('script') ?>
</body>
</html>
