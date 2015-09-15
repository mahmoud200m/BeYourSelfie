<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $name
 * @property string $password
 * @property string $role
 */
class Users extends CActiveRecord {

    // holds the password confirmation word
    public $password_confirmation;
    //will hold the encrypted password for update actions.
    public $initialPassword;

    public $image_temp;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Users the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'users';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, email', 'required'),
            array('name', 'length', 'max' => 80),
            //password confirmation
            array('password, password_confirmation', 'required', 'on' => 'insert'),
            array('password, password_confirmation', 'length', 'min' => 6, 'max' => 60),
            array('password_confirmation', 'compare', 'compareAttribute' => 'password'),
            array('email', 'email'),
            array('email', 'unique'),
            // array('birthdate', 'date', 'format' => 'yyyy-MM-dd', 'allowEmpty'=>true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, password, email, gender, birthdate', 'safe', 'on' => 'search'),

            array('image_temp', 'file', 'types'=>'jpg, gif, png', 'allowEmpty' => true),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'friendships'=>array(self::HAS_MANY, 'Friendships', 'user_id'),
            'acceptedfriendships'=>array(self::HAS_MANY, 'Friendships', 'user_id', 'condition'=>'status=1'),
            'selfies'=>array(self::HAS_MANY, 'Selfies', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'image' => 'Image',
            'name' => 'Name',
            'password' => 'Password',
            'password_confirmation' => 'Password confirmation',
            'role' => 'Role',
            'email' => 'Email',
            'gender' => 'Gender',
            'birthdate' => 'Birth date',
        );
    }

    public function beforeSave() {
        // in this case, we will use the old hashed password.
        if (empty($this->password) && empty($this->password_confirmation) && !empty($this->initialPassword)) {
            $this->password = $this->initialPassword;
            $this->password_confirmation = $this->initialPassword;
        } else {
            $this->password = sha1($this->password); //TODO: edit password encryption method
        }
        
        return parent::beforeSave();
    }

    public function afterFind() {
        //reset the password to null because we don't want the hash to be shown.
        $this->initialPassword = $this->password;
        $this->password = null;
        $this->password_confirmation = null;

        parent::afterFind();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {

        $sort = new CSort;
        $sort->attributes = array(
            'id' => array(
                'asc' => 'id',
                'desc' => 'id desc',
            ),
            'name' => array(
                'asc' => 'name',
                'desc' => 'name desc',
            ),
            'email' => array(
                'asc' => 'email',
                'desc' => 'email desc',
            ),
            'gender' => array(
                'asc' => 'gender',
                'desc' => 'gender desc',
            ),
            'birthdate' => array(
                'asc' => 'birthdate',
                'desc' => 'birthdate desc',
            ),
        );

        $criteria = new CDbCriteria;
        $criteria->condition = 'role != "admin"';

        $criteria->compare('id', $this->id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('password', $this->password, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('gender', $this->gender, true);
        $criteria->compare('birthdate', $this->birthdate, true);

        return new CActiveDataProvider($this, array(
                'criteria' => $criteria,
                'sort' => $sort,
        ));
    }

    public function getFriendsLinks () {
        $acceptedfriendships = $this->acceptedfriendships;

        if( count($acceptedfriendships) > 0 ){
            $html = '<ul>';
            foreach($acceptedfriendships as $key=>$friendship) { 
                $html .= sprintf ('<li>%s</li>', CHtml::link($friendship->friend->name, array('users/view', 'id' => $friendship->friend->id)));
            }
            $html .= '</ul>';
            
            return $html;
        }else{
            return "no friends";
        }

    }

    public function getSelfiesLinks () {
        $selfies = $this->selfies;

        if( count($selfies) > 0 ){
            $html = '<div>';
            foreach($selfies as $key=>$selfie) { 
                $small_image = substr($selfie->image, 0, strlen($selfie->image)-4).'_s'.substr($selfie->image, -4);

                $html .= '<a href="'.Yii::app()->createUrl("selfies/view", array("id" => $selfie->id)).'" style="display: inline-block; width: 50px; height: 50px; line-height: 50px; float: left; margin: 5px; padding: 5px; border: 1px solid white;" >';
                $html .= '<img src="'.Yii::app()->getBaseUrl(true).'/uploads/selfies/'.$small_image.'" style="width: 50px;" />';
                $html .= '</a>';
            }
            $html .= '</div>';
            
            return $html;
        }else{
            return "no selfies";
        }

    }

    public function isFriend($id) {
        //$this->id is the id of the model which is in the array of the many users array
        //$id is the always the id of the logged in user
        $criteria = new CDbCriteria;
        $criteria->select = 'user_id, status';
        $criteria->condition = '(user_id='.$this->id.' && '.'friend_id='.$id.')'.' || '.'(user_id='.$id.' && '.'friend_id='.$this->id.')';
        $criteria->order = 'id DESC';
        $friend_model = Friendships::model()->findAll($criteria);
        if( count($friend_model) > 0 ){
            if( $friend_model[0]->status == '1' ){
                $is_friend = 'y';
            }else if( $friend_model[0]->status == '0' ){
                if( $friend_model[0]->user_id == $id ){
                    $is_friend = 's'; //request sent
                }else{
                    $is_friend = 'r'; //request recieved
                }
            }else{
                $is_friend = 'n';
            } 
        }else{
            $is_friend = 'n';
        }

        return $is_friend;
    }

    public function isFollow($id) {
        $model = Fellowships::model()->find('user_id='.$id.' && '.'follow_id='.$this->id);
        if( count($model) == 0 ){
            return false;
        }else{
            return true;
        }
    }
}