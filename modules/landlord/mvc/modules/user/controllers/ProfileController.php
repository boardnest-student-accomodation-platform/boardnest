<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/user/controllers/ProfileController.php
 * Role: Profile edits, password verification and image replace/remove endpoints.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Renders and updates the logged-in landlord profile. Form values are validated here before being saved by the Landlord model.
 */
namespace BoardNest\Modules\User\Controllers;
use BoardNest\Core\{Controller,Validator,Upload};
use BoardNest\Modules\User\Models\Landlord;
final class ProfileController extends Controller {
    /** STUDY - index(): Display action: prepare only authorized data for a view. */
    public function index(): never {$this->page('user/profile',['title'=>'My profile'],'profile');}
    /** VIVA: Require an approved session, validate editable fields and persist profile changes. */
    public function update(): never {$this->post(function(){
        $u=$this->auth()->require();$model=new Landlord($this->db());
        $model->updateProfile((int)$u['user_id'],
            Validator::text($this->req()->field('name'),'Full name',3,100),
            Validator::text($this->req()->field('phone'),'Phone',9,15),
            Validator::text($this->req()->field('address'),'Address',0,1000));
        return '/profile';
    },'/profile');}
    /** Passwords are updated in users.password_hash; no new password table is needed. */
    public function password(): never {$this->post(function() {
        $u = $this->auth()->require();
        // Verify old password and confirmation. Bcrypt (PHP 8.2 PASSWORD_DEFAULT)
        // truncates after 72 bytes, so enforce the safe input bound explicitly.
        // Passwords must not be trimmed: leading/trailing spaces can be intentional.
        $current = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
        $new = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
        if ($current === '') throw new \DomainException('Enter your current password.');
        if (strlen($new) < 12 || strlen($new) > 72 || str_contains($new, "\0")) {
            throw new \DomainException('New password must contain 12 to 72 bytes.');
        }
        if ($new !== $confirm) throw new \DomainException('New passwords do not match.');
        // A small session-local abuse guard, not a substitute for server-side rate limiting.
        if ((int)($_SESSION['password_lock_until'] ?? 0) > time()) {
            throw new \DomainException('Too many password attempts. Please try later.');
        }
        try {
            (new Landlord($this->db()))->changePassword((int)$u['user_id'], $current, $new);
        } catch (\DomainException $e) {
            $_SESSION['password_failures'] = (int)($_SESSION['password_failures'] ?? 0) + 1;
            if ($_SESSION['password_failures'] >= 5) {
                $_SESSION['password_lock_until'] = time() + 900;
                $_SESSION['password_failures'] = 0;
            }
            throw $e;
        }
        unset($_SESSION['password_lock_until'], $_SESSION['password_failures'], $_SESSION['csrf']);
        session_regenerate_id(true);
        return '/profile';
    },'/profile');}

    /** Check and store an image, keeping other upload folders untouched. */
    public function photoUpload(): never {$this->post(function() {
        $u = $this->auth()->require();
        $path = Upload::save($this->req()->file('photo') ?? [], 'avatars', false, (int)$u['landlord_id']);
        try {
            $old = (new Landlord($this->db()))->saveProfilePhoto((int)$u['landlord_id'], $path);
        } catch (\Throwable $e) {
            Upload::delete($path);
            throw $e;
        }
        if ($old !== null && $old !== $path) Upload::delete($old);
        return '/profile';
    },'/profile');}

    /** Delete only the current landlord's profile photo; other media are untouched. */
    public function photoRemove(): never {$this->post(function() {
        $u = $this->auth()->require();
        $old = (new Landlord($this->db()))->removeProfilePhoto((int)$u['landlord_id']);
        if ($old !== null) Upload::delete($old);
        return '/profile';
    },'/profile');}
}
