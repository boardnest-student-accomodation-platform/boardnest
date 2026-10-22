<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/complaint/controllers/ComplaintController.php
 * Role: Complaint views/actions subject to ownership checks.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Landlord complaint forms, submission and private evidence download. Checks booking ownership before accepting a complaint.
 */
namespace BoardNest\Modules\Complaint\Controllers;
use BoardNest\Core\{Controller,Upload,Validator};
use BoardNest\Modules\Complaint\Models\Complaint;
use BoardNest\Modules\Booking\Models\Booking;
final class ComplaintController extends Controller {
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    private function model(): Complaint{return new Complaint($this->db());}
    /** STUDY - index(): Display action: prepare only authorized data for a view. */
    public function index(): never {$u=$this->auth()->require();$this->page('complaint/index',['title'=>'Complaints','complaints'=>$this->model()->all((int)$u['user_id'])],'complaints');}
    /** VIVA: Provide owned bookings to select the student involved. */
    public function newForm(): never {
        $u=$this->auth()->require();$this->page('complaint/form',['title'=>'Submit complaint','bookings'=>(new Booking($this->db()))->all((int)$u['landlord_id'])],'complaints');
    }
    /** VIVA: Validate complaint details and optional private evidence; require a landlord-owned booking. */
    public function submit(): never {$this->post(function(){
        $u=$this->auth()->require();$b=(new Booking($this->db()))->owned($this->req()->id('booking_id'),(int)$u['landlord_id']);
        $category=Validator::choice($this->req()->field('category'),['property_damage','rent_dispute','conduct','safety','other'],'Complaint category');
        $description=Validator::text($this->req()->field('description'),'Complaint details',20,4000);
        $file=$this->req()->file('evidence');$path=null;
        if($file && ($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)$path=Upload::save($file,'complaints',true,(int)$b['booking_id']);
        try{$this->model()->create((int)$u['user_id'],(int)$b['student_user_id'],(int)$b['booking_id'],$category,$description,$path);}
        catch(\Throwable $e){if($path)Upload::delete($path,true);throw $e;}
        return '/complaints';
    },'/complaint/new');}
    /** VIVA: Show only the landlord’s own complaint record. */
    public function show(): never {
        $u=$this->auth()->require();$c=$this->model()->owned($this->needId(),(int)$u['user_id']);
        $this->page('complaint/show',['title'=>'Complaint #'.$c['complaint_id'],'complaint'=>$c],'complaints');
    }
    /** VIVA: Authorize the complainant and stream evidence from private disk storage. */
    public function evidence(): never {
        $u=$this->auth()->require();$c=$this->model()->owned($this->needId(),(int)$u['user_id']);
        $path=$c['evidence_path']??'';
        if (Upload::privateFilePath($path) === null || !str_starts_with($path, 'storage/private/complaints/')) $this->res()->error(404,'No evidence');
        $file=Upload::privateFilePath($path);
        if($file===null)$this->res()->error(404,'No evidence');
        if(!is_file($file))$this->res()->error(404,'Evidence not found');
        $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
        header('Content-Type: '.(['pdf'=>'application/pdf','jpg'=>'image/jpeg','png'=>'image/png'][$ext]));
        header('X-Content-Type-Options: nosniff');header('Content-Disposition: attachment; filename="evidence.'. $ext .'"');
        header('Cache-Control: private, no-store');readfile($file);exit;
    }
}
