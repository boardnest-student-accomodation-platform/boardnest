#!/usr/bin/env python3
"""Static QA for BoardNest V4. Does NOT pretend to run a MariaDB server."""
from pathlib import Path
from collections import Counter
import re, json, subprocess, shutil, sys
ROOT=Path(__file__).resolve().parents[1]
SCHEMA=(ROOT/'schema.sql').read_text()
BASE=ROOT/'DATABASE_UNIFIED'
MOCK=(BASE/'03_REALISTIC_FICTIONAL_MOCK_DATA.sql').read_text()
checks=[]
def check(label,result,detail=''):
 checks.append({'name':label,'passed':bool(result),'detail':str(detail)})
 print(f"[{'PASS' if result else 'FAIL'}] {label}" + (f' — {detail}' if detail else ''))

tables=re.findall(r'(?im)^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z_]+)`?\s*\(',SCHEMA)
refs=re.findall(r'(?i)\bREFERENCES\s+`?([A-Za-z_]+)`?\s*\(',SCHEMA)
check('47 unique schema tables',len(tables)==47 and len(set(tables))==47,f'{len(tables)} definitions')
check('All foreign-key target tables exist',all(t in tables for t in refs),f'{len(refs)} references')
check('No active schema triggers',not re.search(r'(?im)^\s*CREATE\s+(?:DEFINER\s*=\s*\S+\s+)?TRIGGER\b',SCHEMA))
check('No active schema views',not re.search(r'(?im)^\s*CREATE\s+(?:OR\s+REPLACE\s+)?VIEW\b',SCHEMA))
check('Project root and fresh installer exactly match',SCHEMA==(BASE/'01_FRESH_FULL_SCHEMA_PHP_VALIDATION.sql').read_text())
check('Project root and tables-only installer exactly match',SCHEMA==(BASE/'02_FRESH_TABLES_ONLY_47.sql').read_text())
# Find the exact source-preserved group prefix in the older reference file (same text as user-supplied newschema.sql).
src=(BASE/'00_ORIGINAL_GROUP_NEWSHEMA_REFERENCE.sql').read_bytes()
check('Original 27-table group schema text is unmodified prefix',SCHEMA.encode().startswith(src),f'{len(src)} source bytes')
check('Unique current student and slot reservations',all(s in SCHEMA for s in ['UNIQUE KEY `uq_bn_res_student` (`student_id`)','UNIQUE KEY `uq_bn_res_slot` (`slot_id`)']))
check('No trigger dependency in new PHP core',not any(re.search(r'(?i)trigger-backed|triggers (?:generate|synchronize|reserve)|lets SQL triggers',p.read_text(errors='replace')) for p in (ROOT/'modules/landlord/mvc').rglob('*.php')))
check('Mock SQL contains no DROP/TRUNCATE',not re.search(r'(?im)^\s*(DROP|TRUNCATE)\b',MOCK))
check('Demo SQL imports via database transaction', 'START TRANSACTION;' in MOCK and MOCK.rstrip().endswith('COMMIT;'))
expected=json.loads((BASE/'DEMO_DATA_COUNTS.json').read_text())
def tuples(tab):
 # matches generated multi-row INSERT INTO `tab` ... VALUES\n(...),\n(...);, excludes per-row SELECT clauses.
 match=re.search(r'(?ms)^INSERT INTO '+re.escape(tab)+r'\s*\([^\n]+\) VALUES\n(.*?);\s*$',MOCK)
 if not match:return []
 # each generated row is a single line, with no newline embedded in quoted values
 return re.findall(r'^\(([^\n]+)\)(?:,)?\s*$',match.group(1),re.M)
for tab,exp in [('landlords',12),('students',36),('field_agents',4),('room_slots',63),('bookings',32),('slot_reservations',20),('conversations',14),('messages',42)]:
 actual=len(tuples(tab));check('Mock '+tab+' count',actual==exp,f'{actual}/{exp}')
ids=[];rooms=[]
for row in tuples('room_slots'):
 fields=row.split(',');ids.append(int(fields[0]));rooms.append((int(fields[1]),int(fields[2])))
check('Mock physical slot IDs unique',len(ids)==len(set(ids)))
check('Mock room+slot numbers unique',len(rooms)==len(set(rooms)))
reservations=tuples('slot_reservations')
rstu=[];rslot=[];rbook=[]
for row in reservations:
 fields=row.split(',');rbook.append(int(fields[0]));rstu.append(int(fields[1]));rslot.append(int(fields[2]))
check('Demo reservations 1 per student',len(rstu)==len(set(rstu)))
check('Demo reservations 1 per slot',len(rslot)==len(set(rslot)))
check('Demo reservations match known slot IDs',set(rslot).issubset(set(ids)))
booking_rows=tuples('bookings');booking_ids=[];bk_students=[];bk_slots=[]
for row in booking_rows:
 fields=row.split(',');booking_ids.append(int(fields[0]));bk_students.append(int(fields[1]));bk_slots.append(int(fields[2]))
