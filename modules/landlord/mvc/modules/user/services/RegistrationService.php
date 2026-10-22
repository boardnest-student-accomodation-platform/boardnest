<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/user/services/RegistrationService.php
 * Role: Identity/registration workflow; applicant must not self-approve.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Business rules for creating a pending landlord account and submitting NIC details. Does not grant Admin approval.
 */
namespace BoardNest\Modules\User\Services;
use BoardNest\Core\{App,Upload,Validator};
use BoardNest\Modules\User\Models\Landlord;
final class RegistrationService {
    private Landlord $model;
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private App $app){$this->model=new Landlord($app->db());}
    /** VIVA: Validate account fields, password and consent; store a password hash, not plain text. */
    public function register(): int {
        $p=$this->app->request();
        $name=Validator::text($p->field('name'),'Name',3,100);
        $email=strtolower($p->field('email'));
        if(strlen($email)>100||!filter_var($email,FILTER_VALIDATE_EMAIL))throw new \DomainException('Enter a valid email address.');
        $phone=Validator::text($p->field('phone'),'Phone number',9,15);
        $pass=is_string($_POST['password']??null)?$_POST['password']:'';
        if(strlen($pass)<8||strlen($pass)>72)throw new \DomainException('Password must be between 8 and 72 bytes.');
        if($p->field('consent')!=='1')throw new \DomainException('Please accept the registration terms.');
        if($this->model->account($email))throw new \DomainException('This email address is already registered.');
        try {return $this->model->create($name,$email,$phone,password_hash($pass,PASSWORD_DEFAULT));}
        catch(\PDOException $e){if($e->getCode()==='23000')throw new \DomainException('Email is already registered.');throw $e;}
    }
    /** VIVA: Validate Sri Lankan NIC format and safely store identity evidence; roll back the file on DB failure. */
    public function identity(array $user): void {
        if ($user['identity_status'] === 'approved') throw new \DomainException('Your identity is already approved.');
        if ($user['identity_status'] === 'submitted') throw new \DomainException('Your documents are already awaiting review.');
        $this->checkOpen($user);
        $nic=strtoupper($this->app->request()->field('nic'));
        if(!preg_match('/^(?:\d{9}[VX]|\d{12})$/',$nic))throw new \DomainException('Enter a valid Sri Lankan NIC number.');
        $phone=Validator::text($this->app->request()->field('phone'),'Mobile',9,15);
        $upload=$this->app->request()->file('document');
        if(!$upload)throw new \DomainException('Upload a NIC image or PDF.');
        $path=Upload::save($upload,'nic',true,(int)$user['landlord_id']);
        try {$this->model->identity((int)$user['landlord_id'],$nic,$phone,$path);}
        catch(\Throwable $e){Upload::delete($path,true);throw $e;}
    }
    /** VIVA: Enforce the account registration deadline if one exists. */
    public function checkOpen(array $user): void {
        if($user['registration_deadline']===null)return;
        if((int)$this->app->db()->value('SELECT NOW() > ?',[ $user['registration_deadline']]))
            throw new \DomainException('Your 30-day registration window has expired. Contact an administrator.');
    }
}
