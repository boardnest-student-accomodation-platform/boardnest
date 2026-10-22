<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/subscription/controllers/SubscriptionController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Displays landlord subscription information and payment history. Online upgrade/payment is not implemented by this controller.
 */
namespace BoardNest\Modules\Subscription\Controllers;
use BoardNest\Core\Controller;
use BoardNest\Modules\User\Models\Landlord;
final class SubscriptionController extends Controller {
    /** VIVA: Show current tier, expiry information and payment history. */
    public function index(): never {
        $u=$this->auth()->require();$payments=(new Landlord($this->db()))->profilePayments((int)$u['landlord_id']);
        $this->page('subscription/index',['title'=>'Subscription','payments'=>$payments],'subscription');
    }
    /** VIVA: Render a not-yet-available upgrade page; no payment is performed here. */
    public function upgrade(): never {$this->page('subscription/upgrade',['title'=>'Upgrade plan'],'subscription');}
}
