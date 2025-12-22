<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="users view">
    <h2><?= __('User') ?></h2>
    <dl>
        <dt><?= __('Id') ?></dt>
        <dd>
            <?= h($user->id) ?>
            &nbsp;
        </dd>
        <dt><?= __('Disp Cd') ?></dt>
        <dd>
            <?= h($user->disp_cd) ?>
            &nbsp;
        </dd>
        <dt><?= __('User Name') ?></dt>
        <dd>
            <?= h($user->user_name) ?>
            &nbsp;
        </dd>
        <dt><?= __('Loginid') ?></dt>
        <dd>
            <?= h($user->loginid) ?>
            &nbsp;
        </dd>
        <dt><?= __('Password') ?></dt>
        <dd>
            <?= h($user->password ? '******' : '') ?>
            &nbsp;
        </dd>
        <dt><?= __('Auth') ?></dt>
        <dd>
            <?= isset($authOpt[$user->auth]) ? h($authOpt[$user->auth]) : h($user->auth) ?>
            &nbsp;
        </dd>
        <dt><?= __('Created') ?></dt>
        <dd>
            <?= $user->created ? h($user->created->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('Created User') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->created_user_id]) ? h($opeOpt[$user->created_user_id]) : h($user->created_user_id) ?>
            &nbsp;
        </dd>
        <dt><?= __('Updated') ?></dt>
        <dd>
            <?= $user->updated ? h($user->updated->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('Updated User') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->updated_user_id]) ? h($opeOpt[$user->updated_user_id]) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('Deleted') ?></dt>
        <dd>
            <?= $user->deleted ? h($user->deleted->format('Y-m-d H:i:s')) : '' ?>
            &nbsp;
        </dd>
        <dt><?= __('Deleted User') ?></dt>
        <dd>
            <?= isset($opeOpt[$user->deleted_user_id]) ? h($opeOpt[$user->deleted_user_id]) : '' ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?= __('Actions') ?></h3>
    <ul>
        <li><?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['action' => 'add']) ?> </li>
    </ul>
</div>

