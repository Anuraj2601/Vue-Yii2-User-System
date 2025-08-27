<?php

use yii\db\Migration;

class m250827_034921_seed_rbac_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* $auth = Yii::$app->authManager;

        // Create roles
        $adminRole = $auth->createRole('admin');
        $adminRole->description = 'Administrator';
        $auth->add($adminRole);

        $userRole = $auth->createRole('user');
        $userRole->description = 'Regular User';
        $auth->add($userRole);

        // Create permissions
        $manageUsers = $auth->createPermission('manage_users');
        $manageUsers->description = 'Manage all users';
        $auth->add($manageUsers);

        $viewDashboard = $auth->createPermission('view_dashboard');
        $viewDashboard->description = 'View Dashboard';
        $auth->add($viewDashboard);

        
        $createUser = $auth->createPermission('create_user');
        $createUser->description = 'Create a new user';
        $auth->add($createUser);

        $editUser = $auth->createPermission('edit_user');
        $editUser->description = 'Edit an existing user';
        $auth->add($editUser);

        $deleteUser = $auth->createPermission('delete_user');
        $deleteUser->description = 'Delete the user';
        $auth->add($deleteUser);

        $auth->addChild($adminRole, $manageUsers );
        $auth->addChild($adminRole, $createUser);
        $auth->addChild($adminRole, $editUser );
        $auth->addChild($adminRole, $deleteUser);
        $auth->addChild($adminRole, $viewDashboard);
        $auth->addChild($userRole, $viewDashboard); */
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250827_034921_seed_rbac_roles cannot be reverted.\n";

        return false;
    }
    */
}
