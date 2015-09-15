<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $role
 */
class Friendships extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Friendship the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'friendships';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            // array('username, email, gender, birthdate', 'required'),
            // array('username', 'unique'),
            // array('username', 'length', 'max' => 80),
            // //password confirmation
            // array('password, password_confirmation', 'required', 'on' => 'insert'),
            // array('password, password_confirmation', 'length', 'min' => 6, 'max' => 60),
            // array('password_confirmation', 'compare', 'compareAttribute' => 'password'),
            // array('email', 'email'),
            // array('email', 'unique'),
            // array('birthdate', 'date', 'format' => 'yyyy-MM-dd', 'allowEmpty'=>false),
            // // The following rule is used by search().
            // // Please remove those attributes that should not be searched.
            // array('id, username, password, email, gender, birthdate', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user'=>array(self::BELONGS_TO, 'Users', 'user_id'),
            'friend'=>array(self::BELONGS_TO, 'Users', 'friend_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            // 'id' => 'ID',
            // 'username' => 'Username',
            // 'password' => 'Password',
            // 'password_confirmation' => 'Password confirmation',
            // 'role' => 'Role',
            // 'email' => 'Email',
            // 'gender' => 'Gender',
            // 'birthdate' => 'Birth date',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {

        $sort = new CSort;
        // $sort->attributes = array(
        //     'id' => array(
        //         'asc' => 'id',
        //         'desc' => 'id desc',
        //     ),
        //     'username' => array(
        //         'asc' => 'username',
        //         'desc' => 'username desc',
        //     ),
        //     'email' => array(
        //         'asc' => 'email',
        //         'desc' => 'email desc',
        //     ),
        //     'gender' => array(
        //         'asc' => 'gender',
        //         'desc' => 'gender desc',
        //     ),
        //     'birthdate' => array(
        //         'asc' => 'birthdate',
        //         'desc' => 'birthdate desc',
        //     ),
        // );

        $criteria = new CDbCriteria;
        // $criteria->condition = 'role != "admin"';

        // $criteria->compare('id', $this->id, true);
        // $criteria->compare('username', $this->username, true);
        // $criteria->compare('password', $this->password, true);
        // $criteria->compare('email', $this->email, true);
        // $criteria->compare('gender', $this->gender, true);
        // $criteria->compare('birthdate', $this->birthdate, true);

        return new CActiveDataProvider($this, array(
                'criteria' => $criteria,
                'sort' => $sort,
        ));
    }

}