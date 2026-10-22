<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/dashboard/controllers/DashboardController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Builds the main Landlord overview using Booking statistics, recent bookings and latest announcements.
 */
namespace BoardNest\Modules\Dashboard\Controllers;
use BoardNest\Core\Controller;
use BoardNest\Modules\{Booking\Models\Booking,Notification\Models\Announcement};
final class DashboardController extends Controller {
    /** VIVA: Gather statistics, recent bookings and notices for the approved landlord. */
    public function index(): never {
        $u=$this->auth()->require();$id=(int)$u['landlord_id'];
        $bookings=new Booking($this->db());
        $this->page('dashboard/index',[ 'title'=>'Overview','stats'=>$bookings->stats($id),
            'bookings'=>array_slice($bookings->all($id),0,6),
            'announcements'=>(new Announcement($this->db()))->all((int)$u['user_id'],3)]);
    }
    /** VIVA: Development-only database health endpoint, unavailable while debug is false. */
    public function health(): never {
        if(!$this->app->config()['debug'])$this->res()->error(404,'Not found');
        $this->db()->value('SELECT 1');
        $this->res()->json(['status'=>'ok','database'=>'connected']);
    }
}
