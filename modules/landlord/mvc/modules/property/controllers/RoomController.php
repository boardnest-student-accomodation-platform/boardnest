<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/controllers/RoomController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Handles room listing/details/forms and image uploads. Validates authorization through controller helpers and delegates rules to RoomService.
 */
namespace BoardNest\Modules\Property\Controllers;
use BoardNest\Core\{Controller,Upload};
use BoardNest\Modules\Property\{Models\Property,Models\Room,Services\RoomService};
final class RoomController extends Controller {
    /** STUDY - service(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function service(): RoomService{return new RoomService($this->app);}
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Room{return new Room($this->db());}
    /** STUDY - index(): Display action: prepare only authorized data for a view. */
    public function index(): never {
        $u=$this->auth()->require();$this->page('property/rooms',['title'=>'Rooms','rooms'=>$this->model()->all((int)$u['landlord_id'])],'rooms');
    }
    /** STUDY - newForm(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function newForm(): never {
        $u=$this->auth()->require();$this->page('property/room_form',['title'=>'Add room','room'=>null,'properties'=>(new Property($this->db()))->all((int)$u['landlord_id']),'windows'=>['details_open'=>1,'rent_open'=>1]],'rooms');
    }
    /** STUDY - show(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function show(): never {
        $u=$this->auth()->require();$r=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $slots = $this->model()->slots((int)$r['room_id']);
        $counts = ['total_slots'=>count($slots),'available_slots'=>0,'reserved_slots'=>0,'occupied_slots'=>0,'blocked_slots'=>0];
        foreach ($slots as $slot) {
            $key = (string)$slot['status_display'] . '_slots';
            if (array_key_exists($key, $counts)) $counts[$key]++;
        }
        $this->page('property/room_show',['title'=>$r['room_label']?:'Room details','room'=>$r,'slots'=>$slots,'availability'=>room_availability_state($counts),'availability_counts'=>$counts,'windows'=>$this->model()->windows((int)$r['room_id'])],'rooms');
    }
    /** STUDY - edit(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function edit(): never {
        $u=$this->auth()->require();$r=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/room_form',['title'=>'Edit room','room'=>$r,'properties'=>[], 'windows'=>$this->model()->windows((int)$r['room_id'])],'rooms');
    }
    /** VIVA: Create a room under a property owned by an approved landlord. */
    public function save(): never {$this->post(function(){
        $u=$this->auth()->require();$id=$this->service()->create($this->req()->id('property_id'),$u);return '/room/show?id='.$id;
    },'/room/new');}
    /** VIVA: Apply time-restricted room edits through the service. */
    public function update(): never {$id=$this->req()->id();$this->post(function()use($id){
        $this->service()->update($id,$this->auth()->require());return '/room/show?id='.$id;
    },'/room/edit?id='.$id);}
    /** STUDY - photos(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photos(): never {
        $u=$this->auth()->require();$r=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/room_photos',['title'=>'Room photos','room'=>$r,'photos'=>$this->model()->photos((int)$r['room_id'])],'rooms');
    }
    /** VIVA: Store a room gallery photo. */
    public function photoUpload(): never {$id=$this->req()->id();$this->post(function()use($id){
        $this->service()->uploadPhoto($id,$this->auth()->require());return '/room/photos?id='.$id;
    },'/room/photos?id='.$id);}
    /** VIVA: Delete a room image only after ownership checks. */
    public function photoDelete(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->model()->owned($id,(int)$u['landlord_id']);
        $photo=$this->model()->photo($this->req()->id('photo_id'),$id);
        if(!$photo)throw new \DomainException('Photo not found.');
        $this->model()->deletePhoto((int)$photo['photo_id'],$id);Upload::delete($photo['file_path']);return '/room/photos?id='.$id;
    },'/room/photos?id='.$id);}
    // Old pre-approval onboarding route must not create rooms.
    public function onboardingSave(): never {
        $this->auth()->require();
        $this->res()->redirect('/room/new');
    }
}
