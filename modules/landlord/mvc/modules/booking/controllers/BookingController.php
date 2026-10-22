<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/booking/controllers/BookingController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Landlord HTTP actions for viewing bookings, taking status decisions and listing currently occupied student slots.
 */
namespace BoardNest\Modules\Booking\Controllers;
use BoardNest\Core\Controller;
use BoardNest\Modules\Booking\{Models\Booking,Services\BookingService};
final class BookingController extends Controller {
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Booking{return new Booking($this->db());}
    /** VIVA: Show bookings for landlord-owned rooms. */
    public function index(): never {$u=$this->auth()->require();$this->page('booking/index',['title'=>'Bookings','bookings'=>$this->model()->all((int)$u['landlord_id'])],'bookings');}
    /** VIVA: Load a single owned booking, including its current status. */
    public function show(): never {
        $u=$this->auth()->require();$b=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('booking/show',['title'=>'Booking #'.$b['booking_id'],'booking'=>$b],'bookings');
    }
    /** VIVA: Submit accept/reject/check-in/check-out decision to BookingService. */
    public function decide(): never {$id=$this->req()->id();$this->post(function()use($id){
        (new BookingService($this->app))->decide($id,$this->req()->field('action'),$this->auth()->require());return '/booking/show?id='.$id;
    },'/booking/show?id='.$id);}
    /** VIVA: Display students in occupied slots, not merely accepted requests. */
    public function occupants(): never {$u=$this->auth()->require();$this->page('booking/occupants',['title'=>'Current occupants','occupants'=>$this->model()->occupants((int)$u['landlord_id'])],'occupants');}
}
