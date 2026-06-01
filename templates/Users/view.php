<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="users view">
    <h2><?= __('ユーザ') ?></h2>
    <dl>
        <dt><?= __('ID') ?></dt>
        <dd>
            <?= h($user->id) ?>
            &nbsp;
        </dd>
        <dt><?= __('表示順') ?></dt>
        <dd>
            <?= h($user->disp_cd) ?>
            &nbsp;
        </dd>
        <dt><?= __('ユーザ名') ?></dt>
        <dd>
            <?= h($user->user_name) ?>
            &nbsp;
        </dd>
        <dt><?= __('ログインID') ?></dt>
        <dd>
            <?= h($user->loginid) ?>
            &nbsp;
        </dd>
        <dt><?= __('パスワード') ?></dt>
        <dd>
            <?= h($user->password ? '******' : '') ?>
            &nbsp;
        </dd>
        <dt><?= __('権限') ?></dt>
        <dd>
            <?= isset($authOpt[$user->auth]) ? h($authOpt[$user->auth]) : h($user->auth) ?>
            &nbsp;
        </dd>
        <dt><?= __('登録日時') ?></dt>
        <dd>
            <?= $user->created ? h($user->created->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('登録者') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->created_user_id]) ? h($opeOpt[$user->created_user_id]) : h($user->created_user_id) ?>
            &nbsp;
        </dd>
        <dt><?= __('更新日時') ?></dt>
        <dd>
            <?= $user->updated ? h($user->updated->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('更新者') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->updated_user_id]) ? h($opeOpt[$user->updated_user_id]) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('削除日時') ?></dt>
        <dd>
            <?= $user->deleted ? h($user->deleted->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('削除者') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->deleted_user_id]) ? h($opeOpt[$user->deleted_user_id]) : '' ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?= __('操作') ?></h3>
    <ul>
        <li><?= $this->Html->link(__('ユーザ編集'), ['action' => 'edit', $user->id]) ?> </li>
        <li><?= $this->Form->postLink(__('ユーザ削除'), ['action' => 'delete', $user->id], ['confirm' => __('ユーザID {0} を削除します。よろしいですか？', $user->id)]) ?> </li>
        <li><?= $this->Html->link(__('ユーザ一覧'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('ユーザ新規登録'), ['action' => 'add']) ?> </li>
    </ul>
</div>
