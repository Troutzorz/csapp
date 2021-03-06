<?php
class Activation extends CI_Controller
{
	public function index()
	{
		$user = new User_model;
		
		if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
		
		if (!$user->isAdvisor())
			redirect('Login/logout');
		
		$data = array(
			'user' => $user
		);
		
		$this->load->view('activation_index_view', $data);
	}
	
	//Sets a user's password to a random password
	//	Emails user the new password and their username
	//		And a link to login
	//	Email is either the user's set email, or if passed
	//					the optional passed one
	public function send($userID = NULL, $email = NULL)
	{
		$session_user = new User_model;
		
		if (!$session_user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
		
		if (!$session_user->isAdvisor())
			redirect('Login/logout');
		
		$this->load->library('email');

		$user = new User_model();
		$user->loadPropertiesFromPrimaryKey($userID);

		if($user->getAdvisor()->getUserID() != $session_user->getUserID())
			redirect('Login/logout');
		
		//Loads user's email if optional email wasn't set
		if ($email == NULL)
			$email = $user->getEmailAddress();

		//Array of characters to generate password
		$charset = array(
		        '!','@','#','$','%','^','&','*','(',')',
			'~','=','+','_','-','?','/','>','<','.',
			'0','1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j',
			'k','l','m','n','o','p','q','r','s','t',
			'u','v','w','x','w','z',
			'A','B','C','D','E','F','G','H','I','J',
			'K','L','M','N','O','P','Q','R','S','T',
			'U','V','W','X','W','Z'
		);
		
		//Generate random password
		$passlen = mt_rand(8,12);
		$pass = NULL;
		for ($i = 0; $i < $passlen; $i++)
			$pass = $pass.$charset[mt_rand(0, count($charset)-1)];

		//Set user password
		//Email user their login information
		$this->email->from   ('williamgkeen@gmail.com', 'Admin Name');
		$this->email->to     ('williamgkeen@gmail.com');
		$this->email->subject('Subject');
		$this->email->message(
			'Password: '.$pass. "\r\n".
			'Username: '.$email."\r\n"
			);
		$this->email->send(FALSE);
		$this->email->print_debugger(array('headers','subject','body'));
		$user->setPassword($pass);

		//Email user their login information	
		$this->load->library('email');
		$config['protocol'] = 'smtp';
		$config['smpt_crypt'] = 'ssl';
		$config['smtp_host'] = 'ssl://smtp.gmail.com';
		$config['smtp_port'] = '465';
		$config['smtp_user'] = 'testseniorcapstone@gmail.com';
		$config['smtp_pass'] = 'testpass';
		$config['mailtype'] = 'html';
		$config['charset'] = 'utf-8';
		$config['newline'] = "\r\n";
		$config['validate'] = FALSE;
		$config['bcc_batch_mode'] = FALSE;
		$config['bcc_batch_size'] = 200;
		$this->email->initialize($config);
		
		$this->email->from('testseniorcapstone@gmail.com', 'Senior');
		$list = array('testseniorcapstone@gmail.com');
		$this->email->to($list);
		$this->email->reply_to('testseniorcapstone@gmail.com', 'Senior');
		$this->email->subject('Subject');
		$this->email->message('Email works great!');

		if ($user->update() && $this->email->send())
			$_SESSION['activation.message'] = "Success!";
		else
		{
			$_SESSION['activation.error'] = "Sending email failed!<br />" . $this->email->print_debugger();
		}
		
		redirect('Activation/index');
	}
}
