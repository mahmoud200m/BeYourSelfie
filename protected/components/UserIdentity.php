<?php
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

        private $_id;

        public function authenticate() {
                $user = Users::model()->findByAttributes(array('email' => $this->username));

                $this->password = sha1($this->password); //TODO: edit password encryption method

                if ($user === null){
                        $this->errorCode = self::ERROR_USERNAME_INVALID;
                }
                else if ($user->initialPassword !== $this->password){
                        $this->errorCode = self::ERROR_PASSWORD_INVALID;
                }
                else{
                        $this->_id = $user->id;
                        $this->setState('role', $user->role);
                        $this->setState('name', $user->name);
                        $this->errorCode = self::ERROR_NONE;
                }

                return !$this->errorCode;
        }

        public function getId() {
                return $this->_id;
        }

}