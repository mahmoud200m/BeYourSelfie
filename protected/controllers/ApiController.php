<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class ApiController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/website',
	 * meaning using a single column layout. See 'protected/views/layouts/website.php'.
	 */
	public $layout='//layouts/empty';

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($errors=Yii::app()->errorsHandler->errors)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $errors['message'];
			else
				$this->render('errors', $errors);
		}
	}

	public function actionSignUp()
	{
		$parameters = array('email', 'password', 'parse_objectId');
		$output = array('status'=>'', 'errors'=>'', 'user_id'=>'', 'auth_code'=>'');

		if( $this->_checkParameters($parameters) ){
			
			// collect user input data
	        $user = Users::model()->findByAttributes(array('email' => $_POST['email']));
	        if( isset($user) ){
	        	//check if facebook login or register with the same email
				if( strpos($user->email, "@facebook.com") === false ){
					$output['status'] = 1;
					$output['errors'] = "this email registered before";
					echo json_encode($output);
					return;
	        	}
        	}else{
				// sign up
				$user=new Users;
				$user->name=isset($_POST['name'])?$_POST['name']:"no name";
				$user->email=$_POST['email'];
				$user->password=$_POST['password'];
				$user->password_confirmation=$_POST['password'];
				$user->parse_objectId=$_POST['parse_objectId'];
				
				if(!$user->save()){
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($user); //saving problem
					echo json_encode($output);
					return;
				}else{
					$model=Users::model()->findByPk($user->id);
					$model->image_temp=CUploadedFile::getInstance($model,'image');
					if( isset($model->image_temp) ){
						$model->image = $model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);

						if($model->save()){    			
		                	$model->image_temp->saveAs(Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->image);

		                	//resizing image
							$original_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
							$big_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'_b.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
							$small_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'_s.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
		    				$image = WideImage::load($original_image);
							$resized  = $image->resize(1080, 1080);
							$resized->saveToFile($big_image);
							$resized  = $image->resize(540, 540);
							$resized->saveToFile($small_image);
						}else{
							$output['status'] = 1;
							$output['errors'] = $this->_getErrors($model); //saving problem
        					echo json_encode($output);
							return;
						}
					}
				}
			}

			//login
			$user = $this->_login($_POST['email'], $_POST['password'], $_POST['parse_objectId']);
			if( $user != null ){
				$output['user_id'] = $user->id;
				$output['auth_code'] = $user->auth_code;
				$output['name'] = $user->name;
				$output['image'] = Yii::app()->getBaseUrl(true).'/uploads/users/'.$user->image;
				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "login problem";
			}
		}
        	
    	echo json_encode($output);
	}


	public function actionSignIn()
	{
		$parameters = array('email', 'password', 'parse_objectId');
		$output = array('status'=>'', 'errors'=>'', 'user_id'=>'', 'auth_code'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_checkEmail($_POST['email']) ){
				$user = $this->_login($_POST['email'], $_POST['password'], $_POST['parse_objectId']);
				if( $user != null ){
					$output['user_id'] = $user->id;
					$output['auth_code'] = $user->auth_code;
					$output['name'] = $user->name;
					$output['image'] = Yii::app()->getBaseUrl(true).'/uploads/users/'.$user->image;
					$output['status'] = 0; //ok
				}else{
					$output['status'] = 1;
					$output['errors'] = "email and password is not correct";
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not found please register";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionRecoverPassword()
	{
		$parameters = array('email');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			$new_password = substr(md5(uniqid(rand(), true)), 6, 6);

			$model=Users::model()->find("email='".$_POST['email']."'");
			$model->password=$new_password;
			$model->password_confirmation=$new_password;
			if($model->save()){
			 	$mail = new YiiMailer();
				$mail->setFrom('recovery@senWgem.com', 'Sen W Gem');
				$mail->setTo($_POST['email']);
				$mail->setSubject('Sen W Gem Password Recovery');
				$mail->setBody('your password changed to: '.$new_password);
				if ($mail->send()) {
				    $output['status'] = 0; //ok
				} else {
					$output['status'] = 1; 
					$output['errors'] = 'Password changed but errors while sending email: '.$mail->getError(); //sending email problem
				}
			}else{
				$output['status'] = 1; 
				$output['errors'] = $this->_getErrors($model); //saving problem
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		$parameters = array('user_id', 'auth_code');
		$output = array('status'=>'', 'errors'=>'');
                
		// collect user input data
		if( $this->_checkParameters($parameters) )
		{
			if( $this->_verify_user() ){
				$user = Users::model()->findByPk($_POST['user_id']);
	        	$user->auth_code = "";
	        	$user->parse_objectId = "";
	        	if( $user->save() ){
		        	$output['status'] = 0; //ok
		        }else{
					$output['status'] = 1;
					$output['errors'] = "logout problem";
		        }
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionChangePassword()
	{
		$parameters = array('user_id', 'auth_code', 'new_password');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = Users::model()->findByPk($_POST['user_id']);
				$model->password=$_POST['new_password'];
				$model->password_confirmation=$_POST['new_password'];
				if($model->save()){
					$output['status'] = 0; //ok
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($model); //saving problem
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}
			
		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionUpdateProfile()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = Users::model()->findByPk($_POST['user_id']);

				if( isset($_POST['name']) && !empty($_POST['name']) ){
					$model->name=$_POST['name'];
				}
				if( isset($_POST['email']) && !empty($_POST['email']) ){
					$model->email=$_POST['email'];
				}

				$model->image_temp=CUploadedFile::getInstance($model,'image');
				if( isset($model->image_temp) ){
					$model->image = $model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);

					if($model->save()){    			
	                	$model->image_temp->saveAs(Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->image);

	                	//resizing image
						$original_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
						$big_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'_b.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
						$small_image = Yii::getPathOfAlias('webroot').'/uploads/users/'.$model->id.'_s.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
	    				$image = WideImage::load($original_image);
						$resized  = $image->resize(1080, 1080);
						$resized->saveToFile($big_image);
						$resized  = $image->resize(540, 540);
						$resized->saveToFile($small_image);

						$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($model); //saving problem
    					echo json_encode($output);
						return;
					}
				}else{
					if($model->save()){
						$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($model); //saving problem
					}
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}
			
		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionSendSelfie()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model=new Selfies;
				$model->user_id=$_POST['user_id'];

	            if($model->save()) {
					$model=Selfies::model()->findByPk($model->id);
					$model->image_temp=CUploadedFile::getInstance($model,'image');
					$model->image = $model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);

					if($model->save()){    			
	                	$model->image_temp->saveAs(Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->image);

	                	//resizing image
						$original_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
						$big_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'_b.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
						$small_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'_s.'.pathinfo($model->image_temp->name, PATHINFO_EXTENSION);
	    				$image = WideImage::load($original_image);
						$resized  = $image->resize(1080, 1080);
						$resized->saveToFile($big_image);
						$resized  = $image->resize(540, 540);
						$resized->saveToFile($small_image);

	                	$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($model); //saving problem
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($model); //saving problem
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
    }

	public function actionGetSelfies()
	{
		$this->_getSelfies('a');
	}

	public function actionGetTopSelfies()
	{
		$this->_getSelfies('t');
	}

	public function actionGetMySelfies()
	{
		$this->_getSelfies('m');
	}

	public function actionGetUserSelfies()
	{
		$this->_getSelfies('o');
	}

	private function _getSelfies($type = 'a') //type: 'a'->all, 't'->top, 'm'-> my selfies, 'o'-> other
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'selfies'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->compare("confirmed", "y", false, "AND");
				if( isset($_POST['page']) ){
					$criteria->limit = 10;
					$criteria->offset = ($_POST['page']-1)*10;
				}
				
				if( $type == 'a' ){ 
					$criteria->order = 'id DESC';
				}elseif( $type == 't' ){ 
					$current_month = date('m');
					$current_year = date('Y');
					$criteria->limit = 10;
					$criteria->condition = "timestamp BETWEEN '".$current_year."-".$current_month."-01 00:00:00' AND '".$current_year."-".$current_month."-31 23:59:59' AND confirmed = 'y'";
					$criteria->order = 'likes DESC';
				}elseif( $type == 'm' ){ 
					$criteria->compare("user_id", $_POST['user_id'], false, "AND");
					$criteria->order = 'id DESC';
				}elseif( $type == 'o' ){ 
					if( isset($_POST['user_id_to_get_selfies']) && !empty($_POST['user_id_to_get_selfies']) ){
						$criteria->compare("user_id", $_POST['user_id_to_get_selfies'], false, "AND");
						$criteria->order = 'id DESC';
					}else{
						$output['status'] = 1;
						$output['errors'] = "inputs problem5";
        				echo json_encode($output);
        				return;
					}
				}

				$model = Selfies::model()->findAll($criteria);
				$selfies = array();
				foreach ($model as $key => $selfie) {
					$id = $selfie->id;
					$image = Yii::app()->getBaseUrl(true).'/uploads/selfies/'.$selfie->image;
					$username = $selfie->user->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$selfie->user->image;
					$likes = $selfie->likes;

					$image_year = substr($selfie->timestamp, 0, 4);
					$image_month = substr($selfie->timestamp, 5, 2);

					//can like: 'y'->yes, 'b'->liked before, 'n'->no (selfie from past month)
					if( $image_year == date("Y") && $image_month == date("m") ){ //compare date to allow like for selfies of this month only
						$criteria = new CDbCriteria;
						$criteria->condition = 'selfie_id = '.$selfie->id.' && '.'user_id = '.$_POST['user_id'];
						$like_model = Likes::model()->find($criteria);
						if( count($like_model) == 0 ){
							$can_like = 'y';
						}else{
							$can_like = 'b';
						}
					}else{
						$can_like = 'n';
					}

					//check if user follows the selfie owner
				    $follow_model = Fellowships::model()->find('user_id='.$_POST['user_id'].' && '.'follow_id='.$selfie->user_id);
				    if( count($follow_model) == 0 ){
						$can_follow = 'y';
					}else{
						$can_follow = 'n';
					}

					//check if user friendship status
					$is_friend = $selfie->user->isFriend($_POST['user_id']);

					//check if user is friend with the selfie owner to check if can comment or not
					if( $is_friend == 'y' ){
						$can_comment = 'y';
					}else{
						$can_comment = 'n';
					}

					//get comments
					$criteria = new CDbCriteria;
					$criteria->select = 'id, comment, timestamp, user_id';
					$criteria->condition = 'selfie_id = '.$selfie->id;
					$model = Comments::model()->findAll($criteria);
					$comments = array();
					foreach ($model as $key => $comment) {
						$comment_id = $comment->id;
						$comment_text = $comment->comment;
						$comment_username = $comment->user->name;
						$comment_user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$comment->user->image;
						$comment_timestamp = $comment->timestamp;

						$comments[] = array('id' => $comment_id, 'comment' => $comment_text, 'user_id' => $comment->user_id, 
											'username' => $comment_username, 'user_image' => $comment_user_image, 'timestamp' => $comment_timestamp);
					}

					$selfies[] = array('id' => $id, 'image' => $image, 'user_id' => $selfie->user_id, 'username' => $username, 
										'user_image' => $user_image, 'likes' => $likes, 'can_like' => $can_like, 'can_follow' => $can_follow, 
										'is_friend' => $is_friend, 'can_comment' => $can_comment, 'comments' => $comments);
				}
				// $output['selfies'] = CJSON::encode($selfies);
				$output['selfies'] = $selfies;

				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionDeleteSelfie()
	{
		$parameters = array('user_id','auth_code','selfie_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){

				$selfie = Selfies::model()->findByPk($_POST['selfie_id']);
				if( isset($selfie) && $selfie->user_id == $_POST['user_id'] ){
					$selfie->confirmed = 'd';

					if($selfie->save()){
						$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($selfie); //saving problem
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = "you don't have selfie with this id";
				}
				
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionLike()
	{
		$parameters = array('user_id','auth_code','selfie_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->condition = 'selfie_id = '.$_POST['selfie_id'].' && '.'user_id = '.$_POST['user_id'];
				$like_model = Likes::model()->findAll($criteria);

				if( count($like_model) == 0 ){
					$model = new Likes;
					$model->selfie_id = $_POST['selfie_id'];
					$model->user_id = $_POST['user_id'];
					if($model->save()){    			
	                	$selfie = Selfies::model()->findByPk($_POST['selfie_id']);
						$selfie->likes += 1;

						if($selfie->save()){    
							if( $_POST['user_id'] != $selfie->user_id ){
								$user = Users::model()->findByPk($_POST['user_id']);
								$notification = array('id'=>'4', 'text'=>$user->name." liked your selfie", 'user_id'=>$_POST['user_id'], 'selfie_id'=>$selfie->id);
								$this->_send_notification($selfie->user_id, json_encode($notification));
							}

		                	$output['status'] = 0; //ok
						}else{
							$output['status'] = 1;
							$output['errors'] = $this->_getErrors($model); //saving problem
						}
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($model); //saving problem
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = "liked before";
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionUnLike()
	{
		$parameters = array('user_id','auth_code','selfie_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->condition = 'selfie_id = '.$_POST['selfie_id'].' && '.'user_id = '.$_POST['user_id'];
				$like_model = Likes::model()->find($criteria);

				if( isset($like_model) ){
					if($like_model->delete()){    			
	                	$model = Selfies::model()->findByPk($_POST['selfie_id']);
						$model->likes -= 1;

						if($model->save()){    			
		                	$output['status'] = 0; //ok
						}else{
							$output['status'] = 1;
							$output['errors'] = $this->_getErrors($model); //saving problem
						}
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($model); //saving problem
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = "not liked before";
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionComment()
	{
		$parameters = array('user_id','auth_code','selfie_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = new Comments;
				$model->selfie_id = $_POST['selfie_id'];
				$model->user_id = $_POST['user_id'];
				$model->comment = $_POST['comment'];

				if($model->save()){   
                	$selfie = Selfies::model()->findByPk($_POST['selfie_id']);
					if( $_POST['user_id'] != $selfie->user_id ){
						$user = Users::model()->findByPk($_POST['user_id']);
						$notification = array('id'=>'5', 'text'=>$user->name." added new comment on your selfie", 'user_id'=>$_POST['user_id'], 'selfie_id'=>$selfie->id);
						$this->_send_notification($selfie->user_id, json_encode($notification));
					}
					
                	$output['status'] = 0; //
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($model); //saving problem
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}
	
	public function actionGetComments()
	{
		$parameters = array('user_id','auth_code','selfie_id');
		$output = array('status'=>'', 'errors'=>'', 'comments'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->select = 'id, comment, timestamp, user_id';
				$criteria->condition = 'selfie_id = '.$_POST['selfie_id'];
				$model = Comments::model()->findAll($criteria);
				$comments;
				foreach ($model as $key => $comment) {
					$id = $comment->id;
					$comment_text = $comment->comment;
					$username = $comment->user->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$comment->user->image;
					$timestamp = $comment->timestamp;

					$comments[] = array('id' => $id, 'comment' => $comment_text, 'user_id' => $comment->user_id, 'username' => $username, 'user_image' => $user_image, 'timestamp' => $timestamp);
				}

            	$output['status'] = 0; //ok
            	$output['comments'] = $comments; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionSearchUsers()
	{
		$parameters = array('user_id','auth_code','query');
		$output = array('status'=>'', 'errors'=>'', 'users'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->select = 'id, name, image, email';
				$criteria->condition = 'name like "%'.$_POST['query'].'%"';
				$model = Users::model()->findAll($criteria);
				$users;
				foreach ($model as $key => $user) {
					$id = $user->id;
					$username = $user->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$user->image;
					$email = $user->email;

					$users[] = array('id' => $id, 'username' => $username, 'user_image' => $user_image, 'email' => $email, 'is_friend' => $user->isFriend($_POST['user_id']), 'is_follow' => $user->isFollow($_POST['user_id']));
				}

            	$output['status'] = 0; //ok
            	$output['users'] = $users; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionAddFriend()
	{
		$parameters = array('user_id','auth_code','friend_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$friendship = $this->_getLastFriendshipRequest($_POST['friend_id']);
				if ( $friendship == null || $friendship->status == 2 || $friendship->status == 4 ){ //if there isn't friendship request or if sent old one and rejected (status:2) or removed (status:4)
					$model=new Friendships;
					$model->user_id = $_POST['user_id'];
					$model->friend_id = $_POST['friend_id'];
					$model->status = 0;

					if($model->save()){
						$user = Users::model()->findByPk($_POST['user_id']);
						$notification = array('id'=>'1', 'text'=>$user->name." sent you a new friend request", 'user_id'=>$_POST['user_id']);
						$this->_send_notification($_POST['friend_id'], json_encode($notification));

						$output['status'] = 0; //ok
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = "you can't send new friend request";
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionCancelFriendRequest()	
	{
		$parameters = array('user_id','auth_code','request_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = Friendships::model()->findByPk($_POST['request_id']);
				$model->status = 5;
				if($model->save()){
					$output['status'] = 0; //ok
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	private function _getLastFriendshipRequest($friend_id){
		$criteria = new CDbCriteria;
		$criteria->condition = 'user_id='.$_POST['user_id'].' && friend_id='.$friend_id;
		$criteria->order = 'id DESC';
	    $model = Friendships::model()->find($criteria);

    	if( isset($model) )
    		return $model;
		else
			return null;
	}

	public function actionGetFriendshipRequests()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'requests'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Friendships::model()->findAll('friend_id='.$_POST['user_id'].' && status=0');

				$requests = array();
				foreach ($model as $key => $request) {
					$id = $request->id;
					$user_id = $request->user->id;
					$username = $request->user->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$request->user->image;

					$requests[] = array('id' => $id, 'user_id' => $user_id, 'username' => $username, 'user_image' => $user_image);
				}

            	$output['status'] = 0; //ok
            	$output['requests'] = $requests;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionAcceptFriendRequest()
	{
		$parameters = array('user_id','auth_code','request_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$request = Friendships::model()->findByPk($_POST['request_id']);
				$request->status = 1;
				if( !$request->save() ){
					$output['status'] = 1;
					$output['errors'] = "problem occured: 1-"+$this->_getErrors($request);
				}

				$friendship_model=new Friendships;
				$friendship_model->user_id = $request->friend_id;
				$friendship_model->friend_id = $request->user_id;
				$friendship_model->status = 1;
				if( !$friendship_model->save() ){
					$output['status'] = 1;
					$output['errors'] = "problem occured: 1-"+$this->_getErrors($friendship_model);
				}

				$follow_model = Fellowships::model()->find('user_id='.$request->user_id.' && '.'follow_id='.$request->friend_id);
			    if( count($follow_model) == 0 ){
					$follow_model = new Fellowships;
					$follow_model->user_id = $request->user_id;
					$follow_model->follow_id = $request->friend_id;
					if( !$follow_model->save() ){
						$output['status'] = 1;
						$output['errors'] = "problem occured: 1-"+$this->_getErrors($follow_model);
					}
				}

				$follow_model = Fellowships::model()->find('user_id='.$request->friend_id.' && '.'follow_id='.$request->user_id);
			    if( count($follow_model) == 0 ){
					$follow_model = new Fellowships;
					$follow_model->user_id = $request->friend_id;
					$follow_model->follow_id = $request->user_id;
					if( !$follow_model->save() ){
						$output['status'] = 1;
						$output['errors'] = "problem occured: 1-"+$this->_getErrors($follow_model);
					}
				}

				if( isset($output['status']) && $output['status'] != 1 ){
					$user = Users::model()->findByPk($_POST['user_id']);
					$notification = array('id'=>'2', 'text'=>$user->name." accepted your friend request", 'user_id'=>$_POST['user_id']);
					$this->_send_notification($request->user_id, json_encode($notification));

					$output['status'] = 0; //ok
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionRejectFriendRequest()	
	{
		$parameters = array('user_id','auth_code','request_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = Friendships::model()->findByPk($_POST['request_id']);
				$model->status = 2;
				if($model->save()){
					$output['status'] = 0; //ok
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionRemoveFriend()	
	{
		$parameters = array('user_id','auth_code','friend_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){

				$logged_user_id = $_POST['user_id'];
				$friend_id = $_POST['friend_id'];
			    $friendship1 = Friendships::model()->find('friend_id='.$logged_user_id.' && user_id='.$friend_id.' && status=1');
			    $friendship2 = Friendships::model()->find('friend_id='.$friend_id.' && user_id='.$logged_user_id.' && status=1');

			    if( isset($friendship1) && isset($friendship2) ){
			    	$friendship1->status = 4;
			    	$friendship2->status = 4;
			    
				    if( $friendship1->save() && $friendship2->save() ){
						$output['status'] = 0; //ok
				    }
				}else{
					$output['status'] = 1;
					$output['errors'] = "problem occured";
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionGetFriends()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'friends'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Friendships::model()->findAll('user_id='.$_POST['user_id'].' && status=1');

				$friends = array();
				foreach ($model as $key => $friendship) {
					$user_id = $friendship->friend->id;
					$username = $friendship->friend->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$friendship->friend->image;

					$friends[] = array('user_id' => $user_id, 'username' => $username, 'user_image' => $user_image, 'is_friend' => $friendship->friend->isFriend($_POST['user_id']), 'is_follow' => $friendship->friend->isFollow($_POST['user_id']));
				}

            	$output['status'] = 0; //ok
            	$output['friends'] = $friends;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	// public function actionCheckIfFriend($friend_id){
	// 	$logged_user_id = $_POST['user_id'];
	//     $friendship = Friendships::model()->find('friend_id='.$friend_id.' && user_id='.$logged_user_id.' && status=1');

	//     if( isset($friendship) ){
	//     	return true;
	//     }else{
	//     	return false;
	//     }
	// }

	public function actionFollow()
	{
		$parameters = array('user_id','auth_code','follow_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$model = new Fellowships;
				$model->user_id = $_POST['user_id'];
				$model->follow_id = $_POST['follow_id'];

				if($model->save()){
					$user = Users::model()->findByPk($_POST['user_id']);
					$notification = array('id'=>'3', 'text'=>$user->name." followed you", 'user_id'=>$_POST['user_id']);
					$this->_send_notification($_POST['follow_id'], json_encode($notification));

					$output['status'] = 0; //ok
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionUnfollow()
	{
		$parameters = array('user_id','auth_code','follow_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Fellowships::model()->find('user_id='.$_POST['user_id'].' && follow_id='.$_POST['follow_id']);

				if($model->delete()){
					$output['status'] = 0; //ok
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionGetUsersYouFollow()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'users'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Fellowships::model()->findAll('user_id='.$_POST['user_id']);

				$users = array();
				foreach ($model as $key => $fellowship) {
					$user_id = $fellowship->follow->id;
					$username = $fellowship->follow->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$fellowship->follow->image;

					$users[] = array('user_id' => $user_id, 'username' => $username, 'user_image' => $user_image, 'is_friend' => $fellowship->follow->isFriend($_POST['user_id']), 'is_follow' => $fellowship->follow->isFollow($_POST['user_id']));
				}

            	$output['status'] = 0; //ok
            	$output['users'] = $users;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionGetUsersFollowYou()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'users'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Fellowships::model()->findAll('follow_id='.$_POST['user_id']);

				$users = array();
				foreach ($model as $key => $fellowship) {
					$user_id = $fellowship->user->id;
					$username = $fellowship->user->name;
					$user_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$fellowship->user->image;

					$users[] = array('user_id' => $user_id, 'username' => $username, 'user_image' => $user_image, 'is_friend' => $fellowship->user->isFriend($_POST['user_id']), 'is_follow' => $fellowship->user->isFollow($_POST['user_id']));
				}

            	$output['status'] = 0; //ok
            	$output['users'] = $users;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionGetEvents()
	{
		$parameters = array('user_id','auth_code');
		$output = array('status'=>'', 'errors'=>'', 'events'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
			    $model = Events::model()->findAll();

				$events = array();
				foreach ($model as $key => $event) {
					$event_id = $event->id;
					$event_name = $event->name;

					$image_path = Yii::getPathOfAlias('webroot').'/uploads/events/'.$event_id;
					$files = array_diff(scandir($image_path, 1), array('..', '.'));
					$images = array();
					foreach ($files as $key => $file) {
						$images[] = Yii::app()->getBaseUrl(true).'/uploads/events/'.$event_id.'/'.$file;
					}

					$users[] = array('event_id' => $event_id, 'event_name' => $event_name, 'images' => $images);
				}

            	$output['status'] = 0; //ok
            	$output['users'] = $users;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	/************* chat ***************/
	public function actionChatHistory()
	{
		$parameters = array('user_id', 'auth_code');
		$output = array('status'=>'', 'errors'=>'', 'history'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->order = 'id ASC';
				$criteria->condition = 'user_id = '.$_POST['user_id'].' || receiver_id = '.$_POST['user_id'];				
			 	$model = Chat::model()->findAll($criteria);
			 	foreach ($model as $key => $chat) {
			 		$uniqe_id = ($chat->user_id > $chat->receiver_id) ? $chat->user_id.'&'.$chat->receiver_id : $chat->receiver_id.'&'.$chat->user_id;

					$id = $chat->id;
					$sender_id = $chat->user_id;
					$sender_name = $chat->sender->name;
					$sender_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->sender->image;
					$receiver_id = $chat->receiver_id;
					$receiver_name = $chat->receiver->name;
					$receiver_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->receiver->image;
					$message = $chat->message;
					$timestamp = $chat->timestamp;

					$history[$uniqe_id] = array('id' => $id, 'sender_id' => $sender_id, 'sender_name' => $sender_name, 'sender_image' => $sender_image, 
												'receiver_id' => $receiver_id, 'receiver_name' => $receiver_name, 'receiver_image' => $receiver_image, 
												'message' => $message, 'timestamp' => $timestamp);
			 	}
			 	$history = isset($history)?array_values($history):array();

				$output['history'] = $history;

				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionChat()
	{
		$parameters = array('user_id', 'auth_code', 'receiver_id');
		$output = array('status'=>'', 'errors'=>'', 'history'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$limit = 20;
				$operator = '>';

				$criteria = new CDbCriteria;
				$criteria->order = 'id DESC';
				$criteria->limit = $limit;
				$criteria->condition = '((user_id = '.$_POST['user_id'].' && receiver_id = "'.$_POST['receiver_id'].'") 
									 || (user_id = '.$_POST['receiver_id'].' && receiver_id = "'.$_POST['user_id'].'"))';				

				if( isset($_POST['last_id']) && is_numeric($_POST['last_id']) && $_POST['last_id'] != -1 && 
					isset($_POST['operator']) && (($_POST['operator'] == '<') || ($_POST['operator'] == '>')) ){
					$criteria->condition = $criteria->condition.' && id '.$_POST['operator'].' '.$_POST['last_id'];
					$operator = '<';

					if( $_POST['operator'] == '>' ){
						$criteria->order = 'id ASC';
					}

				}
				
			 	$model = Chat::model()->findAll($criteria);
			 	$number_of_messages = count($model);

			 	if( $operator == '<' ){
				 	for ($i=0; $i < $number_of_messages; $i++) { 
				 		$chat = $model[$i];
						$id = $chat->id;
						$sender_id = $chat->user_id;
						$sender_name = $chat->sender->name;
						$sender_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->sender->image;
						$receiver_id = $chat->receiver_id;
						$receiver_name = $chat->receiver->name;
						$receiver_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->receiver->image;
						$message = $chat->message;
						$timestamp = $chat->timestamp;

						$history[] = array('id' => $id, 'sender_id' => $sender_id, 'sender_name' => $sender_name, 'sender_image' => $sender_image, 
													'receiver_id' => $receiver_id, 'receiver_name' => $receiver_name, 'receiver_image' => $receiver_image, 
													'message' => $message, 'timestamp' => $timestamp);
				 	}
				 }else if( $operator == '>' ){
				 	for ($i=$number_of_messages-1; $i >= 0; $i--) { 
				 		$chat = $model[$i];
						$id = $chat->id;
						$sender_id = $chat->user_id;
						$sender_name = $chat->sender->name;
						$sender_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->sender->image;
						$receiver_id = $chat->receiver_id;
						$receiver_name = $chat->receiver->name;
						$receiver_image = Yii::app()->getBaseUrl(true).'/uploads/users/'.$chat->receiver->image;
						$message = $chat->message;
						$timestamp = $chat->timestamp;

						$history[] = array('id' => $id, 'sender_id' => $sender_id, 'sender_name' => $sender_name, 'sender_image' => $sender_image, 
													'receiver_id' => $receiver_id, 'receiver_name' => $receiver_name, 'receiver_image' => $receiver_image, 
													'message' => $message, 'timestamp' => $timestamp);
				 	}
				 }



				$output['history'] = $history;

				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionSendMessage()
	{
		$parameters = array('user_id', 'auth_code', 'receiver_id', 'message');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$chat = new Chat;
				$chat->user_id = $_POST['user_id'];
				$chat->receiver_id = $_POST['receiver_id'];
				$chat->message = $_POST['message'];
				$chat->timestamp = date('Y-m-d H:i:s');
				if( $chat->save() ){
					$notification = array('id'=>'6', 'text'=>$chat->sender->name." sent you new message", 'user_id'=>$chat->user_id, 'message_id'=>$chat->id);
					$this->_send_notification($chat->receiver_id, json_encode($notification));

					$output['status'] = 0;
				}else{
					$output['status'] = 1;
					$output['errors'] = _getErrors($chat);
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionDeleteMessage()
	{
		$parameters = array('user_id', 'auth_code', 'message_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$chat = Chat::model()->deleteByPk($_POST['message_id']);
				$output['status'] = 0;
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	public function actionDeleteChat()
	{
		$parameters = array('user_id', 'auth_code', 'receiver_id');
		$output = array('status'=>'', 'errors'=>'');

		// collect user input data
		if( $this->_checkParameters($parameters) ){

			if( $this->_verify_user() ){
				$criteria = new CDbCriteria;
				$criteria->condition = '(user_id = '.$_POST['user_id'].' && receiver_id = "'.$_POST['receiver_id'].'") 
									 || (user_id = '.$_POST['receiver_id'].' && receiver_id = "'.$_POST['user_id'].'") ';				
			 	$model = Chat::model()->deleteAll($criteria);

				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = "user not verified";
			}

		}else{
			$output['status'] = 1;
			$output['errors'] = "inputs problem";
		}

        echo json_encode($output);
	}

	/****************************************/
	/****************************************/
	/****************************************/

	private function _checkParameters($parameters){

		foreach ($parameters as $key => $parameter) {
			if(!isset($_POST[$parameter]) || empty($_POST[$parameter])){
				return false;
			}
		}

		return true;
	}

	private function _getErrors($model){
		$errors = '';
		foreach ($model->errors as $key => $element) {
			foreach ($element as $key => $error) {
				$errors .= $error.', ';
			}
		}

		return $errors;
	}

	private function _verify_user(){
		$user = Users::model()->findByPk($_POST['user_id']);

        if( isset($user) && $user->auth_code == $_POST['auth_code'] ){

			return true;
		}else{
			return false;
		}
	}

	private function _checkEmail($email){
        $user = Users::model()->findByAttributes(array('email' => $email));

        if( isset($user) ){
    		return true;
        }

        return false;
	}

	private function _login($email, $password, $parse_objectId){
        $user = Users::model()->findByAttributes(array('email' => $email));
        $password = sha1($password);

        if( isset($user) && $user->initialPassword == $password ){
        	$user->auth_code = substr(md5(uniqid(rand(), true)), 16, 16);
			$user->parse_objectId = $parse_objectId;

        	if( $user->save() ){
        		return $user;
        	}
        }

        return null;
	}

	private function _send_notification($user_id, $notification_text){
		$user = Users::model()->findByPk($user_id);

        $url = 'https://api.parse.com/1/push';
		$appId = 'ktYc1cAAIQZsiVlQRglcU7Puzn82dkXK2yXDhP6y';
		$restKey = 'WELkMwAkl20v8nh0O1k7j3rjM2nZfMKyU8D6CdMS';

		$push_payload = json_encode(array(
	        "where" => array(
                "objectId" => $user->parse_objectId,
	        ),
	        "data" => array(
                "alert" => $notification_text
	        )
		));

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_PORT,443);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$push_payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_HTTPHEADER,
		        array("X-Parse-Application-Id: " . $appId,
		                "X-Parse-REST-API-Key: " . $restKey,
		                "Content-Type: application/json"));

		$response = curl_exec($ch);
		curl_close($ch);
		// return $response['result'];
	}

}