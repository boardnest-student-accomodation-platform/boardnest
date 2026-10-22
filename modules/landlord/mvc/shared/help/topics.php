<?php
/**
 * Context-sensitive help contents, NOT database records.
 * Only the topic for the active Landlord page is rendered to the modal.
 */
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: shared/help/topics.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

$topics = [
    'dashboard'=>['Overview', 'Dashboard guide',
        ['Review the summary cards for boarding houses, rooms, pending booking requests and current occupants.',
         'Use the sidebar to navigate to a management section.',
         'Review booking requests before they expire or block availability.',
         'The dashboard reflects only properties owned by your landlord account.'],
        'Counts come from your real records. To create your first property, open Properties.'],
    'properties'=>['Properties', 'Boarding-house management',
        ['Select New property to register your boarding house.',
         'Choose the city by name, then enter the accurate address and property details.',
         'Only an approved landlord can create properties. Standard accounts may own one active property.',
         'Open a property to manage rooms, facilities, photos, checklist and verification.',
         'Changes to core property details are limited to the initial 15-minute window.'],
        'Property verification is separate from landlord identity approval.'],
    'property_show'=>['Property details', 'Manage one boarding house',
        ['Review the property address, location, rooms and verification status.',
         'Use Photos to add images, Facilities to describe nearby services and Checklist to prepare move-in tasks.',
         'Request verification when the option is available; Field Agent/Admin review is separate.',
         'Archiving is restricted while active bookings exist.'],
        'Room availability comes from physical slots; it is not a manually selected property status.'],
    'property_form'=>['Property form', 'Add or edit a boarding house',
        ['Choose the correct city from the dropdown; its numeric ID is saved automatically.',
         'Enter a descriptive title, address and any optional location/details.',
         'Save before the 15-minute core-details editing window closes.',
         'After approval, the property can proceed to its own listing verification.'],
        'The form validates required fields and location IDs on the server.'],
    'rooms'=>['Rooms', 'Room and bed-slot management',
        ['Each room belongs to a boarding house you own.',
         'Single rooms have one slot; shared rooms may have several slots.',
         'Available, Reserved, Occupied and Blocked are separate physical-slot states.',
         'A Fully reserved, Fully occupied or Fully booked room has no free slots.',
         'Standard landlords may add up to four rooms per property.'],
        'The booking status badges are calculated from room slots, not typed by hand.'],
    'room_show'=>['Room details', 'Check room occupancy and restrictions',
        ['Review each physical slot and its current reservation/occupancy state.',
         'If every slot is reserved, the room shows Fully reserved; if occupied, Fully occupied.',
         'Blocked slots are unavailable for student requests.',
         'Edit ordinary room details during the first 15 minutes; rent follows its separate annual lock.'],
        'Rejecting, cancelling or completing a booking may automatically release the slot.'],
    'room_form'=>['Room form', 'Add or edit room information',
        ['Choose a boarding house you own and enter a room name.',
         'Choose Single (exactly one slot) or Shared (multiple slots), then set capacity.',
         'Enter monthly rent, deposit, furnishing, preference and rules.',
         'Submit accurate details promptly; later edits and rent changes may be locked.'],
        'Slots are created automatically by the database when a room is saved.'],
    'bookings'=>['Bookings', 'Review student requests',
        ['New Tier 2 student requests reserve one available slot immediately.',
         'Open a booking to Accept or Reject a pending request.',
         'An accepted booking can be marked Active when the student moves in.',
         'Mark an Active booking Completed after move-out to free the slot.',
         'Rejecting or cancelling releases reserved slots automatically.'],
        'Only bookings for your own rooms can be managed here.'],
    'occupants'=>['Current students', 'See currently occupied slots',
        ['This page shows students with Active bookings in your boarding houses.',
         'Use Bookings to inspect an individual request and its details.',
         'When a student moves out, mark the Active booking Completed.',
         'Completed bookings leave history records but no longer appear as current occupants.'],
        'Current students are calculated from active occupancy records.'],
    'messages'=>['Messages', 'Communicate with students',
        ['Open a conversation from the list to read the complete message history.',
         'Conversations are connected to actual bookings and the student involved.',
         'Write the message in the chat field and select Send.',
         'Refresh or wait for new messages, and never share passwords or private documents in chat.'],
        'If a chat is missing, check whether the student has a relevant booking.'],
    'chat'=>['Conversation', 'Chat with the student',
        ['Read previous messages to understand the student request.',
         'Type your reply into the message field and select Send.',
         'The conversation is restricted to the parties associated with its booking.',
         'For booking decisions, return to the Bookings section; chat messages do not change a booking status.'],
        'Messages and booking decisions are different workflows.'],
    'complaints'=>['Complaints', 'Submit and follow complaints',
        ['Select New complaint to report an issue.',
         'Fill in the subject, category, details and supported evidence if available.',
         'Open a complaint to track review progress and updates.',
         'A landlord can see their own submitted complaints only.'],
        'Write factual descriptions, and do not submit sensitive information unnecessarily.'],
    'verification'=>['Verification', 'Landlord and property verification',
        ['First ensure your identity has been approved by Admin.',
         'For boarding-house verification, open the property and request review.',
         'Respond to requested corrections and keep photos/facilities accurate.',
         'A listing is not public until its required checks are complete.'],
        'Approval of the landlord account does not automatically publish a boarding house.'],
    'announcements'=>['Announcements', 'Read official platform notices',
        ['Review Admin announcements relevant to landlords.',
         'Open each announcement to read its contents.',
         'Mark notices as read after reviewing them.',
         'Important verification or booking updates should be checked in their own sections.'],
        'Announcements are not personal chat messages.'],
    'subscription'=>['Subscription', 'Understand Standard and Pro plans',
        ['Review your current subscription tier and expiry date.',
         'Standard accounts can create one property with up to four rooms.',
         'An active Pro plan may allow additional properties and rooms.',
         'If Pro expires, Standard limits apply to new additions.'],
        'Subscription screens do not by themselves confirm a payment was processed.'],
    'profile'=>['My profile', 'Edit your profile, photo and password',
        ['Review your name and contact details.',
         'Use the profile form to update permitted fields.',
         'Upload, replace, or remove your profile picture using the photo form.',
         'Change your password using your current password and a new 12–72 byte password.',
         'Your identity approval status is a separate admin-managed state.',
         'Sign out on shared computers to protect your account.'],
        'Profile editing never bypasses property verification or room-edit locks.'],
];
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$key = $active;
if ($active === 'properties') {
    if (preg_match('~/(property/new|property/edit|registration/property)$~', $path)) $key='property_form';
    elseif (preg_match('~/(property/show|property/photos|property/facilities|property/checklist)$~', $path)) $key='property_show';
}
if ($active === 'rooms') {
    if (preg_match('~/(room/new|room/edit)$~', $path)) $key='room_form';
    elseif (preg_match('~/(room/show|room/photos)$~', $path)) $key='room_show';
}
if ($active === 'messages' && preg_match('~/chat$~', $path)) $key='chat';
$help = $topics[$key] ?? $topics['dashboard'];
