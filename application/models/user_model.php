<?php

/**
 * Summary of User_model
 * 
 * A model used to describe a single user of the CSC Web App project
 */
class User_model extends CI_Model
{
    // Member variables, use getter / setter functions for access
    private $userID = null;
    private $emailAddress = null;
    private $passwordHash = null;
    private $name = null;
    private $roles = array();
    
    // Constants to represent the various user roles as reflected in the CSC Web App database
    // If the table `Roles` or any of its rows are ever modified, reflect those changes in these constants
    const ROLE_ADMIN = 1;
    const ROLE_PROGRAM_CHAIR = 2;
    const ROLE_ADVISOR = 3;
    const ROLE_STUDENT = 4;
    
    /**
     * Main Constructor for User_model
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Summary of loadPropertiesFromPrimaryKey
     * Loads a user model's data from the database into this object using a UserID as a primary key lookup
     * 
     * @param int $userID The primary key (UserID) to lookup user properties in the database with
     * @return boolean True if a user model's properties were successfully loaded from database, false otherwise
     */
    public function loadPropertiesFromPrimaryKey($userID)
    {
        if($userID != null)
        {
            if(filter_var($userID, FILTER_VALIDATE_INT))
            {
                $results = $this->db->get_where('Users', array('UserID' => $userID), 1);
                
                if($results->num_rows() > 0)
                {
                    $row = $results->row_array();
                    
                    $this->userID = $row['UserID'];
                    $this->emailAddress = $row['EmailAddress'];
                    $this->passwordHash = $row['PasswordHash'];
                    $this->name = $row['Name'];
                    
                    $role_results = $this->db->get_where('UserRoles', array('UserID' => $userID));
                    
                    if($role_results->num_rows() > 0)
                    {
                        foreach($role_results->result_array() as $row)
                        {
                            array_push($this->roles, $row['RoleID']);
                        }
                    }
                    
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Summary of loadPropertiesFromEmailAddress
     * Loads a user model's data from the database into this object using an email address as lookup
     * 
     * @param mixed $emailAddress The email address to lookup user properties in the database with
     * @return boolean True if a user model's properties were successfully loaded from database, false otherwise
     */
    public function loadPropertiesFromEmailAddress($emailAddress)
    {
        if($emailAddress != null)
        {
            $emailAddress = filter_var($emailAddress, FILTER_SANITIZE_EMAIL);
            
            if(filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
            {
                $results = $this->db->get_where('Users', array('EmailAddress' => $emailAddress), 1);
                
                if($results->num_rows() > 0)
                {
                    $row = $results->row_array();
                    
                    $this->userID = $row['UserID'];
                    $this->emailAddress = $row['EmailAddress'];
                    $this->passwordHash = $row['PasswordHash'];
                    $this->name = $row['Name'];
                    
                    $role_results = $this->db->get_where('UserRoles', array('UserID' => $userID));
                    
                    if($role_results->num_rows() > 0)
                    {
                        foreach($role_results->result_array() as $row)
                        {
                            array_push($this->roles, $row['RoleID']);
                        }
                    }
                    
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Summary of setPassword
     * Sets the password for the user model and associates is hash with the passwordHash of the model
     * 
     * @param string $password The new password to set for this user model
     */
    public function setPassword($password)
    {
        $this->passwordHash = hash('sha512', $password);
    }
    
    /**
     * Summary of getPasswordHash
     * Get the password hash string associated with this user model
     * 
     * @return string The hash of the password associated with this user model or null if model not saved in database
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }
    
    /**
     * Summary of getUserID
     * Get the UserID (Primary key) of this user model
     * 
     * @return int The user id associated with this user model or null if model not saved in database
     */
    public function getUserID()
    {
        return $this->userID;
    }
    
    /**
     * Summary of getEmailAddress
     * Get the email address of this user model
     * 
     * @return string The email address associated with this user model or null if model not saved in database
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }
    
    /**
     * Summary of setEmailAddress
     * Set the email address to be assoicated with this user model
     * 
     * @param string $emailAddress The email address to associate with this user model
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = filter_var($emailAddress, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Summary of setName
     * Set the name of the user
     * 
     * @param string $name The name to associate with this user model
     */
    public function setName($name)
    {
        $this->name = filter_var($name, FILTER_SANITIZE_MAGIC_QUOTES);
    }
    
    /**
     * Summary of getName
     * Get the name of the user
     * 
     * @return string The name associated with this user model or null if model not saved in database
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Summary of isStudent
     * Check whether this user has the role of a student
     * 
     * @return boolean True is the user has a student role, false otherwise
     */
    public function isStudent()
    {
        return in_array(self::ROLE_STUDENT, $this->roles);
    }
    
    /**
     * Summary of isAdmin
     * Check whether this user has the role of a administrator
     * 
     * @return boolean True is the user has an administrator role, false otherwise
     */
    public function isAdmin()
    {
        return in_array(self::ROLE_ADMIN, $this->roles);
    }
    
    /**
     * Summary of isProgramChair
     * Check whether this user has the role of program chair
     * 
     * @return boolean True is the user has a program chair role, false otherwise
     */
    public function isProgramChair()
    {
        return in_array(self::ROLE_PROGRAM_CHAIR, $this->roles);
    }
    
    /**
     * Summary of isAdvisor
     * Check whether this user has the role of a advisor
     * 
     * @return boolean True is the user has a advisor role, false otherwise
     */
    public function isAdvisor()
    {
        return in_array(self::ROLE_ADVISOR, $this->roles);
    }
    
    /**
     * Summary of addRole
     * Adds a role to the user model if the role isn't already enabled
     * 
     * @param int $roleType The role to add to the user (see ROLE constants)
     */
    public function addRole($roleType)
    {
        if(!in_array($roleType, $this->roles))
        {
            array_push($this->roles, $roleType);
        }
    }
    
    /**
     * Summary of removeRole
     * Removes a role from the user model if the role was enabled
     * 
     * @param int $roleType The role to remove from the user (see ROLE constants)
     */
    public function removeRole($roleType)
    {
        if(in_array($roleType, $this->roles))
        {
            unset($this->roles[array_search($roleType, $this->roles)]);
        }
    }
    
    /**
     * Summary of update
     * Update existing rows in the database associated with this user model with newly modified information
     * 
     * @return boolean True if all rows associated with this model were successfully modified in the database, false otherwise
     */
    public function update()
    {
        if($this->userID != null && filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL))
        {
            $data = array('EmailAddress' => $this->emailAddress, 'PasswordHash' => $this->passwordHash, 'Name' => $this->name);
            
            $this->db->where('UserID', $this->userID);
            $this->db->update('Users', $data);
            
            $sum = $this->db->affected_rows();
            
            $this->db->where('UserID', $this->userID);
            $this->db->delete('UserRoles');
            
            $roledata = array();
            
            foreach($this->roles as $role)
            {
                array_push($roledata, array('UserID' => $this->userID, 'RoleID' => $role));
            }
            
            $this->db->insert_batch('UserRoles', $roledata);
            
            return $sum > 0;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Summary of create
     * Save a new user model into the Users table in the database and all associated user role rows into the UserRoles table
     * and binds the newly generated row id to the user id property of the user model
     * 
     * @return boolean True if all rows were successfully saved in the database, false otherwise
     */
    public function create()
    {   
        if(filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL))
        {
            $data = array('EmailAddress' => $this->emailAddress, 'PasswordHash' => $this->passwordHash, 'Name' => $this->name);
            
            $this->db->insert('Users', $data);
            
            if($this->db->affected_rows() > 0)
            {
                $this->userID = $this->db->insert_id();
                
                foreach($roles as $role)
                {
                    $roledata = array('UserID' => $this->userID, 'RoleID' => $role);
                    
                    $this->db->insert('UserRoles', $roledata);
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     * Summary of delete
     * Delete this user from the database and all associated models for this user
     * 
     * @return boolean True if the model and associated models were successfully deleted, false otherwise
     */
    public function delete()
    {
        if($this->userID != null)
        {
            $this->db->where('UserID', $this->userID);
            $this->db->delete('UserRoles');
            
            $this->db->where('UserID', $this->userID);
            $this->db->delete('Users');
            
            return $this->db->affected_rows() > 0;
        }
        else
        {
            return false; 
        }
    }
    
    /**
     * Summary of authenticate
     * Check a submitted password guess against this user model's hashed password from the database
     * Usees a constant time string comparison to prevent timing attacks
     * 
     * @param string $passwordGuess The raw password used to authenticate against this user model
     * @return boolean True if the password hashes and matches this user model, false otherwise
     */
    public function authenticate($passwordGuess)
    {
        $hashedPasswordGuess = hash('sha512', $passwordGuess);
        
        $len = strlen($hashedPasswordGuess);
        
        $finalFlag = true;
        
        for($i=0;$i<$len;$i++)
        {
            if ($finalFlag && $hashedPasswordGuess[$i] != $this->passwordHash[$i])
            {
                $finalFlag = false;
            }
        }
        
        return $finalFlag;
    }
}
