<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: modules/property/services/RoomService.php
 * Role: Room validation: capacity, deposits, 15-minute details window, rent lock and slot history.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Room business rules: validation, plan limits, 15-minute detail lock, annual rent lock and capacity/booking integrity checks.
 */
namespace BoardNest\Modules\Property\Services;
use BoardNest\Core\{App,Validator,Upload};
use BoardNest\Modules\Property\Models\{Property,Room};
final class RoomService {
    private Room $rooms;private Property $properties;
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private App $app){$this->rooms=new Room($app->db());$this->properties=new Property($app->db());}
    /** STUDY - model(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function model(): Room{return $this->rooms;}
    /** VIVA: Validate room type/capacity, rent, deposit, furnishings, preference and house rules. */
    public function fields(): array {
        $p=$this->app->request();$type=Validator::choice($p->field('type'),['single','shared'],'Room type');
        $capacity=int_field($p->field('capacity'),1,100);
        if($type==='single'&&$capacity!==1)throw new \DomainException('Single rooms must have exactly one slot.');
        return ['label'=>Validator::text($p->field('label'),'Room name',1,100),
            'type'=>$type,'capacity'=>$capacity,
            'price'=>Validator::number($p->field('price'),'Monthly rent',1,10000000),
            'deposit'=>Validator::number($p->field('deposit','0'),'Deposit',0,10000000),
            'partial'=>$p->field('partial')==='1'?1:0,
            'furnishing'=>Validator::choice($p->field('furnishing'),['unfurnished','semi_furnished','furnished'],'Furnishing'),
            'gender'=>Validator::choice($p->field('gender'),['any','male','female'],'Gender preference'),
            'rules'=>Validator::text($p->field('rules'),'House rules',0,3000)];
    }
    /** VIVA: Lock the parent property and enforce Standard plan maximum four rooms per property. */
    public function create(int $propertyId,array $landlord): int {
        $lid=(int)$landlord['landlord_id'];$v=$this->fields();$pdo=$this->app->db()->pdo();$pdo->beginTransaction();
        try {
            $this->app->auth()->assertApprovedForWrite($lid);
            // Lock this property row during the room-limit check and insert.
            $property=$this->app->db()->one('SELECT * FROM properties WHERE property_id=? AND landlord_id=? AND status<>\'suspended\' FOR UPDATE',[$propertyId,$lid]);
            if(!$property)throw new \DomainException('Property not found.');
            $validPro=(bool)$this->app->db()->value("SELECT (subsc_tier='pro' AND subsc_expires IS NOT NULL AND subsc_expires >= CURRENT_DATE) FROM landlords WHERE landlord_id=?",[$lid]);
            if(!$validPro&&$this->rooms->countFor($propertyId)>=4)
                throw new \DomainException('Standard plan allows at most four rooms.');
            $id=$this->rooms->create($propertyId,$v);$pdo->commit();return $id;
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }
    /** VIVA: Honor room detail and rent lock windows, and protect historical slot references. */
    public function update(int $id,array $landlord): void {
        $v=$this->fields();$db=$this->app->db();$pdo=$db->pdo();$pdo->beginTransaction();
        try {
        $this->app->auth()->assertApprovedForWrite((int)$landlord['landlord_id']);
        $row=$db->one('SELECT r.*,d.room_label,d.rent_locked_until,r.security_deposit AS deposit_amount,r.gender_pref AS gender_preference,r.partial_occupancy AS partial_occupancy_allowed FROM rooms r JOIN properties p ON p.property_id=r.property_id LEFT JOIN room_landlord_details d ON d.room_id=r.room_id WHERE r.room_id=? AND p.landlord_id=? AND p.status<>\'suspended\' AND NOT EXISTS(SELECT 1 FROM landlord_property_archives la WHERE la.property_id=p.property_id) FOR UPDATE',[$id,(int)$landlord['landlord_id']]);
        if(!$row)throw new \DomainException('Room not found.');
        $window=$this->rooms->windows($id);
        // Editing ordinary room details and editing monthly rent have separate clocks.
        if(!$window['details_open']){
            foreach(['label'=>'room_label','type'=>'room_type','capacity'=>'slot_capacity','deposit'=>'deposit_amount','partial'=>'partial_occupancy_allowed','furnishing'=>'furnishing','gender'=>'gender_preference','rules'=>'house_rules'] as $key=>$column){
                if((string)$v[$key] !== (string)($row[$column]??''))throw new \DomainException('Normal room details are locked after 15 minutes.');
            }
        }
        if((float)$v['price']!==(float)$row['price']&&!$window['rent_open'])
            throw new \DomainException('Rent can only be updated after its annual lock expires.');
        // Do not remove numbered slots referenced by any historical booking.
        if($v['capacity']<(int)$row['slot_capacity']){
            $historical=(int)$this->app->db()->value('SELECT COUNT(*) FROM bookings b JOIN room_slots s ON s.slot_id=b.slot_id WHERE s.room_id=? AND s.slot_no>?',[$id,$v['capacity']]);
            if($historical)throw new \DomainException('Capacity cannot shrink because historical bookings reference affected slots.');
        }
        // Check live reservations/maintenance blocks before deleting any slots.
        if($v['capacity']<(int)$row['slot_capacity']){
            $busy=(int)$db->value("SELECT COUNT(*) FROM room_slots s LEFT JOIN slot_reservations sr ON sr.slot_id=s.slot_id LEFT JOIN room_slot_blocks bl ON bl.slot_id=s.slot_id WHERE s.room_id=? AND s.slot_no>? AND (sr.slot_id IS NOT NULL OR bl.slot_id IS NOT NULL OR s.status<>'available')",[$id,$v['capacity']]);
            if($busy)throw new \DomainException('Cannot remove a reserved, occupied or blocked slot.');
        }
        $oldCapacity=(int)$row['slot_capacity'];
        $this->rooms->update($id,(int)$row['property_id'],$v);
        if($v['capacity']>$oldCapacity){
            for($i=$oldCapacity+1;$i<=$v['capacity'];$i++)$db->run("INSERT INTO room_slots(room_id,slot_no,status) VALUES (?,?,'available')",[$id,$i]);
        }elseif($v['capacity']<$oldCapacity){
            $db->run('DELETE FROM room_slots WHERE room_id=? AND slot_no>?',[$id,$v['capacity']]);
        }
        if((float)$row['price']!==(float)$v['price']) {
            $db->run('UPDATE room_landlord_details SET rent_last_changed_at=NOW(),rent_locked_until=DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE room_id=?',[$id]);
            $db->run('INSERT INTO room_rent_history(room_id,changed_by_user_id,old_price,new_price,reason) VALUES (?,?,?,?,?)',[$id,(int)$landlord['user_id'],$row['price'],$v['price'],'Landlord rent adjustment']);
        }
        $pdo->commit();
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    /** VIVA: Save a room image and remove it if the related database operation fails. */
    public function uploadPhoto(int $id,array $landlord): void {
        $this->rooms->owned($id,(int)$landlord['landlord_id']);$f=$this->app->request()->file('photo');if(!$f)throw new \DomainException('Choose a photo.');
        $path=Upload::save($f,'rooms',false,$id);
        try{$caption=Validator::text($this->app->request()->field('caption'),'Caption',0,255);$this->rooms->addPhoto($id,$path,$caption);}
        catch(\Throwable $e){Upload::delete($path);throw $e;}
    }
}
