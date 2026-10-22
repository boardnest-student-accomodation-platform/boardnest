<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Controller.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Base controller shared by Landlord features. Provides request/database helpers, protected page rendering, and a common CSRF-protected POST handler.
 */
namespace BoardNest\Core;
abstract class Controller {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(protected App $app) {}
    /** STUDY - db(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    protected function db(): Database {return $this->app->db();}
    /** STUDY - req(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    protected function req(): Request {return $this->app->request();}
    /** STUDY - auth(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    protected function auth(): Auth {return $this->app->auth();}
    /** STUDY - res(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    protected function res(): Response {return $this->app->response();}
    /** VIVA: Render an authenticated feature view inside the common sidebar/header layout. */
    protected function page(string $view,array $data=[],string $active='dashboard',bool $approved=true): never {
        $landlord=$this->auth()->require($approved);
        $content=$this->app->view()->render($view,$data+['landlord'=>$landlord]);
        $this->res()->html($this->app->view()->layout($content,(string)($data['title']??'Landlord'),$landlord,$active));
    }
    /** VIVA: Render public/login-related content with the separate authentication layout. */
    protected function guestPage(string $view,array $data=[]): never {
        $this->res()->html($this->app->view()->render($view,$data+['flash'=>Session::pullFlash()]));
    }
    /** VIVA: Enforce CSRF, run the supplied action, set flash feedback and redirect (Post/Redirect/Get pattern). */
    protected function post(callable $handler,string $fallback): never {
        if($this->req()->method()!=='POST')$this->res()->error(405,'Method not allowed');
        try {
            CSRF::enforce();
            $destination=$handler();
            Session::flash('success','Changes saved successfully.');
            $this->res()->redirect(is_string($destination)?$destination:$fallback);
        }catch(\DomainException|\InvalidArgumentException $e){
            Session::flash('error',$e->getMessage());$this->res()->redirect($fallback);
        }catch(\Throwable $e){
            error_log((string)$e);Session::flash('error','The request could not be completed. Check server logs.');
            $this->res()->redirect($fallback);
        }
    }
    /** VIVA: Require a positive numeric URL identifier before loading a record. */
    protected function needId(): int {
        $id=$this->req()->id();if(!$id)throw new \DomainException('Invalid record ID.');return $id;
    }
}
