<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/notification/controllers/NotificationController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Displays Admin announcements intended for landlords and handles the mark-as-read POST action.
 */
namespace BoardNest\Modules\Notification\Controllers;
use BoardNest\Core\Controller;
use BoardNest\Modules\Notification\Models\Announcement;
final class NotificationController extends Controller {
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Announcement{return new Announcement($this->db());}
    /** VIVA: Render notices with their read/unread state. */
    public function index(): never {$u=$this->auth()->require();$this->page('notification/index',['title'=>'Announcements','announcements'=>$this->model()->all((int)$u['user_id'])],'announcements');}
    /** VIVA: Save the logged-in user’s read marker for one announcement. */
    public function mark(): never {$this->post(function(){
        $u=$this->auth()->require();$this->model()->markRead($this->req()->id('id'),(int)$u['user_id']);return '/announcements';
    },'/announcements');}
}
