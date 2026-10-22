<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: config/routes.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Single source of truth for Landlord MVC endpoints. Each row is [HTTP method, relative URL, controller class, action method]. GET shows information; POST changes data.
 */
// Central routing map: METHOD, URL path, Controller class, method.
return [
    // ROOT / SESSION: home, health and account/identity onboarding.
    ['GET','/',\BoardNest\Modules\Dashboard\Controllers\DashboardController::class,'index'],
    ['GET','/health',\BoardNest\Modules\Dashboard\Controllers\DashboardController::class,'health'],
    ['GET','/login',\BoardNest\Modules\User\Controllers\UserController::class,'login'],
    ['POST','/login',\BoardNest\Modules\User\Controllers\UserController::class,'loginPost'],
    ['POST','/logout',\BoardNest\Modules\User\Controllers\UserController::class,'logout'],
    ['GET','/register',\BoardNest\Modules\User\Controllers\UserController::class,'register'],
    ['POST','/register',\BoardNest\Modules\User\Controllers\UserController::class,'registerPost'],
    ['GET','/registration/identity',\BoardNest\Modules\User\Controllers\UserController::class,'identity'],
    ['POST','/registration/identity',\BoardNest\Modules\User\Controllers\UserController::class,'identityPost'],
    ['GET','/registration/status',\BoardNest\Modules\User\Controllers\UserController::class,'status'],
    ['GET','/registration/property',\BoardNest\Modules\User\Controllers\UserController::class,'propertySetup'],
    ['POST','/registration/property',\BoardNest\Modules\Property\Controllers\PropertyController::class,'onboardingSave'],
    ['GET','/registration/room',\BoardNest\Modules\User\Controllers\UserController::class,'roomSetup'],
    ['POST','/registration/room',\BoardNest\Modules\Property\Controllers\RoomController::class,'onboardingSave'],
    // DASHBOARD / PROPERTIES: approved landlord pages and property lifecycle.
    ['GET','/dashboard',\BoardNest\Modules\Dashboard\Controllers\DashboardController::class,'index'],
    ['GET','/properties',\BoardNest\Modules\Property\Controllers\PropertyController::class,'index'],
    ['GET','/property/new',\BoardNest\Modules\Property\Controllers\PropertyController::class,'newForm'],
    ['GET','/property/show',\BoardNest\Modules\Property\Controllers\PropertyController::class,'show'],
    ['GET','/property/edit',\BoardNest\Modules\Property\Controllers\PropertyController::class,'edit'],
    ['POST','/property/save',\BoardNest\Modules\Property\Controllers\PropertyController::class,'save'],
    ['POST','/property/update',\BoardNest\Modules\Property\Controllers\PropertyController::class,'update'],
    ['POST','/property/archive',\BoardNest\Modules\Property\Controllers\PropertyController::class,'archive'],
    ['POST','/property/verify',\BoardNest\Modules\Property\Controllers\PropertyController::class,'verify'],
    ['GET','/property/facilities',\BoardNest\Modules\Property\Controllers\PropertyController::class,'facilities'],
    ['POST','/property/facilities/add',\BoardNest\Modules\Property\Controllers\PropertyController::class,'facilityAdd'],
    ['POST','/property/facilities/remove',\BoardNest\Modules\Property\Controllers\PropertyController::class,'facilityRemove'],
    ['GET','/property/checklist',\BoardNest\Modules\Property\Controllers\PropertyController::class,'checklist'],
    ['POST','/property/checklist/save',\BoardNest\Modules\Property\Controllers\PropertyController::class,'checklistSave'],
    ['GET','/property/photos',\BoardNest\Modules\Property\Controllers\PropertyController::class,'photos'],
    ['POST','/property/photos/upload',\BoardNest\Modules\Property\Controllers\PropertyController::class,'photoUpload'],
    ['POST','/property/photos/delete',\BoardNest\Modules\Property\Controllers\PropertyController::class,'photoDelete'],
    // ROOMS: room forms, lifecycle and photographs.
    ['GET','/rooms',\BoardNest\Modules\Property\Controllers\RoomController::class,'index'],
    ['GET','/room/new',\BoardNest\Modules\Property\Controllers\RoomController::class,'newForm'],
    ['GET','/room/show',\BoardNest\Modules\Property\Controllers\RoomController::class,'show'],
    ['GET','/room/edit',\BoardNest\Modules\Property\Controllers\RoomController::class,'edit'],
    ['POST','/room/save',\BoardNest\Modules\Property\Controllers\RoomController::class,'save'],
    ['POST','/room/update',\BoardNest\Modules\Property\Controllers\RoomController::class,'update'],
    ['GET','/room/photos',\BoardNest\Modules\Property\Controllers\RoomController::class,'photos'],
    ['POST','/room/photos/upload',\BoardNest\Modules\Property\Controllers\RoomController::class,'photoUpload'],
    ['POST','/room/photos/delete',\BoardNest\Modules\Property\Controllers\RoomController::class,'photoDelete'],
    // BOOKINGS: pending decisions, move-in/out and occupants.
    ['GET','/bookings',\BoardNest\Modules\Booking\Controllers\BookingController::class,'index'],
    ['GET','/booking/show',\BoardNest\Modules\Booking\Controllers\BookingController::class,'show'],
    ['POST','/booking/decide',\BoardNest\Modules\Booking\Controllers\BookingController::class,'decide'],
    ['GET','/students/current',\BoardNest\Modules\Booking\Controllers\BookingController::class,'occupants'],
    // MESSAGING: conversations are tied to owned bookings.
    ['GET','/messages',\BoardNest\Modules\Messaging\Controllers\MessagingController::class,'index'],
    ['GET','/chat',\BoardNest\Modules\Messaging\Controllers\MessagingController::class,'chat'],
    ['POST','/chat/start',\BoardNest\Modules\Messaging\Controllers\MessagingController::class,'start'],
    ['POST','/chat/send',\BoardNest\Modules\Messaging\Controllers\MessagingController::class,'send'],
    ['GET','/chat/poll',\BoardNest\Modules\Messaging\Controllers\MessagingController::class,'poll'],
    // COMPLAINTS: submission, listing and private evidence retrieval.
    ['GET','/complaints',\BoardNest\Modules\Complaint\Controllers\ComplaintController::class,'index'],
    ['GET','/complaint/new',\BoardNest\Modules\Complaint\Controllers\ComplaintController::class,'newForm'],
    ['POST','/complaint/save',\BoardNest\Modules\Complaint\Controllers\ComplaintController::class,'submit'],
    ['GET','/complaint/show',\BoardNest\Modules\Complaint\Controllers\ComplaintController::class,'show'],
    ['GET','/complaint/evidence',\BoardNest\Modules\Complaint\Controllers\ComplaintController::class,'evidence'],
    // NOTIFICATIONS: Admin announcements and read markers.
    ['GET','/announcements',\BoardNest\Modules\Notification\Controllers\NotificationController::class,'index'],
    ['POST','/announcements/read',\BoardNest\Modules\Notification\Controllers\NotificationController::class,'mark'],
    // SUBSCRIPTION, VERIFICATION AND PROFILE: read-only status + editable profile.
    ['GET','/subscription',\BoardNest\Modules\Subscription\Controllers\SubscriptionController::class,'index'],
    ['GET','/subscription/upgrade',\BoardNest\Modules\Subscription\Controllers\SubscriptionController::class,'upgrade'],
    ['GET','/verification',\BoardNest\Modules\Verification\Controllers\VerificationController::class,'index'],
    ['GET','/profile',\BoardNest\Modules\User\Controllers\ProfileController::class,'index'],
    ['POST','/profile/save',\BoardNest\Modules\User\Controllers\ProfileController::class,'update'],
    ['POST','/profile/password',\BoardNest\Modules\User\Controllers\ProfileController::class,'password'],
    ['POST','/profile/photo',\BoardNest\Modules\User\Controllers\ProfileController::class,'photoUpload'],
    ['POST','/profile/photo/remove',\BoardNest\Modules\User\Controllers\ProfileController::class,'photoRemove'],
];
