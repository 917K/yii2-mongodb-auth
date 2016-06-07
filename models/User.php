<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\mongodb\Query;

/**
 * This is the model class for collection "user".
 *
 * @property ObjectID|string $_id
 * @property mixed $name
 * @property mixed $authKey
 */
class User extends \yii\mongodb\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * @var array EAuth attributes
     */
    public $profile;
    public $_id;
    public $id;
    public $authKey;
    public $password;


    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'user';
    }

    public function getId() {
        return $this->_id;
    }

    public function getAuthKey() {
        return $this->authKey;
    }

    public function validateAuthKey($authKey) {
        return $this->authKey === $authKey;
    }

    public function validatePassword($password) {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'id',
            'name',
            'authKey'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'authKey'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => '_ID',
            'id' => 'ID',
            'name' => 'Name',
            'authKey' => 'AuthKey'
        ];
    }

    public static function findIdentity($id) {
        if (Yii::$app->getSession()->has('user-'.$id)) {
            return new self(Yii::$app->getSession()->get('user-'.$id));
        }
        else {
            return isset(self::$users[$id]) ? new self(self::$users[$id]) : null;
        }
    }

    /**
     * @param \nodge\eauth\ServiceBase $service
     * @return User
     * @throws ErrorException
     */
    public static function findByEAuth($service) {
        if (!$service->getIsAuthenticated()) {
            throw new ErrorException('EAuth user should be authenticated before creating identity.');
        }

        $id = $service->getServiceName().'-'.$service->getId();
        $attributes = array(
            'id' => $id,
            'name' => $service->getAttribute('name'),
            'authKey' => md5($id),
            //'profile' => $service->getAttributes(),
        );
        var_dump($attributes);
        //exit();
        //$attributes['profile']['service'] = $service->getServiceName();
        Yii::$app->getSession()->set('user-'.$id, $attributes);
        return new self($attributes);
    }

    public static function findIdentityByAccessToken($token, $type = NULL) {
        return static::findOne(['authKey' => $token]);
    }

    public static function findByUsername($name) {
        return static::findOne(['name' => $name]);
    }

}
