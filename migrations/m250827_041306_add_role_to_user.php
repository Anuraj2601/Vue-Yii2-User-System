<?php

use yii\db\Migration;

class m250827_041306_add_role_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $adminRole = $auth->getRole('admin');
        if($adminRole) {
            $auth->assign($adminRole, 2);
        }

        $userRole = $auth->getRole('user');
        if($userRole) {
            $auth->assign($userRole, 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->revoke($auth->getRole('admin'), 2);
        $auth->revoke($auth->getRole('user'), 1);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250827_041306_add_role_to_user cannot be reverted.\n";

        return false;
    }
    */
}
