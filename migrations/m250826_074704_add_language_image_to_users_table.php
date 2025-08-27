<?php

use yii\db\Migration;

class m250826_074704_add_language_image_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}','language', $this->string(10)->defaultValue('en')->after('email'));
        $this->addColumn('{{%users}}','image',$this->string(255)->null()->after('language'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'image');
        $this->dropColumn('{{%users}}', 'language');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250826_074704_add_language_image_to_users_table cannot be reverted.\n";

        return false;
    }
    */
}
