<?php

declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Auth.php
 * Role: Current-user identity, account approval and access gates; every write needs backend ownership checks.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Landlord authentication and authorization. A logged-in session is not the same as an approved landlord account; protected operations check both the user status and identity approval.
 */

namespace BoardNest\Core;

/**
 * Landlord account authentication and authorization.
 *
 * users.user_id is the login identity; landlords.landlord_id is the owner ID.
 * A valid session alone does not prove administrator approval.
 */
final class Auth
{
    private ?array $landlord = null;

    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(
        private Database $db,
        private Response $response,
        private array $cfg
    ) {
    }

    /** Retrieve the authenticated landlord record, or null if unavailable. */
    /** VIVA: Look up the Landlord profile for the session user; suspended/banned users cannot proceed. */
    public function user(): ?array
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if ($userId < 1) {
            return null;
        }

        if ($this->landlord !== null) {
            return $this->landlord;
        }

        $landlord = $this->db->one(
            "SELECT u.user_id, u.full_name, u.email, l.address,
                    u.role, u.status AS user_status,
                    l.landlord_id, l.nic_number, l.mobile,
                    COALESCE(d.identity_status,'not_submitted') AS identity_status, COALESCE(d.registration_stage,'account') AS registration_stage,
                    d.registration_deadline, d.nic_document_path,
                    l.subsc_tier, l.subsc_expires, d.approved_at,
                    lpp.file_path AS profile_photo
             FROM users u
             INNER JOIN landlords l ON l.user_id = u.user_id
             LEFT JOIN landlord_registration_details d ON d.landlord_id=l.landlord_id
             LEFT JOIN landlord_profile_photos lpp ON lpp.landlord_id = l.landlord_id
             WHERE u.user_id = ? AND u.role = 'landlord'
             LIMIT 1",
            [$userId]
        );

        if (!$landlord || in_array($landlord['user_status'], ['banned', 'suspended'], true)) {
            return null;
        }

        $this->landlord = $landlord;
        return $landlord;
    }

    /** Administrator-approved accounts may manage property/rooms. */
    /** VIVA: Approval requires both an active users record and approved landlord identity status. */
    public function approved(?array $landlord = null): bool
    {
        $landlord ??= $this->user();

        return $landlord !== null
            && $landlord['user_status'] === 'active'
            && $landlord['identity_status'] === 'approved';
    }

    /**
     * Verify current approval *within* a transaction, holding a row lock.
     * Called before inserting a property or room to prevent stale-session bypass.
     */
    /** VIVA: Recheck approval immediately before writes rather than trusting an old browser session. */
    public function assertApprovedForWrite(int $landlordId): void
    {
        $row = $this->db->one(
            "SELECT u.status, u.role, d.identity_status
             FROM landlords l
             JOIN users u ON u.user_id = l.user_id
             LEFT JOIN landlord_registration_details d ON d.landlord_id=l.landlord_id
             WHERE l.landlord_id = ?
             FOR UPDATE",
            [$landlordId]
        );

        if (!$row
            || $row['role'] !== 'landlord'
            || $row['status'] !== 'active'
            || $row['identity_status'] !== 'approved') {
            throw new \DomainException(
                'Your account must be approved before managing properties or rooms.'
            );
        }
    }

    /** Reject unauthenticated, banned and (optionally) unverified accounts. */
    /** VIVA: Protect controller actions; redirect guests or pending landlords to the correct step. */
    public function require(bool $approved = true): array
    {
        $landlord = $this->user();
        if (!$landlord) {
            $this->response->redirect('/login');
        }

        if ($approved && !$this->approved($landlord)) {
            $this->response->redirect('/registration/status');
        }

        return $landlord;
    }

    /** VIVA: Regenerate the PHP session ID on login to reduce session-fixation risk. */
    public function signIn(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'landlord';
        unset($_SESSION['csrf']);
        $this->landlord = null;
    }

    /** VIVA: Clear the authenticated session and invalidate the cookie. */
    public function signOut(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        session_destroy();
    }
}
