<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/messaging/controllers/MessagingController.php
 * Role: Conversation routing and message permissions for landlords.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Endpoints for booking-linked chat threads, sending messages and returning polled messages as JSON.
 */
namespace BoardNest\Modules\Messaging\Controllers;
use BoardNest\Core\{Controller,CSRF,Validator};
use BoardNest\Modules\Messaging\Models\Conversation;
final class MessagingController extends Controller {
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Conversation{return new Conversation($this->db());}
    /** VIVA: Show the landlord’s booking-related chat inbox. */
    public function index(): never {$u=$this->auth()->require();$this->page('messaging/index',['title'=>'Messages','threads'=>$this->model()->all((int)$u['landlord_id'])],'messages');}
    /** VIVA: Display a single authorized conversation. */
    public function chat(): never {
        $u=$this->auth()->require();$row=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $messages=$this->model()->messages((int)$row['conversation_id']);
        $this->model()->markRead((int)$row['conversation_id'],(int)$u['user_id']);
        $this->page('messaging/chat',['title'=>'Chat with '.$row['student_name'],'thread'=>$row,'messages'=>$messages],'messages');
    }
    /** VIVA: Create or reuse a booking-linked conversation only for an owned booking. */
    public function start(): never {$this->post(function(){
        $u=$this->auth()->require();$id=$this->model()->start($this->req()->id('booking_id'),(int)$u['landlord_id']);return '/chat?id='.$id;
    },'/bookings');}
    /** VIVA: Validate and save the text of a message. */
    public function send(): never {
        // Chat POST requests are AJAX; never put sender IDs in POST data.
        try {
            CSRF::enforce();$u=$this->auth()->require();$id=$this->req()->id();
            $this->model()->owned($id,(int)$u['landlord_id']);
            $body=Validator::text($this->req()->field('message'),'Message',1,2000);
            $this->model()->send($id,(int)$u['user_id'],$body);
            $this->res()->json(['ok'=>true]);
        }catch(\DomainException $e){$this->res()->json(['ok'=>false,'error'=>$e->getMessage()],422);}
    }
    /** VIVA: Provide authorized message data for JavaScript periodic refresh. */
    public function poll(): never {
        $u=$this->auth()->require();$id=$this->needId();$this->model()->owned($id,(int)$u['landlord_id']);
        $this->model()->markRead($id,(int)$u['user_id']);
        $this->res()->json(['messages'=>$this->model()->messages($id),'my_user_id'=>(int)$u['user_id']]);
    }
}
