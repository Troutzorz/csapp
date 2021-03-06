<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mainpage extends CI_Controller
{
    public function index()
    {
        if (!isset($_SESSION['UserID']))
            redirect('Login/logout');
        //Create new user and load its data
        $user = new User_model;
        if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
        
        //Load the appropriate mainpage
        if ($user->isAdmin())
            $this->load->view('MainPages/admin_main_page', array('user'=>$user));
        elseif ($user->isProgramChair())
            $this->load->view('MainPages/pc_main_page', array('user'=>$user));
        elseif ($user->isAdvisor())
            $this->load->view('MainPages/advisor_main_page', array('user'=>$user));
        elseif ($user->isStudent())
            $this->load->view('MainPages/student_main_page', array('user'=>$user));
        else
            $this->load->view('MainPages/guest_main_page', array('user'=>$user));
    }
    
    public function student()
    {
        //Load the student mainpage if user is a student
        if (!isset($_SESSION['UserID']))
            redirect('Login/logout');
        $user = new User_model;
        if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
        if ($user->isStudent())
            $this->load->view('MainPages/student_main_page', array('user'=>$user));
        else
            index();
    }
    
    public function advisor()
    {
        //Load the advisor mainpage if user is an advisor
        if (!isset($_SESSION['UserID']))
            redirect('Login/logout');
        $user = new User_model;
        if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
        if ($user->isAdvisor())
            $this->load->view('MainPages/advisor_main_page', array('user'=>$user));
        else
            index();
    }
    
    public function programChair()
    {
        //Load the program chair mainpage if user is a program chair
        if (!isset($_SESSION['UserID']))
            redirect('Login/logout');
        $user = new User_model;
        if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
        if ($user->isProgramChair())
            $this->load->view('MainPages/pc_main_page', array('user'=>$user));
        else
            index();
    }
    
    public function admin()
    {
        //Load the admin mainpage if user is a admin
        if (!isset($_SESSION['UserID']))
            redirect('Login/logout');
        $user = new User_model;
        if (!$user->loadPropertiesFromPrimaryKey($_SESSION['UserID']))
            redirect('Login/logout');
        if ($user->isAdmin())
            $this->load->view('MainPages/admin_main_page', array('user'=>$user));
        else
            index();
    }
    
    public function guest()
    {
        //Load the guest mainpage
        $this->load->view('MainPages/guest_main_page', array('user'=>$user));
    }
    
    //the following two function were created by Scott
    //There were created to allow advisor to see students advising forms
    
    //loads a view with all the students and their ids
     public function loadAllStudents()
    {
        //
        $puid = $_SESSION['UserID'];
        
        $profmod = new user_model();
        $profmod->loadPropertiesFromPrimaryKey($puid);
        
        $student_list = $profmod->getAdvisees();
        
        $pdata = array('students' => $student_list);
        
        $this->load->view('all_students_view', $pdata);
        
    }
    
    //sets a session variable for advising form based on what was clicked
    public function loadStudentID()
    {
        //if(isset($_POST['StudID']))
       // {
            //print_r($_POST['StudID']);
            $StudentID = $_POST['StudID'];
            $_SESSION['StudCWID'] = $StudentID;
            //print_r("session: " . $_SESSION['StudCWID']. "CWID");
       // }
    }
}

