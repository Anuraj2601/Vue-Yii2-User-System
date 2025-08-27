<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $language
 * @property string|null $image
 * @property string $password_hash
 * @property string|null $auth_key
 * @property string|null $access_token
 * @property string $created_at
 * @property string $updated_at
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return self::findOne(['email' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getProfileImageUrl()
    {
        return $this->image
            ? Yii::getAlias('@web/' . $this->image)
            : Yii::getAlias('@web/images/profile/sample.jpg');
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['auth_key', 'access_token'], 'default', 'value' => null],
            [['name', 'email', 'password_hash'], 'required', 'message' => Yii::t('app', 'This field is required.')],
            [['email'], 'email', 'message' => Yii::t('app', 'Invalid email address.')],
            [['password_hash'], 'string', 'min' => 6, 'message' => Yii::t('app', 'Password must be at least 6 characters.')],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['password_hash', 'access_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'unique', 'message' => 'This email address has already been taken.'],
            [['access_token'], 'unique'],
            [['language'], 'string', 'max' => 10],
            [['image'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'language' => 'Language',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
