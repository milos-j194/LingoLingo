<?php
/**
* Miloš Jovanović 2013/0669
**/
namespace App\Controllers;
use App\Models\UserModel;
use App\Models\AccountStatusModel;
use App\Models\AccountTypeModel;
use App\Models\LanguageModel;
use App\Models\QuestionModel;

/**
* Administrator – klasa za administratorovu stranicu
*
* @version 1.0
*/

class Administrator extends BaseController
{
    /**
    * Dohvatanje svih flag-ovanih pitanja
    */
    public function getFlaggedQuestions()
    {
        $this->receiveAJAX();
        
        $flaggedQuestions = array();
        
        $questionsModel = new QuestionModel();
        $languageModel = new LanguageModel();
        
        $dbFlaggedQuestions = $questionsModel->GetAllFlaggedQuestions();
        foreach($dbFlaggedQuestions as $dbFlaggedQuestion)
        {
            $idQuestion = $dbFlaggedQuestion->IdQuestion;
            $languageName = $languageModel->GetLanguageName($dbFlaggedQuestion->IdLanguage);
            $questionText = $dbFlaggedQuestion->QuestionText;
            $answerText = $dbFlaggedQuestion->AnswerText;
            
            $flaggedQuestions[] = array(
                'IdQuestion' => $idQuestion,
                'Language'   => $languageName,
                'Question'   => $questionText,
                'Answer'     => $answerText
                );
        }
        
        $this->sendAJAX(json_encode($flaggedQuestions));
    }
    
    /**
    * Brisanje prosleđenog pitanja
    */
    public function deleteQuestion()
    {
        $this->receiveAJAX();
        
        $idQuestionToBeDeleted = $this->request->getVar('IdQuestion', FILTER_SANITIZE_STRING);
        
        $questionModel = new QuestionModel();
        $questionModel->DeleteQuestion($idQuestionToBeDeleted);
    }
    
    /**
    * Dohvatanje svih Usera koji čekaju na odobrenje
    */
    public function GetUsersPendingApproval()
    {
        // separate professors and admins
        
        $this->receiveAJAX();
        
        $pendingProfessors = array();
        $pendingAdmins = array();
        
        $userModel = new UserModel();
        $accountStatusModel = new AccountStatusModel();
        $accountTypeModel = new AccountTypeModel();
        
        $pendingAccountStatusID = $accountStatusModel->GetStatusIDByName(ACCOUNT_STATUS_PENDING);
        $professorAccountID = $accountTypeModel->GetTypeIDByName(ACCOUNT_TYPE_PROFESSOR);
        $adminAccountID = $accountTypeModel->GetTypeIDByName(ACCOUNT_TYPE_ADMIN);
        
        $dbPendingProfessors = $userModel->GetUsersByAccountStatusIdAndAccountTypeId($pendingAccountStatusID, $professorAccountID);
        foreach($dbPendingProfessors as $dbPendingProfessor)
        {
            $idUser = $dbPendingProfessor['IdUser'];
            $firstName = $dbPendingProfessor['FirstName'];
            $email = $dbPendingProfessor['Email'];
            
            $pendingProfessors[] = array(
                'IdUser'    =>  $idUser,
                'FirstName' =>  $firstName,
                'Email'     =>  $email
            );
        }
        
        $dbPendingAdmins = $userModel->GetUsersByAccountStatusIdAndAccountTypeId($pendingAccountStatusID, $adminAccountID);
        foreach($dbPendingAdmins as $dbPendingAdmin)
        {
            $idUser = $dbPendingAdmin['IdUser'];
            $firstName = $dbPendingAdmin['FirstName'];
            $email = $dbPendingAdmin['Email'];
            
            $pendingAdmins[] = array(
                'IdUser'    =>  $idUser,
                'FirstName' =>  $firstName,
                'Email'     =>  $email
            );
        }
        
        $result = array(
            'PendingProfessors' => $pendingProfessors,
            'PendingAdmins'     => $pendingAdmins
        );
        
        $this->sendAJAX(json_encode($result));
    }
    
    /**
    * Odobrenje profila sa čekanja
    */
    public function ApproveAccount()
    {
        $this->receiveAJAX();
        $idUser = $this->request->getVar('IdUser', FILTER_SANITIZE_STRING);
        
        $accountStatusModel = new AccountStatusModel();
        $idStatusApproved = $accountStatusModel->GetStatusIDByName(ACCOUNT_STATUS_APPROVED);
        
        $userModel = new UserModel();
        $userModel->ChangeAccountStatus($idUser, $idStatusApproved);
    }
    
    /**
    * Brisanje naloga sa čekanja
    */
    public function DeleteAccount()
    {
        $this->receiveAJAX();
        $idUser = $this->request->getVar('IdUser', FILTER_SANITIZE_STRING);
        
        $userModel = new UserModel();
        $userModel->DeleteAccount($idUser);
    }
}