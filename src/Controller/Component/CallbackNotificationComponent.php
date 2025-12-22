<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * CallbackNotification component
 */
class CallbackNotificationComponent extends Component
{
    /**
     * Get number of warnings for callbacks
     *
     * @return int Number of warnings
     */
    public function getNumOfWarning(): int
    {
        $controller = $this->getController();
        $coreSettingsTable = $controller->fetchTable('CoreSettings');
        $callbacksTable = $controller->fetchTable('Callbacks');

        // Get core settings
        $coreSetting = $coreSettingsTable->find()
            ->where(['CoreSettings.deleted IS' => null])
            ->first();

        if (!$coreSetting) {
            return 0;
        }

        $callbackNoticeTime = $coreSetting->callback_notice_time ?? 60;
        
        // Get current user from Authentication component
        $controller = $this->getController();
        $identity = $controller->Authentication->getIdentity();
        $currentUser = $identity ? $identity->getOriginalData() : null;

        if (!$currentUser) {
            return 0;
        }

        // Calculate notice datetime
        $noticeDatetime = date('Y-m-d H:i:s', strtotime("+{$callbackNoticeTime} minute"));

        // Count warnings
        $warningCount = $callbacksTable->find()
            ->where([
                'Callbacks.is_done' => 0,
                'Callbacks.callback_datetime <' => $noticeDatetime,
                'Callbacks.deleted IS' => null,
                'Callbacks.updated_user_id' => $currentUser->id,
            ])
            ->count();

        return $warningCount;
    }
}