check('Demo bookings reference seeded slots',set(bk_slots).issubset(set(ids)))
check('Demo reservation-booking identity consistent',all(rbook[i] in booking_ids and bk_slots[booking_ids.index(rbook[i])]==rslot[i] and bk_students[booking_ids.index(rbook[i])]==rstu[i] for i in range(len(rbook))))
# Canonical historical statuses, landlord actions and reservation presence must agree.
bk_statuses={}
for row in booking_rows:
    fields=row.split(',')
    bk_statuses[int(fields[0])]=fields[3].strip("' ")
actions=tuples('booking_landlord_actions')
act={}
for row in actions:
    # This generated fixture does not place commas inside action textual fields.
    fields=row.split(',')
    act[int(fields[0])]=(fields[2].strip("' "), fields[5].strip("' "))
check('Demo canonical booking status distribution',Counter(bk_statuses.values())=={'confirmed':14,'pending':6,'completed':5,'cancelled':7},str(dict(Counter(bk_statuses.values()))))
check('Demo active booking count and reservation count agree',len([b for b,status in bk_statuses.items() if status in ('pending','confirmed')])==len(reservations))
check('Demo every current booking has reservation',all(b in rbook for b,status in bk_statuses.items() if status in ('pending','confirmed')))
check('Demo no closed booking has reservation',all(b not in rbook for b,status in bk_statuses.items() if status in ('completed','cancelled')))
check('Demo occupied bookings are confirmed',all(bk_statuses[b]=='confirmed' for b,(dec,occ) in act.items() if occ=='occupied'))
check('Demo moved-out bookings are completed',all(bk_statuses[b]=='completed' for b,(dec,occ) in act.items() if occ=='moved_out'))
check('Demo rejected bookings are cancelled',all(bk_statuses[b]=='cancelled' for b,(dec,occ) in act.items() if dec=='rejected'))
check('Demo booking action rows complete',len(tuples('booking_landlord_actions'))==len(booking_ids))
check('Demo bookings reserve exactly 20 slots',len(re.findall(r'^UPDATE room_slots SET status=',MOCK,re.M))==20)
check('Demo password is a bcrypt hash',bool(re.search(r'\$2y\$\d{2}\$',MOCK)))
check('Mock JPEG assets exist',all((ROOT/'public/landlord'/rel).is_file() for rel in re.findall(r"'(?P<rel>assets/uploads/landlord/(?:rooms|properties)/[a-f0-9]{40}\.jpg)'",MOCK)))
php_files=sorted(ROOT.rglob('*.php'));fail_lint=[]
for f in php_files:
 result=subprocess.run(['php','-l',str(f)],capture_output=True,text=True)
 if result.returncode:fail_lint.append(f.relative_to(ROOT).as_posix())
check('All PHP files parse',not fail_lint,f'{len(php_files)} files; failures: {fail_lint[:4]}')
node=shutil.which('node');js_files=list((ROOT/'public/landlord/assets/js').rglob('*.js'))
if node:
 issues=[p.name for p in js_files if subprocess.run([node,'--check',str(p)],capture_output=True).returncode]
 check('Landlord JavaScript syntax',not issues,f'{len(js_files)} checked')
else:check('Landlord JavaScript syntax',False,'node unavailable; not tested')
# Detect module-level intended invariants by control-path inspection.
room=(ROOT/'modules/landlord/mvc/modules/property/services/RoomService.php').read_text()
booking=(ROOT/'modules/landlord/mvc/modules/booking/services/BookingService.php').read_text()
student=(ROOT/'public/student/actions/submit_booking.php').read_text()
check('PHP room create inserts physical slots', 'INSERT INTO room_slots' in (ROOT/'modules/landlord/mvc/modules/property/models/Room.php').read_text())
check('PHP room rent history, locks and tx',all(x in room for x in ['beginTransaction','rent_locked_until','room_rent_history','commit()','rollBack()']))
check('PHP booking all four transitions',all(x in booking for x in ["'accepted'","'rejected'","'active'","'completed'","UPDATE bookings SET",'slot_reservations']))
check('Student booking writes all 4 entities transactionally',all(x in student for x in ['beginTransaction','INSERT INTO bookings','INSERT INTO slot_reservations','INSERT INTO booking_landlord_actions','UPDATE room_slots','commit()']))
report={'static_passed':sum(x['passed'] for x in checks),'static_total':len(checks),'failed':[x for x in checks if not x['passed']],'mysql_runtime':'NOT RUN — MariaDB server and PHP pdo_mysql are not available in this packaging environment','checks':checks}
(ROOT/'QA_V4_STATIC_RESULTS.json').write_text(json.dumps(report,indent=2))
print(f"\nSTATIC QA: {report['static_passed']}/{report['static_total']} PASS")
sys.exit(0 if report['static_passed']==report['static_total'] else 1)
