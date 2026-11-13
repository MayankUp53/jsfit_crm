<?php
/**
 * Store list pop-up (reuses phone list data but only shows names).
 */
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">店舗一覧</h3>
        </div>
        <div class="list-group">
            <?php foreach ($mtPhonelists as $row): ?>
                <button type="button" class="list-group-item store-select">
                    <?= h($row->item_name) ?>
                </button>
            <?php endforeach; ?>
            <?php if (count($mtPhonelists) === 0): ?>
                <div class="list-group-item text-muted">表示できる店舗がありません。</div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.querySelectorAll('.store-select').forEach(function(button) {
            button.addEventListener('click', function() {
                if (window.opener && !window.opener.closed) {
                    var storeName = button.textContent.trim();
                    var input = window.opener.document.getElementById('summary')
                        || window.opener.document.getElementById('ContactSummary');
                    if (!input) {
                        input = window.opener.document.querySelector('[name="summary"], [name="Contact[summary]"]');
                    }
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            var exists = Array.prototype.some.call(input.options, function(option) {
                                return option.value === storeName;
                            });
                            if (!exists) {
                                input.add(new window.opener.Option(storeName, storeName));
                            }
                            input.value = storeName;
                        } else {
                            input.value = storeName;
                        }
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                window.close();
            });
        });
    </script>
</body>
</html>
