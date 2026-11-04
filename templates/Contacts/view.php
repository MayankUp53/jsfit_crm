<?php
$this->assign('title', '対応履歴詳細');
$currentUser = $this->request->getAttribute('identity');
?>

<script>
    $(function() {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('.email-tooltip').forEach(el => new bootstrap.Tooltip(el));
        } else if ($.fn.tooltip) {
            $('.email-tooltip').tooltip();
        }

        const hideAllTooltips = () => {
            if (window.bootstrap && bootstrap.Tooltip) {
                document.querySelectorAll('.email-tooltip').forEach(el => {
                    const instance = bootstrap.Tooltip.getInstance(el);
                    if (instance) {
                        instance.hide();
                    }
                });
            } else if (window.jQuery && typeof window.jQuery.fn.tooltip === 'function') {
                window.jQuery('.email-tooltip').tooltip('hide');
            }
        };

        window.addEventListener('scroll', hideAllTooltips, true);
        window.addEventListener('pagehide', hideAllTooltips);
        window.addEventListener('pageshow', hideAllTooltips);
        window.addEventListener('beforeunload', hideAllTooltips);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                hideAllTooltips();
            }
        });
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (target && target.closest('a, button, form')) {
                hideAllTooltips();
            }
        }, true);
    });
</script>

<!-- ================= Customer Details ================= -->
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客情報</h3>
    </div>

<table class="table table-bordered">
    <tr>
        <th width="15%">得意先CD</th>
        <td><?= h($customer->client_cd ?? '') ?></td>
        <th width="15%">会社・支店名</th>
        <td><?= h($customer->company_name ?? '') ?></td>
    </tr>
    <tr>
        <th>氏名</th>
        <td><?= h($customer->name ?? '') ?></td>
        <th>番号種別</th>
        <td><?= h($customer->type_of_number ?? '') ?></td>
    </tr>
    <tr>
        <th>TEL</th>
        <td><?= h($customer->tel ?? '') ?></td>
        <th>FAX</th>
        <td><?= h($customer->fax ?? '') ?></td>
    </tr>
    <tr>
        <th>メールアドレス</th>
        <td>
            <?php
            $rawEmail = (string)($customer->email ?? '');
            $emailParts = array_values(array_filter(array_map('trim', preg_split('/\s*[,;\r\n]+\s*/', $rawEmail))));
            $emailCount = count($emailParts);
            $emailFull = implode('; ', $emailParts);
            $emailDisplay = $emailFull;

            if ($emailCount > 3) {
                $emailDisplay = implode('; ', array_slice($emailParts, 0, 3)) . ' ...';
            }
            ?>
            <?php if ($emailCount > 3): ?>
                <span class="email-tooltip"
                      title="<?= h($emailFull) ?>"
                      data-toggle="tooltip"
                      data-bs-toggle="tooltip">
                    <?= h($emailDisplay) ?>
                </span>
            <?php else: ?>
                <?= h($emailDisplay) ?>
            <?php endif; ?>
        </td>
        <th>登録日</th>
        <td><?= h($customer->created?->format('Y-m-d')) ?></td>
    </tr>
    <tr>
    <th>更新日</th>
    <td><?= h($customer->updated?->format('Y-m-d')) ?></td>
    <th></th>
    <td></td>
</tr>

</table>


    <div class="text-right" style="margin-bottom:10px;margin-right:10px;">
        <a href="<?= $this->Url->build(['controller' => 'Customers', 'action' => 'edit', $customer->id]) ?>"
            class="btn btn-warning btn-xs">顧客編集</a>

        <!-- <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
            <?= $this->Form->postLink(
                '顧客削除',
                ['controller' => 'Customers', 'action' => 'delete', $customer->id],
                ['confirm' => '顧客情報を削除します。よろしいですか？', 'class' => 'btn btn-danger btn-xs']
            ) ?>
        <?php endif; ?> -->
    </div>
</div>

