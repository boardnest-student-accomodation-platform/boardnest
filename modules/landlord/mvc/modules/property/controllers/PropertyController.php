<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/controllers/PropertyController.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Handles property pages/forms, verification requests, facilities, checklists and photo uploads. Business rules belong in PropertyService.
 */
namespace BoardNest\Modules\Property\Controllers;
use BoardNest\Core\{Controller,Upload,Session,CSRF};
use BoardNest\Modules\Property\{Models\Property,Services\PropertyService};
final class PropertyController extends Controller {
    /** STUDY - service(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function service(): PropertyService{return new PropertyService($this->app);}
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Property{return new Property($this->db());}
    /** STUDY - index(): Display action: prepare only authorized data for a view. */
    public function index(): never {
        $u=$this->auth()->require();$this->page('property/index',['title'=>'Properties','properties'=>$this->model()->all((int)$u['landlord_id'])],'properties');
    }
    /** STUDY - newForm(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function newForm(): never {$this->auth()->require();$this->page('property/form',['title'=>'Add property','property'=>null,'cities'=>$this->model()->cities()],'properties');}
    /** STUDY - show(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function show(): never {
        $u=$this->auth()->require();$p=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $rooms=(new \BoardNest\Modules\Property\Models\Room($this->db()))->forProperty((int)$p['property_id'],(int)$u['landlord_id']);
        $this->page('property/show',['title'=>$p['title'],'property'=>$p,'rooms'=>$rooms],'properties');
    }
    /** STUDY - edit(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function edit(): never {
        $u=$this->auth()->require();$p=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/form',['title'=>'Edit property','property'=>$p,'open'=>$this->model()->isWindowOpen((int)$p['property_id'],(int)$u['landlord_id']),'cities'=>$this->model()->cities()],'properties');
    }
    /** VIVA: Validate and create a new property through the service layer. */
    public function save(): never {$this->post(function(){
        $u=$this->auth()->require();$id=$this->service()->create($u);return '/property/show?id='.$id;
    },'/property/new');}
    /** VIVA: Update an existing owner-scoped property within the edit window. */
    public function update(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->service()->update($id,$u);return '/property/show?id='.$id;
    },'/property/edit?id='.$id);}
    /** VIVA: Archive property only when bookings allow it. */
    public function archive(): never {$id=$this->req()->id();$this->post(function()use($id){
        $this->service()->archive($id,$this->auth()->require());return '/properties';
    },'/properties');}
    /** VIVA: Request property verification rather than directly approving it. */
    public function verify(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->model()->owned($id,(int)$u['landlord_id']);
        $this->model()->requestVerification($id,(int)$u['landlord_id']);return '/property/show?id='.$id;
    },'/property/show?id='.$id);}
    /** STUDY - facilities(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function facilities(): never {
        $u=$this->auth()->require();$p=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/facilities',['title'=>'Nearby facilities','property'=>$p,'facilities'=>$this->model()->facilities((int)$p['property_id'])],'properties');
    }
    /** VIVA: Add a facility claim to a property owned by the landlord. */
    public function facilityAdd(): never {$id=$this->req()->id();$this->post(function()use($id){
        $this->service()->addFacility($id,$this->auth()->require());return '/property/facilities?id='.$id;
    },'/property/facilities?id='.$id);}
    /** VIVA: Remove a facility record within landlord-owned property scope. */
    public function facilityRemove(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->model()->owned($id,(int)$u['landlord_id']);
        $this->model()->removeFacility($id,$this->req()->id('facility_id'));return '/property/facilities?id='.$id;
    },'/property/facilities?id='.$id);}
    /** STUDY - checklist(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function checklist(): never {
        $u=$this->auth()->require();$p=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/checklist',['title'=>'Move-in checklist','property'=>$p,'record'=>$this->model()->checklist((int)$p['property_id'],(int)$u['landlord_id'])],'properties');
    }
    /** VIVA: Save a property handover checklist. */
    public function checklistSave(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->model()->owned($id,(int)$u['landlord_id']);
        $items=\BoardNest\Core\Validator::text($this->req()->field('items'),'Checklist',1,4000);
        $notes=\BoardNest\Core\Validator::text($this->req()->field('notes'),'Notes',0,2000);
        $this->model()->saveChecklist($id,(int)$u['landlord_id'],$items,$notes);return '/property/checklist?id='.$id;
    },'/property/checklist?id='.$id);}
    /** STUDY - photos(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function photos(): never {
        $u=$this->auth()->require();$p=$this->model()->owned($this->needId(),(int)$u['landlord_id']);
        $this->page('property/photos',['title'=>'Property photos','property'=>$p,'photos'=>$this->model()->photos((int)$p['property_id'])],'properties');
    }
    /** VIVA: Upload a property gallery photo. */
    public function photoUpload(): never {$id=$this->req()->id();$this->post(function()use($id){
        $this->service()->uploadPhoto($id,$this->auth()->require());return '/property/photos?id='.$id;
    },'/property/photos?id='.$id);}
    /** VIVA: Remove a selected property photo and its stored file. */
    public function photoDelete(): never {$id=$this->req()->id();$this->post(function()use($id){
        $u=$this->auth()->require();$this->model()->owned($id,(int)$u['landlord_id']);
        $photo=$this->model()->photo($this->req()->id('photo_id'),$id);
        if(!$photo)throw new \DomainException('Photo not found.');
        $this->model()->deletePhoto((int)$photo['photo_id'],$id);Upload::delete($photo['file_path']);return '/property/photos?id='.$id;
    },'/property/photos?id='.$id);}
    // Previous onboarding POST is retained only for URL compatibility.
    // It never creates a property before account approval.
    public function onboardingSave(): never {
        $this->auth()->require();
        $this->res()->redirect('/property/new');
    }
}
