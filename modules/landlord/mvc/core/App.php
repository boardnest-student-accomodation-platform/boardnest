<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/App.php
 * Role: Composition root wiring config, database, authentication, router and HTTP request services.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Composition root for the Landlord application. Starts the session, wires shared helpers, and dispatches each HTTP request through the router. A controller sends the response; exceptions become generic error pages.
 */
namespace BoardNest\Core;
final class App {
    private Database $database;
    private Response $response;
    private Request $request;
    private Auth $auth;
    private View $view;
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private array $config) {
        Session::start('/');
        $this->database=new Database($config['db']);
        $this->response=new Response();
        $this->request=new Request($config['base_path']);
        $this->auth=new Auth($this->database,$this->response,$config);
        $this->view=new View(dirname(__DIR__));
    }
    /** STUDY - db(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function db(): Database{return $this->database;}
    /** STUDY - response(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function response(): Response{return $this->response;}
    /** STUDY - request(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function request(): Request{return $this->request;}
    /** STUDY - auth(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function auth(): Auth{return $this->auth;}
    /** STUDY - view(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function view(): View{return $this->view;}
    /** STUDY - config(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function config(): array{return $this->config;}
    /** VIVA: Run the matched controller and translate unexpected failures into a generic HTTP 500; log details only on the server. */
    public function run(): never {
        try {(new Router(require dirname(__DIR__).'/config/routes.php'))->dispatch($this);}
        catch(\DomainException|\InvalidArgumentException $e){$this->response->error(404,'Page not found or access denied');}
        catch(\Throwable $e){error_log((string)$e);$this->response->error(500,'Internal server error');}
    }
}