<!-- ================= Customer Contact List ================= -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴一覧</h3>
    </div>

    <div class="table-responsive">
    <table class="table table-striped table-hover" style="font-size:12px">
    <thead>
        <tr>
            <th>得意先CD</th>
            <th>会社・支店名</th>
            <th>氏名</th>
            <th>番号種別</th>
            <th>TEL</th>
            <th>概要</th>
            <th>ステータス</th>
            <th>担当OP</th>
            <th>登録日</th>
            <th>更新日</th> <!-- new column -->
            <th>録音</th>            
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($contacts) && count($contacts) > 0): ?>
        <?php foreach ($contacts as $row): ?>
            <tr>
                <td><?= h($row->customer->client_cd ?? '') ?></td>
                <td><?= h($row->customer->company_name ?? '') ?></td>
                <td><?= h($row->customer->name ?? '') ?></td>
                <td><?= h($row->customer->type_of_number ?? '') ?></td>
                <td><?= h($row->customer->tel ?? '') ?></td>
                <td><?= h($row->summary) ?></td>
                <td><?= h($row->status) ?></td>
                <td><?= h($row->op_incharge) ?></td>
                <td><?= h($row->created ? $row->created->format('Y-m-d H:i') : '') ?></td>
                <td><?= h($row->updated ? $row->updated->format('Y-m-d H:i') : '') ?></td> 
                <td>
                    <a href="javascript:void(0);" onclick="openVoiceLogModal('<?= h($row->id ?? '') ?>','<?= h($row->crt_object_id ?? '') ?>');">再生</a>
                </td>               
                
            </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">対応履歴はありません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

    </div>
</div>

 <!-- ================= Main Contact Detail ================= -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴更新</h3>
    </div>

    <!-- Form bound to contact, posts to view() action -->
    <?= $this->Form->create($contact, ['url' => ['action' => 'view', $contact->id, 'type' => 'post' ]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <?= $this->Form->control('customer_id', ['type' => 'hidden', 'value' => $contact->customer_id]) ?>

    <div class="panel-body">
        <!-- 概要 -->
        <div class="form-group">
            <label>概要</label>
            <?= $this->Form->control('summary', [
                'label' => false,
                'type' => 'select',
                'options' => $overviewOpt ?? [],
                'empty' => '-----',
                'class' => 'form-control',
                'value' => $contact->summary
            ]) ?>
        </div>

        <!-- ステータス -->
        <div class="form-group">
            <label>ステータス</label>
            <?= $this->Form->control('status', [
                'label' => false,
                'type' => 'select',
                'options' => $statusOpt ?? [],
                'empty' => '-----',
                'class' => 'form-control',
                'value' => $contact->status
            ]) ?>
        </div>

        <!-- 対応内容 -->
        <div class="form-group">
            <label>対応内容</label>
            <?= $this->Form->control('remark', [
                'label' => false,
                'type' => 'textarea',
                'rows' => 5,
                'class' => 'form-control',
                'value' => $contact->remark
            ]) ?>
        </div>
    </div>

    <!-- ================= Buttons ================= -->
    <div class="text-center" style="margin-top:15px;">
       
           <!-- Cancel button (submit to controller) -->
    <?= $this->Form->button('キャンセル', [
        'type'  => 'submit',
        'name'  => 'back',   
        'value' => '1',
        'id'    => 'cancelBtn',
         'formnovalidate' => true,  
        'class' => 'btn-default btn-xs',
        'style' => 'width:150px;height:30px;cursor:pointer; margin-right:10px;'
    ]) ?>
     
        <?= $this->Form->button('更　新', [
            'type' => 'submit',
            'name' => 'update',
            'value' => '1',
            'class' => 'btn btn-info btn-xs',
            'style' => 'width:150px;height:35px;margin:5px;'
        ]) ?>

       
        <?= $this->Form->button('削　除', [
            'type' => 'submit',
            'name' => 'delete',
            'value' => '1',
            'class' => 'btn btn-danger btn-xs',
            'style' => 'width:150px;height:35px;margin:5px;',
            'onclick' => "return confirm('削除しますか？');"
        ]) ?>

      
        <?= $this->Form->button('新規登録', [
            'type' => 'submit',
            'name' => 'add',
            'value' => '1',
            'class' => 'btn btn-success btn-xs',
            'style' => 'width:150px;height:35px;margin:5px;'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
