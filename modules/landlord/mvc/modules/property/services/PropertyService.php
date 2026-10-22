<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/services/PropertyService.php
 * Role: Property write validation: approval, city lookup, quota, edit clock and archive.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Property business rules: input validation, approval gate, Standard-versus-Pro property limit, edit window, archive rules and related uploads.
 */
namespace BoardNest\Modules\Property\Services;
use BoardNest\Core\{App,Validator,Upload,MapLink};
use BoardNest\Modules\Property\Models\Property;
final class PropertyService {
    private Property $model;
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private App $app){$this->model=new Property($app->db());}
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function model(): Property {return $this->model;}
    /** Validate the pasted Google Maps link; no manual GPS fields are posted. */
    public function fields(): array {
        $p = $this->app->request();
        $maps = MapLink::normalize($p->field('maps_url'));
        [$lat, $lon] = MapLink::pinCoordinates($maps);
        // Short Google Maps links have no coordinates available without
        // resolving them externally. They are accepted and stored unchanged.
        // Landlord sees a city NAME dropdown; its submitted ID is validated here.
        $cityId = int_field($p->field('city_id'), 1, 999999);
        if (!$this->model->cityExists($cityId)) {
            throw new \DomainException('Please select a valid city from the list.');
        }
        return ['title'=>Validator::text($p->field('title'),'Property title',3,100),
            'city_id'=>$cityId,
            'address'=>Validator::text($p->field('address'),'Address',5,1000),
            'maps_url'=>$maps,
            'latitude'=>$lat,
            'longitude'=>$lon,
            'description'=>Validator::text($p->field('description'),'Description',0,3000)];
    }
    /** VIVA: In one transaction check live approval and plan tier before creating a property. */
    public function create(array $landlord): int {
        $lid=(int)$landlord['landlord_id'];$pdo=$this->app->db()->pdo();$values=$this->fields();
        $pdo->beginTransaction();
        try {
            // Serialize simultaneous property creation requests by this landlord.
            // Without the FOR UPDATE lock, both requests can see zero properties
            // and exceed the Standard plan's one-property quota.
            $locked = $this->app->db()->one('SELECT landlord_id FROM landlords WHERE landlord_id=? FOR UPDATE', [$lid]);
            if (!$locked) throw new \DomainException('Landlord account not found.');
            $this->app->auth()->assertApprovedForWrite($lid);
            // Pro privileges count only when the subscription has not expired.
            // Expired Pro landlords use Standard limits again.
            $validPro=(bool)$this->app->db()->value("SELECT (subsc_tier='pro' AND subsc_expires IS NOT NULL AND subsc_expires >= CURRENT_DATE) FROM landlords WHERE landlord_id=?",[$lid]);
            if(!$validPro) {
                $count=(int)$this->app->db()->value("SELECT COUNT(*) FROM properties WHERE landlord_id=? AND status<>'suspended'",[$lid]);
                if($count>=1)throw new \DomainException('Standard plan allows only one active property.');
            }
            $id=$this->model->create($lid,$values);
            $pdo->commit();return $id;
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** VIVA: Require landlord ownership and the initial 15-minute core-details edit window. */
    public function update(int $id,array $landlord): void {
        $lid=(int)$landlord['landlord_id'];$values=$this->fields();$db=$this->app->db();$pdo=$db->pdo();
        $pdo->beginTransaction();
        try {
            $db->one('SELECT status FROM properties WHERE property_id=? AND landlord_id=? AND status<>\'suspended\' FOR UPDATE',[$id,$lid])
                ?: throw new \DomainException('Property not found or archived.');
            if(!$this->model->isWindowOpen($id,$lid))throw new \DomainException('Property details are locked after 15 minutes.');
            $this->model->update($id,$lid,$values);
            $pdo->commit();
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    /** VIVA: Do not archive a property with a pending, accepted or active booking. */
    public function archive(int $id,array $landlord): void {
        $lid=(int)$landlord['landlord_id'];$this->model->owned($id,$lid);
        // Archiving is blocked while students still have unresolved/current bookings.
        $busy=(int)$this->app->db()->value("SELECT COUNT(*) FROM bookings b JOIN room_slots rs ON rs.slot_id=b.slot_id JOIN rooms r ON r.room_id=rs.room_id WHERE r.property_id=? AND b.bk_status IN ('pending','confirmed')",[$id]);
        if($busy)throw new \DomainException('A property with current bookings cannot be archived.');
        $this->model->archive($id,$lid);
    }
    /** VIVA: Save a landlord facility claim; independent verification belongs to the Agent workflow. */
    public function addFacility(int $id,array $landlord): void {
        $this->model->owned($id,(int)$landlord['landlord_id']);$p=$this->app->request();
        $data=['type'=>Validator::choice($p->field('type'),['bus_route','supermarket','grocery','pharmacy','hospital','railway_station','restaurant','other'],'Facility type'),
            'name'=>Validator::text($p->field('name'),'Facility name',2,150),
            'details'=>Validator::text($p->field('details'),'Description',0,255),
            'distance'=>$p->field('distance')===''?null:int_field($p->field('distance'),0,100000)];
        $this->model->addFacility($id,$data);
    }
    /** VIVA: Validate and save an image; delete the file if the database insert fails. */
    public function uploadPhoto(int $id,array $landlord): void {
        $this->model->owned($id,(int)$landlord['landlord_id']);
        $file=$this->app->request()->file('photo');if(!$file)throw new \DomainException('Please choose a photo.');
        $path=Upload::save($file,'properties',false,$id);
        try{$caption=Validator::text($this->app->request()->field('caption'),'Caption',0,255);$this->model->addPhoto($id,$path,$caption);}
        catch(\Throwable $e){Upload::delete($path);throw $e;}
    }
}
