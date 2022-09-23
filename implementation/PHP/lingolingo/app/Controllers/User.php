<?php
/**
 * Miloš Jovanović 2013/0669
 * Ksenija Bulatović 2019/0730
**/

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AccountStatusModel;
use App\Models\AccountTypeModel;
use App\Models\LanguageModel;
use App\Models\QuestionModel;

 /**
* User – klasa register i login
*
* @version 1.0
*/

class User extends BaseController
{
    /**
    * Logovanje na sajt
    */
    public function login()
    {
            $this->receiveAJAX();
            $username = htmlspecialchars($this->request->getVar('username'), ENT_COMPAT, "UTF-8");
            $pass = htmlspecialchars($this->request->getVar('password'), ENT_COMPAT, "UTF-8");
            
            $model = new UserModel();
            $user = $model->GetUser($username);
            
            $loginResult = array(
                "LoginSuccessful" => false, 
                "Username" => null,
                "FirstName" => null,
                "LastName" => null,
                "UserTypeId" => null,
                "Message" => null
                );
        
            if(isset($user))
            {
                if(password_verify($pass, $user->Password))
                {
                    
                    $accModel = new AccountStatusModel();
                    $accountStatus = $accModel->GetStatusNameByID($user->IdStatus);

                    if($accountStatus == ACCOUNT_STATUS_APPROVED)
                    {
                        $_SESSION['username'] = $username;
                        
                        $loginResult["LoginSuccessful"] = true;
                        $loginResult["Username"] = $username;
                        $loginResult["FirstName"] = $user->FirstName;
                        $loginResult["LastName"] = $user->LastName;
                        $loginResult["UserTypeId"] = $user->IdUserType;
                    }
                    else if($accountStatus == ACCOUNT_STATUS_PENDING)
                    {
                        $loginResult["Message"] = LOGIN_PENDING_APPROVAL;
                    }
                    else if($accountStatus == ACCOUNT_STATUS_SUSPENDED)
                    {
                        $loginResult["Message"] = LOGIN_ACCOUNT_SUSPENDED;
                    }
                }
                else
                {
                    $loginResult["Message"] = LOGIN_WRONG_PASSWORD;
                }
            }
            else
            {
                $loginResult["Message"] = LOGIN_UNKNOWN_USER;
            }  
            
            $this->sendAJAX(json_encode($loginResult));
        }
        
    /**
    * Registracija novog korisnika
    */
    public function register()
    {
        $this->receiveAJAX();
        $firstName = htmlspecialchars($this->request->getVar('FirstName'), ENT_COMPAT, "UTF-8");
        $lastName = htmlspecialchars($this->request->getVar('LastName'), ENT_COMPAT, "UTF-8");
        $username = htmlspecialchars($this->request->getVar('Username'), ENT_COMPAT, "UTF-8");
        $email = htmlspecialchars($this->request->getVar('Email'), ENT_COMPAT, "UTF-8");
        $password = htmlspecialchars($this->request->getVar('Password'), ENT_COMPAT, "UTF-8");
        $confirmPassword = htmlspecialchars($this->request->getVar('ConfirmPassword'), ENT_COMPAT, "UTF-8");
        $accountType = htmlspecialchars($this->request->getVar('AccountType'), ENT_COMPAT, "UTF-8");

        $registerResult = array(
            "RegisterSuccessful" => false,
            "Message" => null
            );
        
        $userModel = new UserModel();
        $accountStatusModel = new AccountStatusModel();
        $accountTypeModel = new AccountTypeModel();
        
        if(empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($accountType))
        {
            $registerResult["Message"] = REGISTRATION_MISSING_INFO;
        }
        else if($password != $confirmPassword)
        {
            $registerResult["Message"] = REGISTRATION_PASSWORD_MISMATCH;
        }
        else if($userModel->CheckUsernameTaken($username))
        {
            $registerResult["Message"] = REGISTRATION_USERNAME_TAKEN;
        }
        else if($userModel->CheckEmailTaken($email))
        {
            $registerResult["Message"] = REGISTRATION_EMAIL_TAKEN;
        }
        else if(strlen($password) > 70)
        {
            $registerResult["Message"] = REGISTRATION_PASSWORD_TOO_LONG;
        }
        else
        {
            $accountTypeId = null;
            $accountStatusId = null;
            if($accountType == ACCOUNT_TYPE_PLAYER)
            {
                $accountTypeId = $accountTypeModel->GetTypeIDByName(ACCOUNT_TYPE_PLAYER);
                $accountStatusId = $accountStatusModel->GetStatusIDByName(ACCOUNT_STATUS_APPROVED);
            }
            else if($accountType == ACCOUNT_TYPE_PROFESSOR)
            {
                $accountTypeId = $accountTypeModel->GetTypeIDByName(ACCOUNT_TYPE_PROFESSOR);
                $accountStatusId = $accountStatusModel->GetStatusIDByName(ACCOUNT_STATUS_PENDING);
            }
            else if($accountType == ACCOUNT_TYPE_ADMIN)
            {
                $accountTypeId = $accountTypeModel->GetTypeIDByName(ACCOUNT_TYPE_ADMIN);
                $accountStatusId = $accountStatusModel->GetStatusIDByName(ACCOUNT_STATUS_PENDING);
            }
            else
            {
                $registerResult["Message"] = REGISTRATION_INCORRECT_ACCOUNT_TYPE;
            }
            
            if(isset($accountStatusId) && isset($accountTypeId))
            {
                $passwordHash = $this->encryptPassword($password);
                $userModel->RegisterNewUser($firstName, $lastName, $username, $passwordHash, $email, $accountTypeId, $accountStatusId);
                
                $registerResult['RegisterSuccessful'] = true;
                $registerResult['Message'] = REGISTRATION_SUCCESSFUL;
            }
        }
        
        $this->sendAJAX(json_encode($registerResult));
    }
}
 