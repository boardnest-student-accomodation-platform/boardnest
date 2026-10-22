<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/verification/controllers/VerificationController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Displays property verification status. Actual approval decisions are reserved for other authorized roles.
 */
namespace BoardNest\Modules\Verification\Controllers;
use BoardNest\Core\Controller;
use BoardNest\Modules\Property\Models\Property;
final class VerificationController extends Controller {
    /** VIVA: Read and display verification states without granting self-approval. */
    public function index(): never {
        $u=$this->auth()->require();$this->page('verification/index',['title'=>'Verification status','properties'=>(new Property($this->db()))->all((int)$u['landlord_id'])],'verification');
    }
}
