<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/user/controllers/UserController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * HTTP actions for Landlord sign-in, sign-out, registration and identity verification. Delegates registration validation and writes to RegistrationService.
 */
namespace BoardNest\Modules\User\Controllers;
use BoardNest\Core\{Controller,Session,CSRF,Validator};
use BoardNest\Modules\User\{Models\Landlord,Services\RegistrationService};
final class UserController extends Controller {
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Landlord{return new Landlord($this->db());}
    /** STUDY - registration(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function registration(): RegistrationService{return new RegistrationService($this->app);}
    /** VIVA: Show the sign-in page or redirect a signed-in landlord to the appropriate status/dashboard page. */
    public function login(): never {
        $user = $this->auth()->user();
        if ($user) $this->res()->redirect($this->auth()->approved($user) ? '/dashboard' : '/registration/status');
        $this->guestPage('user/login',['title'=>'Sign in']);
    }
    /** VIVA: Validate credentials, enforce a session-level attempt limit, then sign in and redirect according to approval. */
    public function loginPost(): never {$this->post(function(){
        $email=strtolower($this->req()->field('email'));$password=is_string($_POST['password']??null)?$_POST['password']:'';
        // A small session-level brute-force limiter. Deploy an IP/account-based limiter too.
        if((int)($_SESSION['login_block_until']??0)>time())throw new \DomainException('Too many attempts. Try again later.');
        $row=$this->model()->account($email);
        if(!$row||$row['role']!=='landlord'||in_array($row['status'],['banned','suspended'],true)||!password_verify($password,$row['password_hash'])) {
            $_SESSION['login_failures']=(int)($_SESSION['login_failures']??0)+1;
            if($_SESSION['login_failures']>=8){$_SESSION['login_block_until']=time()+900;$_SESSION['login_failures']=0;}
            throw new \DomainException('Invalid credentials or account unavailable.');
        }
        unset($_SESSION['login_failures'],$_SESSION['login_block_until']);
        $this->auth()->signIn((int)$row['user_id']);
        return $this->auth()->approved() ? '/dashboard' : '/registration/status';
    },'/login');}
    /** VIVA: Require a CSRF token even for logout, then terminate the authenticated session. */
    public function logout(): never {
        \BoardNest\Core\CSRF::enforce();
        $this->auth()->signOut();
        $this->res()->redirect('/login');
    }
    /** STUDY - register(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function register(): never {
        if($this->auth()->user())$this->res()->redirect('/registration/status');
        $this->guestPage('user/register',['title'=>'Register as landlord']);
    }
    /** VIVA: Create a pending account and establish a session before NIC submission. */
    public function registerPost(): never {$this->post(function(){
        $id=$this->registration()->register();$this->auth()->signIn($id);return '/registration/identity';
    },'/register');}
    /** STUDY - identity(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function identity(): never {
        $u = $this->auth()->require(false);
        if ($this->auth()->approved($u) || $u['identity_status'] === 'submitted') {
            $this->res()->redirect('/registration/status');
        }
        $this->guestPage('user/identity', ['landlord' => $u]);
    }
    /** VIVA: Send NIC/mobile/document information to RegistrationService, then show approval status. */
    public function identityPost(): never {$this->post(function(){
        $u=$this->auth()->require(false);$this->registration()->identity($u);return '/registration/status';
    },'/registration/identity');}
    /** VIVA: Pending or submitted identity requests remain on the status page until Admin approval. */
    public function status(): never {
        $u=$this->auth()->require(false);
        if($this->auth()->approved($u))$this->res()->redirect('/dashboard');
        $this->guestPage('user/status',['landlord'=>$u]);
    }
    // Legacy onboarding links redirect to normal, approval-protected pages.
    /** VIVA: Compatibility route to the approved landlord property-create page. */
    public function propertySetup(): never {
        $this->auth()->require();
        $this->res()->redirect('/property/new');
    }
    /** VIVA: Compatibility route to the approved landlord room-create page. */
    public function roomSetup(): never {
        $this->auth()->require();
        $this->res()->redirect('/room/new');
    }
}
