-- BoardNest realistic FICTIONAL demo dataset for trigger-free 47-table PHP-validation schema.
-- Import on a FRESH boardnest database ONLY, after 01 schema + city seed.
-- This seed assumes the base installer already creates admin user ID 1.
-- All emails use .test and mobiles are synthetic. DO NOT import into live data.
-- Account login: demo.landlord01@example.test / BoardNestDemo#2026
--                demo.student01@example.test  / BoardNestDemo#2026
--                admin@boardnest.lk          / BoardNestDemo#2026 (DEMO ONLY!)
--                demo.agent01@example.test  / BoardNestDemo#2026
USE `boardnest`;
START TRANSACTION;
-- Explicit seeded IDs and missing-row guards keep FK relationships predictable.

UPDATE users SET password_hash='$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O' WHERE user_id=1 AND role='admin' AND email='admin@boardnest.lk';

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 1,'University of Colombo School of Computing','Western Province',city_id FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 2,'University of Kelaniya','Western Province',city_id FROM cities WHERE name='Kelaniya' ORDER BY city_id LIMIT 1;

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 3,'University of Moratuwa','Western Province',city_id FROM cities WHERE name='Moratuwa' ORDER BY city_id LIMIT 1;

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 4,'University of Peradeniya','Central Province',city_id FROM cities WHERE name='Peradeniya' ORDER BY city_id LIMIT 1;

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 5,'University of Sri Jayewardenepura','Western Province',city_id FROM cities WHERE name='Kotte' ORDER BY city_id LIMIT 1;

INSERT INTO universities(uni_id,uni_name,province,city_id) SELECT 6,'University of Ruhuna','Southern Province',city_id FROM cities WHERE name='Matara' ORDER BY city_id LIMIT 1;

INSERT INTO users (user_id,full_name,email,password_hash,role,status) VALUES
(101,'Anura Perera','demo.landlord01@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(102,'Nadeesha Fernando','demo.landlord02@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(103,'Chaminda Jayasinghe','demo.landlord03@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(104,'Dilani Wijesinghe','demo.landlord04@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(105,'Ruwan Gunasekara','demo.landlord05@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(106,'Madhavi Senanayake','demo.landlord06@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(107,'Sunil Ratnayake','demo.landlord07@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(108,'Priyanka Herath','demo.landlord08@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(109,'Kamal Rathnayake','demo.landlord09@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(110,'Ishara Bandara','demo.landlord10@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','active'),
(111,'Malith Silva','demo.landlord11@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','pending'),
(112,'Shamali Abeysinghe','demo.landlord12@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','landlord','rejected'),
(201,'Kasun Edirisinghe','demo.student01@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(202,'Nimasha Wijeratne','demo.student02@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(203,'Sachini de Silva','demo.student03@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(204,'Tharindu Seneviratne','demo.student04@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(205,'Hiruni Jayawardena','demo.student05@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(206,'Dulan Peris','demo.student06@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(207,'Dinuka Madushanka','demo.student07@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(208,'Isuri Dissanayake','demo.student08@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(209,'Navodi Kularatne','demo.student09@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(210,'Sahan Lakshan','demo.student10@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(211,'Thinuli Randima','demo.student11@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(212,'Dilshan Maduwantha','demo.student12@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(213,'Ashini Gunathilake','demo.student13@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(214,'Dinesh Rukshan','demo.student14@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(215,'Janith Fernando','demo.student15@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(216,'Hasini Hewage','demo.student16@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(217,'Amila Samarasekara','demo.student17@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(218,'Thilini Kaushalya','demo.student18@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(219,'Sandaru Madhushan','demo.student19@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(220,'Piumi Madhara','demo.student20@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(221,'Pramod Peiris','demo.student21@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(222,'Rashmi Wickramasinghe','demo.student22@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(223,'Nethmi Hansika','demo.student23@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(224,'Shehan Priyadarshana','demo.student24@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(225,'Lakmi Gunasinghe','demo.student25@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(226,'Supun Lakmal','demo.student26@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(227,'Heshani Kavindi','demo.student27@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(228,'Yasiru Madushan','demo.student28@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(229,'Maleesha Sewwandi','demo.student29@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(230,'Sandunika Thilakaratne','demo.student30@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(231,'Ravindu Kalhara','demo.student31@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(232,'Anjali Siriwardena','demo.student32@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(233,'Dineth Pathirana','demo.student33@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','active'),
(234,'Gayani Perera','demo.student34@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','pending'),
(235,'Heshan Vihanga','demo.student35@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','pending'),
(236,'Upeksha Madushani','demo.student36@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','student','pending'),
(301,'Rasika Kumara','demo.agent01@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','field_agent','active'),
(302,'Tharushi Amaratunga','demo.agent02@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','field_agent','active'),
(303,'Devinda Gunathilaka','demo.agent03@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','field_agent','active'),
(304,'Ayesha Nisansala','demo.agent04@example.test','$2y$12$PS1H4s095yFHIWLF3w9JwOrDz/kvl8YFkiKDXl9QgiQK6OcTmKo9O','field_agent','active');

INSERT INTO landlords (landlord_id,user_id,nic_number,mobile,address,subsc_tier,subsc_expires,consent_agreed) VALUES
(1,101,'198112345678','0700000001','No. 8, Kelaniya','pro','2027-12-31',1),
(2,102,'198212345678','0700000002','No. 16, Kandy','pro','2027-12-31',1),
(3,103,'198312345678','0700000003','No. 24, Moratuwa','pro','2027-12-31',1),
(4,104,'198412345678','0700000004','No. 32, Peradeniya','pro','2027-12-31',1),
(5,105,'198512345678','0700000005','No. 40, Galle','standard',NULL,1),
(6,106,'198612345678','0700000006','No. 48, Colombo 06','standard',NULL,1),
(7,107,'198712345678','0700000007','No. 56, Kelaniya','standard',NULL,1),
(8,108,'198812345678','0700000008','No. 64, Kandy','standard',NULL,1),
(9,109,'198912345678','0700000009','No. 72, Moratuwa','standard',NULL,1),
(10,110,'199012345678','0700000010','No. 80, Peradeniya','standard',NULL,1),
(11,111,'199112345678','0700000011','No. 88, Galle','standard',NULL,1),
(12,112,'199212345678','0700000012','No. 96, Colombo 06','standard',NULL,1);

INSERT INTO students (student_id,user_id,nic_number,mobile,uni_id,academic_year,verf_tier) VALUES
(1,201,'200100000001','0710000001',2,'2','tier2'),
(2,202,'200200000002','0710000002',3,'3','tier2'),
(3,203,'200300000003','0710000003',4,'4','tier2'),
(4,204,'200400000004','0710000004',5,'1','tier2'),
(5,205,'200500000005','0710000005',6,'2','tier2'),
(6,206,'200600000006','0710000006',1,'3','tier2'),
(7,207,'200700000007','0710000007',2,'4','tier2'),
(8,208,'200800000008','0710000008',3,'1','tier2'),
(9,209,'200900000009','0710000009',4,'2','tier2'),
(10,210,'200000000010','0710000010',5,'3','tier2'),
(11,211,'200100000011','0710000011',6,'4','tier2'),
(12,212,'200200000012','0710000012',1,'1','tier2'),
(13,213,'200300000013','0710000013',2,'2','tier2'),
(14,214,'200400000014','0710000014',3,'3','tier2'),
(15,215,'200500000015','0710000015',4,'4','tier2'),
(16,216,'200600000016','0710000016',5,'1','tier2'),
(17,217,'200700000017','0710000017',6,'2','tier2'),
(18,218,'200800000018','0710000018',1,'3','tier2'),
(19,219,'200900000019','0710000019',2,'4','tier2'),
(20,220,'200000000020','0710000020',3,'1','tier2'),
(21,221,'200100000021','0710000021',4,'2','tier2'),
(22,222,'200200000022','0710000022',5,'3','tier2'),
(23,223,'200300000023','0710000023',6,'4','tier2'),
(24,224,'200400000024','0710000024',1,'1','tier2'),
(25,225,'200500000025','0710000025',2,'2','tier2'),
(26,226,'200600000026','0710000026',3,'3','tier2'),
(27,227,'200700000027','0710000027',4,'4','tier2'),
(28,228,'200800000028','0710000028',5,'1','tier2'),
(29,229,'200900000029','0710000029',6,'2','tier2'),
(30,230,'200000000030','0710000030',1,'3','tier2'),
(31,231,'200100000031','0710000031',2,'4','tier2'),
(32,232,'200200000032','0710000032',3,'1','tier2'),
(33,233,'200300000033','0710000033',4,'2','tier1'),
(34,234,'200400000034','0710000034',5,'3','tier1'),
(35,235,'200500000035','0710000035',6,'4','tier1'),
(36,236,'200600000036','0710000036',1,'1','tier1');

INSERT INTO field_agents (agent_id,user_id,mobile,assigned_city,is_active) VALUES
(1,301,'0750000001','Colombo',1),
(2,302,'0750000002','Kelaniya',1),
(3,303,'0750000003','Kandy',1),
(4,304,'0750000004','Galle',1);

INSERT INTO landlord_registration_details (landlord_id,identity_status,registration_stage,registration_deadline,approved_at,rejection_reason) VALUES
(1,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(2,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(3,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(4,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(5,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(6,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(7,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(8,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(9,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(10,'approved','completed',NULL,'2026-09-28 11:00:00',NULL),
(11,'submitted','submitted','2030-01-01 00:00:00',NULL,NULL),
(12,'rejected','submitted',NULL,NULL,'Demo rejection: identity details did not match');

INSERT INTO registration_approvals (user_id,reviewed_by,decision,rejection_reason) VALUES
(101,1,'approved',NULL),
(102,1,'approved',NULL),
(103,1,'approved',NULL),
(104,1,'approved',NULL),
(105,1,'approved',NULL),
(106,1,'approved',NULL),
(107,1,'approved',NULL),
(108,1,'approved',NULL),
(109,1,'approved',NULL),
(110,1,'approved',NULL),
(112,1,'rejected','NIC details mismatch');

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 1,1,'Wellawatte Campus Ladies Residence',city_id,'No. 24, Station Road, Wellawatte','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',25000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 2,1,'Bambalapitiya Study Nest',city_id,'No. 18, Galle Road, Bambalapitiya','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',30000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 3,1,'Kirulapone University Boarding',city_id,'No. 7, High Level Road, Kirulapone','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',20000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 4,2,'Kelaniya River View Rooms',city_id,'No. 51, Kandy Road, Kelaniya','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',17000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kelaniya' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 5,2,'Peliyagoda Student Stay',city_id,'No. 14, Biyagama Road, Peliyagoda','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',15500,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Peliyagoda' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 6,2,'Kadawatha Green Rooms',city_id,'No. 28, Mahara Junction, Kadawatha','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',14500,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kadawatha' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 7,3,'Peradeniya Hillside Boarding',city_id,'No. 12, Galaha Road, Peradeniya','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',24000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Peradeniya' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 8,3,'Kandy Lake Student Rooms',city_id,'No. 39, Ampitiya Road, Kandy','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',27000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kandy' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 9,4,'Katubedda Tech Residence',city_id,'No. 8, Bandaranayake Mawatha, Katubedda','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',19000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Moratuwa' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 10,4,'Moratuwa Coastal Annex',city_id,'No. 71, New Galle Road, Moratuwa','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',22000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Moratuwa' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 11,5,'Dehiwala Girls Boarding',city_id,'No. 6, Hill Street, Dehiwala','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',23500,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Dehiwala-Mount Lavinia' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 12,6,'Kotte Scholars Residence',city_id,'No. 4, Parliament Road, Kotte','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',26000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kotte' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 13,7,'Galle Fort Student Annex',city_id,'No. 14, Wakwella Road, Galle','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',18000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Galle' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 14,8,'Matara Town Shared Rooms',city_id,'No. 9, Nupe Junction, Matara','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','verified',13000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Matara' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 15,9,'Negombo Lagoon Student House',city_id,'No. 27, St Joseph Street, Negombo','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','pending',19000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Negombo' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 16,10,'Kurunegala Central Boarding',city_id,'No. 10, Lake Round, Kurunegala','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','suspended',16000,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kurunegala' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 17,1,'Colombo Quiet Courtyard New',city_id,'No. 5, Havelock Road, Colombo 05','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','pending',28000,NOW() FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO properties(property_id,landlord_id,title,city_id,address,structural_type,description,status,rent_amount,created_at) SELECT 18,2,'Kelaniya Newly Listed House',city_id,'No. 3, Ward Place, Kelaniya','boarding_house','Furnished student-focused boarding with reliable transport and a quiet study environment.','under_verification',16500,DATE_SUB(NOW(), INTERVAL 35 DAY) FROM cities WHERE name='Kelaniya' ORDER BY city_id LIMIT 1;

INSERT INTO property_landlord_details (property_id,verification_submitted_at,verification_notes) VALUES
(1,'2026-09-12 09:00:00','Field check completed'),
(2,'2026-09-12 09:00:00','Field check completed'),
(3,'2026-09-12 09:00:00','Field check completed'),
(4,'2026-09-12 09:00:00','Field check completed'),
(5,'2026-09-12 09:00:00','Field check completed'),
(6,'2026-09-12 09:00:00','Field check completed'),
(7,'2026-09-12 09:00:00','Field check completed'),
(8,'2026-09-12 09:00:00','Field check completed'),
(9,'2026-09-12 09:00:00','Field check completed'),
(10,'2026-09-12 09:00:00','Field check completed'),
(11,'2026-09-12 09:00:00','Field check completed'),
(12,'2026-09-12 09:00:00','Field check completed'),
(13,'2026-09-12 09:00:00','Field check completed'),
(14,'2026-09-12 09:00:00','Field check completed'),
(15,NULL,NULL),
(16,NULL,NULL),
(17,NULL,NULL),
(18,'2026-09-12 09:00:00',NULL);

INSERT INTO listings (listing_id,landlord_id,property_id,title,description,address,city,monthly_rent,status) VALUES
(1,1,1,'Wellawatte Campus Ladies Residence - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 24, Station Road, Wellawatte','Colombo',28000,'live'),
(2,1,1,'Wellawatte Campus Ladies Residence - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 24, Station Road, Wellawatte','Colombo',24000,'live'),
(3,1,2,'Bambalapitiya Study Nest - Private','Water, electricity and shared kitchen. Near local buses.','No. 18, Galle Road, Bambalapitiya','Colombo',33500,'live'),
(4,1,2,'Bambalapitiya Study Nest - Shared','Water, electricity and shared kitchen. Near local buses.','No. 18, Galle Road, Bambalapitiya','Colombo',29500,'live'),
(5,1,3,'Kirulapone University Boarding - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 7, High Level Road, Kirulapone','Colombo',22500,'live'),
(6,1,3,'Kirulapone University Boarding - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 7, High Level Road, Kirulapone','Colombo',18500,'live'),
(7,2,4,'Kelaniya River View Rooms - Private','Water, electricity and shared kitchen. Near local buses.','No. 51, Kandy Road, Kelaniya','Kelaniya',20000,'live'),
(8,2,4,'Kelaniya River View Rooms - Shared','Water, electricity and shared kitchen. Near local buses.','No. 51, Kandy Road, Kelaniya','Kelaniya',16000,'live'),
(9,2,5,'Peliyagoda Student Stay - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 14, Biyagama Road, Peliyagoda','Peliyagoda',19000,'live'),
(10,2,5,'Peliyagoda Student Stay - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 14, Biyagama Road, Peliyagoda','Peliyagoda',15000,'live'),
(11,2,6,'Kadawatha Green Rooms - Private','Water, electricity and shared kitchen. Near local buses.','No. 28, Mahara Junction, Kadawatha','Kadawatha',17000,'live'),
(12,2,6,'Kadawatha Green Rooms - Shared','Water, electricity and shared kitchen. Near local buses.','No. 28, Mahara Junction, Kadawatha','Kadawatha',13000,'live'),
(13,3,7,'Peradeniya Hillside Boarding - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 12, Galaha Road, Peradeniya','Peradeniya',27000,'live'),
(14,3,7,'Peradeniya Hillside Boarding - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 12, Galaha Road, Peradeniya','Peradeniya',23000,'live'),
(15,3,8,'Kandy Lake Student Rooms - Private','Water, electricity and shared kitchen. Near local buses.','No. 39, Ampitiya Road, Kandy','Kandy',30500,'live'),
(16,3,8,'Kandy Lake Student Rooms - Shared','Water, electricity and shared kitchen. Near local buses.','No. 39, Ampitiya Road, Kandy','Kandy',26500,'live'),
(17,4,9,'Katubedda Tech Residence - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 8, Bandaranayake Mawatha, Katubedda','Moratuwa',21500,'live'),
(18,4,9,'Katubedda Tech Residence - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 8, Bandaranayake Mawatha, Katubedda','Moratuwa',17500,'live'),
(19,4,10,'Moratuwa Coastal Annex - Private','Water, electricity and shared kitchen. Near local buses.','No. 71, New Galle Road, Moratuwa','Moratuwa',25000,'live'),
(20,4,10,'Moratuwa Coastal Annex - Shared','Water, electricity and shared kitchen. Near local buses.','No. 71, New Galle Road, Moratuwa','Moratuwa',21000,'live'),
(21,5,11,'Dehiwala Girls Boarding - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 6, Hill Street, Dehiwala','Dehiwala-Mount Lavinia',27000,'live'),
(22,5,11,'Dehiwala Girls Boarding - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 6, Hill Street, Dehiwala','Dehiwala-Mount Lavinia',23000,'live'),
(23,6,12,'Kotte Scholars Residence - Private','Water, electricity and shared kitchen. Near local buses.','No. 4, Parliament Road, Kotte','Kotte',28500,'live'),
(24,6,12,'Kotte Scholars Residence - Shared','Water, electricity and shared kitchen. Near local buses.','No. 4, Parliament Road, Kotte','Kotte',24500,'live'),
(25,7,13,'Galle Fort Student Annex - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 14, Wakwella Road, Galle','Galle',21000,'live'),
(26,7,13,'Galle Fort Student Annex - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 14, Wakwella Road, Galle','Galle',17000,'live'),
(27,8,14,'Matara Town Shared Rooms - Private','Water, electricity and shared kitchen. Near local buses.','No. 9, Nupe Junction, Matara','Matara',16500,'live'),
(28,8,14,'Matara Town Shared Rooms - Shared','Water, electricity and shared kitchen. Near local buses.','No. 9, Nupe Junction, Matara','Matara',12500,'live'),
(29,9,15,'Negombo Lagoon Student House - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 27, St Joseph Street, Negombo','Negombo',21500,'pending'),
(30,9,15,'Negombo Lagoon Student House - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 27, St Joseph Street, Negombo','Negombo',17500,'pending'),
(31,10,16,'Kurunegala Central Boarding - Private','Water, electricity and shared kitchen. Near local buses.','No. 10, Lake Round, Kurunegala','Kurunegala',19000,'pending'),
(32,10,16,'Kurunegala Central Boarding - Shared','Water, electricity and shared kitchen. Near local buses.','No. 10, Lake Round, Kurunegala','Kurunegala',15000,'pending'),
(33,1,17,'Colombo Quiet Courtyard New - Private','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 5, Havelock Road, Colombo 05','Colombo',31500,'pending'),
(34,1,17,'Colombo Quiet Courtyard New - Shared','Water, electricity and shared kitchen. Quiet hours after 10 PM.','No. 5, Havelock Road, Colombo 05','Colombo',27500,'pending'),
(35,2,18,'Kelaniya Newly Listed House - Private','Water, electricity and shared kitchen. Near local buses.','No. 3, Ward Place, Kelaniya','Kelaniya',19000,'pending'),
(36,2,18,'Kelaniya Newly Listed House - Shared','Water, electricity and shared kitchen. Near local buses.','No. 3, Ward Place, Kelaniya','Kelaniya',15000,'pending');

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(1,1,'single',1,1,1,0,56000,56000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'female',28000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(2,1,'shared',2,3,3,1,48000,48000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'female',24000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(3,2,'single',3,1,1,0,67000,67000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',33500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(4,2,'shared',4,2,2,1,59000,59000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',29500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(5,3,'single',5,1,1,0,45000,45000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',22500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(6,3,'shared',6,3,3,1,37000,37000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',18500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(7,4,'single',7,1,1,0,40000,40000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'male',20000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(8,4,'shared',8,2,2,1,32000,32000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'male',16000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(9,5,'single',9,1,1,0,38000,38000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',19000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(10,5,'shared',10,3,3,1,30000,30000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',15000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(11,6,'single',11,1,1,0,34000,34000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',17000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(12,6,'shared',12,2,2,1,26000,26000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',13000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(13,7,'single',13,1,1,0,54000,54000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',27000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(14,7,'shared',14,3,3,1,46000,46000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',23000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(15,8,'single',15,1,1,0,61000,61000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',30500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(16,8,'shared',16,2,2,1,53000,53000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',26500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(17,9,'single',17,1,1,0,43000,43000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'male',21500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(18,9,'shared',18,3,3,1,35000,35000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'male',17500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(19,10,'single',19,1,1,0,50000,50000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',25000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(20,10,'shared',20,2,2,1,42000,42000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',21000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(21,11,'single',21,1,1,0,54000,54000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'female',27000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(22,11,'shared',22,3,3,1,46000,46000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'female',23000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(23,12,'single',23,1,1,0,57000,57000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',28500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(24,12,'shared',24,2,2,1,49000,49000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',24500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(25,13,'single',25,1,1,0,42000,42000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',21000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(26,13,'shared',26,3,3,1,34000,34000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',17000,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(27,14,'single',27,1,1,0,33000,33000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',16500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(28,14,'shared',28,2,2,1,25000,25000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',12500,'verified',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(29,15,'single',29,1,1,0,43000,43000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',21500,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(30,15,'shared',30,3,3,1,35000,35000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',17500,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(31,16,'single',31,1,1,0,38000,38000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',19000,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(32,16,'shared',32,2,2,1,30000,30000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',15000,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(33,17,'single',33,1,1,0,63000,63000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',31500,'pending',NOW());

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(34,17,'shared',34,3,3,1,55000,55000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',27500,'pending',NOW());

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(35,18,'single',35,1,1,0,38000,38000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',19000,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO rooms(room_id,property_id,room_type,listing_id,slot_cap,slot_capacity,partial_occupancy,deposit,security_deposit,furnishing,bathroom_type,bathroom_access,wifi,wifi_available,house_rules,gender_pref,price,status,created_at) VALUES(36,18,'shared',36,2,2,1,30000,30000,'furnished','shared','shared',1,1,'No smoking indoors; respect housemates', 'any',15000,'pending',DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO room_landlord_details (room_id,room_label,rent_last_changed_at,rent_locked_until) VALUES
(1,'Private Room 1','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(2,'Shared Room 1','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(3,'Private Room 2','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(4,'Shared Room 2','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(5,'Private Room 3','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(6,'Shared Room 3','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(7,'Private Room 4','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(8,'Shared Room 4','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(9,'Private Room 5','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(10,'Shared Room 5','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(11,'Private Room 6','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(12,'Shared Room 6','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(13,'Private Room 7','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(14,'Shared Room 7','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(15,'Private Room 8','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(16,'Shared Room 8','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(17,'Private Room 9','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(18,'Shared Room 9','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(19,'Private Room 10','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(20,'Shared Room 10','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(21,'Private Room 11','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(22,'Shared Room 11','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(23,'Private Room 12','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(24,'Shared Room 12','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(25,'Private Room 13','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(26,'Shared Room 13','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(27,'Private Room 14','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(28,'Shared Room 14','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(29,'Private Room 15','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(30,'Shared Room 15','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(31,'Private Room 16','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(32,'Shared Room 16','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(33,'Private Room 17','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(34,'Shared Room 17','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(35,'Private Room 18','2026-09-18 10:30:00','2027-09-18 10:30:00'),
(36,'Shared Room 18','2026-09-18 10:30:00','2027-09-18 10:30:00');

INSERT INTO room_slots (slot_id,room_id,slot_no,status) VALUES
(1,1,1,'available'),
(2,2,1,'available'),
(3,2,2,'available'),
(4,2,3,'available'),
(5,3,1,'available'),
(6,4,1,'available'),
(7,4,2,'available'),
(8,5,1,'available'),
(9,6,1,'available'),
(10,6,2,'available'),
(11,6,3,'available'),
(12,7,1,'available'),
(13,8,1,'available'),
(14,8,2,'available'),
(15,9,1,'available'),
(16,10,1,'available'),
(17,10,2,'available'),
(18,10,3,'available'),
(19,11,1,'available'),
(20,12,1,'available'),
(21,12,2,'available'),
(22,13,1,'available'),
(23,14,1,'available'),
(24,14,2,'available'),
(25,14,3,'available'),
(26,15,1,'available'),
(27,16,1,'available'),
(28,16,2,'available'),
(29,17,1,'available'),
(30,18,1,'available'),
(31,18,2,'available'),
(32,18,3,'available'),
(33,19,1,'available'),
(34,20,1,'available'),
(35,20,2,'available'),
(36,21,1,'available'),
(37,22,1,'available'),
(38,22,2,'available'),
(39,22,3,'available'),
(40,23,1,'available'),
(41,24,1,'available'),
(42,24,2,'available'),
(43,25,1,'available'),
(44,26,1,'available'),
(45,26,2,'available'),
(46,26,3,'available'),
(47,27,1,'available'),
(48,28,1,'available'),
(49,28,2,'available'),
(50,29,1,'available'),
(51,30,1,'available'),
(52,30,2,'available'),
(53,30,3,'available'),
(54,31,1,'available'),
(55,32,1,'available'),
(56,32,2,'available'),
(57,33,1,'available'),
(58,34,1,'available'),
(59,34,2,'available'),
(60,34,3,'available'),
(61,35,1,'available'),
(62,36,1,'available'),
(63,36,2,'available');

INSERT INTO room_slot_blocks (slot_id,reason,blocked_by_user_id) VALUES
(45,'Bathroom plumbing repair; expected clearance after inspection',107);

UPDATE room_slots SET status='occupied',student_id=1,move_in='2026-10-02' WHERE slot_id=1;

UPDATE room_slots SET status='occupied',student_id=2,move_in='2026-10-02' WHERE slot_id=2;

UPDATE room_slots SET status='occupied',student_id=3,move_in='2026-10-02' WHERE slot_id=5;

UPDATE room_slots SET status='occupied',student_id=4,move_in='2026-10-02' WHERE slot_id=6;

UPDATE room_slots SET status='occupied',student_id=5,move_in='2026-10-02' WHERE slot_id=8;

UPDATE room_slots SET status='occupied',student_id=6,move_in='2026-10-02' WHERE slot_id=9;

UPDATE room_slots SET status='occupied',student_id=7,move_in='2026-10-02' WHERE slot_id=12;

UPDATE room_slots SET status='occupied',student_id=8,move_in='2026-10-02' WHERE slot_id=13;

UPDATE room_slots SET status='pending',student_id=9,move_in=NULL WHERE slot_id=15;

UPDATE room_slots SET status='pending',student_id=10,move_in=NULL WHERE slot_id=16;

UPDATE room_slots SET status='pending',student_id=11,move_in=NULL WHERE slot_id=19;

UPDATE room_slots SET status='pending',student_id=12,move_in=NULL WHERE slot_id=20;

UPDATE room_slots SET status='pending',student_id=13,move_in=NULL WHERE slot_id=22;

UPDATE room_slots SET status='pending',student_id=14,move_in=NULL WHERE slot_id=23;

UPDATE room_slots SET status='pending',student_id=15,move_in=NULL WHERE slot_id=26;

UPDATE room_slots SET status='pending',student_id=16,move_in=NULL WHERE slot_id=27;

UPDATE room_slots SET status='pending',student_id=17,move_in=NULL WHERE slot_id=29;

UPDATE room_slots SET status='pending',student_id=18,move_in=NULL WHERE slot_id=30;

UPDATE room_slots SET status='pending',student_id=19,move_in=NULL WHERE slot_id=33;

UPDATE room_slots SET status='pending',student_id=20,move_in=NULL WHERE slot_id=34;

INSERT INTO bookings (booking_id,student_id,slot_id,bk_status) VALUES
(1,1,1,'confirmed'),
(2,2,2,'confirmed'),
(3,3,5,'confirmed'),
(4,4,6,'confirmed'),
(5,5,8,'confirmed'),
(6,6,9,'confirmed'),
(7,7,12,'confirmed'),
(8,8,13,'confirmed'),
(9,9,15,'confirmed'),
(10,10,16,'confirmed'),
(11,11,19,'confirmed'),
(12,12,20,'confirmed'),
(13,13,22,'confirmed'),
(14,14,23,'confirmed'),
(15,15,26,'pending'),
(16,16,27,'pending'),
(17,17,29,'pending'),
(18,18,30,'pending'),
(19,19,33,'pending'),
(20,20,34,'pending'),
(21,21,36,'completed'),
(22,22,37,'completed'),
(23,23,40,'completed'),
(24,24,41,'completed'),
(25,25,43,'completed'),
(26,26,44,'cancelled'),
(27,27,47,'cancelled'),
(28,28,48,'cancelled'),
(29,29,50,'cancelled'),
(30,30,51,'cancelled'),
(31,31,54,'cancelled'),
(32,32,55,'cancelled');

INSERT INTO booking_landlord_actions (booking_id,landlord_id,decision,decision_at,rejection_reason,occupancy_status,move_in_date,move_out_date) VALUES
(1,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(2,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(3,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(4,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(5,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(6,1,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(7,2,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(8,2,'accepted','2026-10-12 12:00:00',NULL,'occupied','2026-10-02',NULL),
(9,2,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(10,2,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(11,2,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(12,2,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(13,3,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(14,3,'accepted','2026-10-12 12:00:00',NULL,'not_moved_in',NULL,NULL),
(15,3,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(16,3,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(17,4,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(18,4,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(19,4,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(20,4,'pending',NULL,NULL,'not_moved_in',NULL,NULL),
(21,5,'accepted','2026-10-12 12:00:00',NULL,'moved_out','2026-08-20','2026-09-30'),
(22,5,'accepted','2026-10-12 12:00:00',NULL,'moved_out','2026-08-20','2026-09-30'),
(23,6,'accepted','2026-10-12 12:00:00',NULL,'moved_out','2026-08-20','2026-09-30'),
(24,6,'accepted','2026-10-12 12:00:00',NULL,'moved_out','2026-08-20','2026-09-30'),
(25,7,'accepted','2026-10-12 12:00:00',NULL,'moved_out','2026-08-20','2026-09-30'),
(26,7,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(27,8,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(28,8,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(29,9,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(30,9,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(31,10,'rejected','2026-10-12 12:00:00','Rejected: no suitable verification / full capacity','not_moved_in',NULL,NULL),
(32,10,'pending',NULL,NULL,'not_moved_in',NULL,NULL);

INSERT INTO slot_reservations (booking_id,student_id,slot_id,reservation_status) VALUES
(1,1,1,'occupied'),
(2,2,2,'occupied'),
(3,3,5,'occupied'),
(4,4,6,'occupied'),
(5,5,8,'occupied'),
(6,6,9,'occupied'),
(7,7,12,'occupied'),
(8,8,13,'occupied'),
(9,9,15,'reserved'),
(10,10,16,'reserved'),
(11,11,19,'reserved'),
(12,12,20,'reserved'),
(13,13,22,'reserved'),
(14,14,23,'reserved'),
(15,15,26,'reserved'),
(16,16,27,'reserved'),
(17,17,29,'reserved'),
(18,18,30,'reserved'),
(19,19,33,'reserved'),
(20,20,34,'reserved');

INSERT INTO payments (payment_id,booking_id,amount,pay_type,status) VALUES
(1,1,28000,'cash','completed'),
(2,2,24000,'card','completed'),
(3,3,33500,'bank_transfer','completed'),
(4,4,29500,'cash','completed'),
(5,5,22500,'card','completed'),
(6,6,18500,'bank_transfer','completed'),
(7,7,20000,'cash','completed'),
(8,8,16000,'card','completed'),
(9,9,19000,'bank_transfer','completed'),
(10,10,15000,'cash','completed'),
(11,11,17000,'card','pending'),
(12,12,13000,'bank_transfer','pending'),
(13,13,27000,'cash','pending'),
(14,14,23000,'card','pending');

INSERT INTO group_bookings (group_id,lead_student_id,grp_status,member_count) VALUES
(1,9,'active',3),
(2,17,'pending',2);

INSERT INTO group_booking_members (group_id,student_id) VALUES
(1,9),
(1,10),
(1,11),
(2,17),
(2,18);

INSERT INTO group_booking_items (group_id,booking_id) VALUES
(1,9),
(1,10),
(1,11),
(2,17),
(2,18);

INSERT INTO property_facilities (property_id,facility_type,facility_name,distance_meters,verification_status,verified_by_agent_id) VALUES
(1,'grocery','Daily grocery shop',365,'verified',2),
(1,'pharmacy','Pharmacy and clinic',665,'verified',2),
(2,'pharmacy','Pharmacy and clinic',680,'verified',3),
(2,'railway_station','Railway station',1230,'verified',3),
(3,'railway_station','Railway station',1245,'verified',4),
(3,'bus_route','Nearest bus stop',495,'verified',4),
(4,'bus_route','Nearest bus stop',510,'verified',1),
(4,'grocery','Daily grocery shop',410,'verified',1),
(5,'grocery','Daily grocery shop',425,'verified',2),
(5,'pharmacy','Pharmacy and clinic',725,'verified',2),
(6,'pharmacy','Pharmacy and clinic',740,'verified',3),
(6,'railway_station','Railway station',1290,'verified',3),
(7,'railway_station','Railway station',1305,'verified',4),
(7,'bus_route','Nearest bus stop',555,'verified',4),
(8,'bus_route','Nearest bus stop',570,'verified',1),
(8,'grocery','Daily grocery shop',470,'verified',1),
(9,'grocery','Daily grocery shop',485,'pending',NULL),
(9,'pharmacy','Pharmacy and clinic',785,'pending',NULL),
(10,'pharmacy','Pharmacy and clinic',800,'pending',NULL),
(10,'railway_station','Railway station',1350,'pending',NULL),
(11,'railway_station','Railway station',1365,'pending',NULL),
(11,'bus_route','Nearest bus stop',615,'pending',NULL),
(12,'bus_route','Nearest bus stop',630,'pending',NULL),
(12,'grocery','Daily grocery shop',530,'pending',NULL),
(13,'grocery','Daily grocery shop',545,'pending',NULL),
(13,'pharmacy','Pharmacy and clinic',845,'pending',NULL),
(14,'pharmacy','Pharmacy and clinic',860,'pending',NULL),
(14,'railway_station','Railway station',1410,'pending',NULL),
(15,'railway_station','Railway station',1425,'pending',NULL),
(15,'bus_route','Nearest bus stop',675,'pending',NULL),
(16,'bus_route','Nearest bus stop',690,'pending',NULL),
(16,'grocery','Daily grocery shop',590,'pending',NULL),
(17,'grocery','Daily grocery shop',605,'pending',NULL),
(17,'pharmacy','Pharmacy and clinic',905,'pending',NULL),
(18,'pharmacy','Pharmacy and clinic',920,'pending',NULL),
(18,'railway_station','Railway station',1470,'pending',NULL);

INSERT INTO property_checklists (property_id,landlord_id,items,notes) VALUES
(1,1,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(2,1,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(3,1,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules'),
(4,2,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(5,2,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(6,2,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules'),
(7,3,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(8,3,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(9,4,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules'),
(10,4,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(11,5,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(12,6,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules'),
(13,7,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(14,8,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(15,9,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules'),
(16,10,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(17,1,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": true, "water_supply": true}','Inspection checklist submitted with house rules'),
(18,2,'{"fire_exit": true, "electrical_safety": true, "kitchen_available": false, "water_supply": true}','Inspection checklist submitted with house rules');

INSERT INTO agent_tasks (task_id,property_id,task_type,agent_id,status) VALUES
(1,1,'verification',2,'completed'),
(2,2,'verification',3,'completed'),
(3,3,'verification',4,'completed'),
(4,4,'verification',1,'completed'),
(5,5,'verification',2,'completed'),
(6,6,'verification',3,'completed'),
(7,7,'verification',4,'completed'),
(8,8,'verification',1,'completed'),
(9,18,'verification',2,'in_progress');

INSERT INTO verification_reports (task_id,field_agent_user_id,structural_safety,electrical_safety,fire_exit,gps_match,neighborhood_safety,furnishing_match,bathroom_match,kitchen_food_match,wifi_match,finance_match,agent_comments) VALUES
(1,302,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(2,303,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(3,304,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(4,301,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(5,302,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(6,303,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(7,304,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.'),
(8,301,1,1,1,1,4,1,1,1,1,1,'Site visited; utilities matched the landlord declaration.');

INSERT INTO area_profiles(city_id,safety_stats,transport,amenity_logs) SELECT city_id,'Public transport access and typical urban safety considerations; verify locally.','Bus and railway services available depending on neighborhood.','Grocery, hospital and pharmacy options within commuting range.' FROM cities WHERE name='Colombo' ORDER BY city_id LIMIT 1;

INSERT INTO area_profiles(city_id,safety_stats,transport,amenity_logs) SELECT city_id,'Public transport access and typical urban safety considerations; verify locally.','Bus and railway services available depending on neighborhood.','Grocery, hospital and pharmacy options within commuting range.' FROM cities WHERE name='Kelaniya' ORDER BY city_id LIMIT 1;

INSERT INTO area_profiles(city_id,safety_stats,transport,amenity_logs) SELECT city_id,'Public transport access and typical urban safety considerations; verify locally.','Bus and railway services available depending on neighborhood.','Grocery, hospital and pharmacy options within commuting range.' FROM cities WHERE name='Peradeniya' ORDER BY city_id LIMIT 1;

INSERT INTO area_profiles(city_id,safety_stats,transport,amenity_logs) SELECT city_id,'Public transport access and typical urban safety considerations; verify locally.','Bus and railway services available depending on neighborhood.','Grocery, hospital and pharmacy options within commuting range.' FROM cities WHERE name='Moratuwa' ORDER BY city_id LIMIT 1;

INSERT INTO area_profiles(city_id,safety_stats,transport,amenity_logs) SELECT city_id,'Public transport access and typical urban safety considerations; verify locally.','Bus and railway services available depending on neighborhood.','Grocery, hospital and pharmacy options within commuting range.' FROM cities WHERE name='Galle' ORDER BY city_id LIMIT 1;

INSERT INTO area_reports (agent_id,city,transport_details,amenities_details,safety_details,status) VALUES
(1,'Colombo','Multiple bus routes and railway station','Markets, hospitals and pharmacies','Well-lit main roads; inspect smaller lanes','approved'),
(2,'Kelaniya','Local buses and railway line','Supermarkets and student services','Residential setting; confirm curfew policies','approved'),
(3,'Kandy','Town buses and hill-country connections','University-centered amenities','Hilly roads and busy junctions','pending'),
(4,'Galle','Town bus routes','Nearby services and stores','Tourist zones may have high traffic','approved');

INSERT INTO subscription_payments (landlord_id,tier,amount,payhere_order_id,status,paid_at) VALUES
(1,'pro',4900,'DEMO-SUB-2026-0001','success','2026-09-25 12:00:00'),
(2,'pro',4900,'DEMO-SUB-2026-0002','success','2026-09-25 12:00:00'),
(3,'pro',4900,'DEMO-SUB-2026-0003','success','2026-09-25 12:00:00'),
(4,'pro',4900,'DEMO-SUB-2026-0004','success','2026-09-25 12:00:00'),
(5,'pro',4900,'DEMO-SUB-2026-0005','failed',NULL);

INSERT INTO room_rent_history (room_id,changed_by_user_id,old_price,new_price,reason,changed_at) VALUES
(1,101,27000,28000,'Annual adjustment for maintenance','2026-09-18 10:30:00'),
(3,101,33000,33500,'Electricity and water price review','2026-09-18 10:30:00');

INSERT INTO saved_listings (student_id,listing_id) VALUES
(1,2),
(2,3),
(3,4),
(4,5),
(5,6),
(6,7),
(7,8),
(8,9),
(9,10),
(10,11),
(11,12),
(12,13),
(13,14),
(14,15),
(15,16),
(16,17),
(17,18),
(18,19),
(19,20),
(20,1);

INSERT INTO reviews (listing_id,student_id,rating,review_text) VALUES
(22,22,4,'Clean rooms and a convenient commute; communication was helpful.'),
(23,23,5,'Clean rooms and a convenient commute; communication was helpful.'),
(24,24,3,'Clean rooms and a convenient commute; communication was helpful.'),
(25,25,4,'Clean rooms and a convenient commute; communication was helpful.'),
(26,26,5,'Clean rooms and a convenient commute; communication was helpful.');

INSERT INTO landlord_complaints (landlord_id,target_student_id,booking_id,category,description,priority,status) VALUES
(1,2,2,'property_damage','Damage to a shared cupboard; admin review requested.','medium','submitted'),
(1,5,5,'conduct','Repeated disturbance during agreed quiet hours.','low','under_review');

INSERT INTO complaints (listing_id,complainant_user_id,landlord_user_id,category,description,status) VALUES
(22,222,105,'maintenance_issue','The bathroom tap has been leaking for several days.','assigned'),
(23,223,106,'amenity_discrepancy','Wi-Fi speed was below what the listing described.','under_investigation');

INSERT INTO complaint_investigations (complaint_id,field_agent_user_id,findings,visit_fee_charged) VALUES
(1,301,'Maintenance request forwarded to owner; photo evidence reviewed.',0);

INSERT INTO announcements (sent_by,audience,subject,message,priority) VALUES
(1,'all','BoardNest welcomes new residents','Please review your room and safety guidelines.','normal'),
(1,'landlord','Room verification reminder','Keep facilities checklists up to date.','normal'),
(1,'student','October boarding safety tips','Check your lease, fire exits and nearest bus stop.','urgent'),
(1,'field_agent','Inspection report submission','Submit completed site visits before the end of the week.','normal');

INSERT INTO announcement_reads (announcements_id,user_id) VALUES
(1,101),
(1,102),
(2,101),
(3,201),
(3,202),
(4,301);

INSERT INTO conversations (conversation_id,student_id,landlord_id,room_id,booking_id,last_message_at) VALUES
(1,1,1,1,1,'2026-10-16 18:30:00'),
(2,2,1,2,2,'2026-10-16 18:30:00'),
(3,3,1,3,3,'2026-10-16 18:30:00'),
(4,4,1,4,4,'2026-10-16 18:30:00'),
(5,5,1,5,5,'2026-10-16 18:30:00'),
(6,6,1,6,6,'2026-10-16 18:30:00'),
(7,7,2,7,7,'2026-10-16 18:30:00'),
(8,8,2,8,8,'2026-10-16 18:30:00'),
(9,9,2,9,9,'2026-10-16 18:30:00'),
(10,10,2,10,10,'2026-10-16 18:30:00'),
(11,11,2,11,11,'2026-10-16 18:30:00'),
(12,12,2,12,12,'2026-10-16 18:30:00'),
(13,13,3,13,13,'2026-10-16 18:30:00'),
(14,14,3,14,14,'2026-10-16 18:30:00');

INSERT INTO messages (conversation_id,sender_user_id,message_text,sent_at) VALUES
(1,201,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(1,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(1,201,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(2,202,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(2,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(2,202,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(3,203,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(3,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(3,203,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(4,204,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(4,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(4,204,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(5,205,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(5,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(5,205,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(6,206,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(6,101,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(6,206,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(7,207,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(7,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(7,207,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(8,208,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(8,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(8,208,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(9,209,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(9,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(9,209,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(10,210,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(10,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(10,210,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(11,211,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(11,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(11,211,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(12,212,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(12,102,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(12,212,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(13,213,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(13,103,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(13,213,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00'),
(14,214,'Hello, is the room available for move-in next month?','2026-10-15 08:30:00'),
(14,103,'Thanks for contacting us. Please confirm your university and intended date.','2026-10-15 09:10:00'),
(14,214,'I study nearby and prefer a quiet room. Thank you!','2026-10-16 18:30:00');

INSERT INTO notifications (user_id,type,message,link_url) VALUES
(201,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(202,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(203,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(204,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(205,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(206,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(207,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(208,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(209,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(210,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(211,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(212,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(213,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(214,'booking_accepted','Your BoardNest booking request was accepted.','/public/student/bookings.php'),
(101,'announcement','A new platform announcement has been posted.','/public/landlord/announcements'),
(101,'announcement','A new platform announcement has been posted.','/public/landlord/announcements'),
(102,'announcement','A new platform announcement has been posted.','/public/landlord/announcements');

INSERT INTO listing_decisions (listing_id,admin_user_id,decision) VALUES
(1,1,'approved'),
(2,1,'approved'),
(3,1,'approved'),
(4,1,'approved'),
(5,1,'approved'),
(6,1,'approved'),
(7,1,'approved'),
(8,1,'approved'),
(9,1,'approved'),
(10,1,'approved'),
(11,1,'approved'),
(12,1,'approved'),
(13,1,'approved'),
(14,1,'approved'),
(15,1,'approved'),
(16,1,'approved'),
(17,1,'approved'),
(18,1,'approved'),
(19,1,'approved'),
(20,1,'approved'),
(21,1,'approved'),
(22,1,'approved'),
(23,1,'approved'),
(24,1,'approved'),
(25,1,'approved'),
(26,1,'approved'),
(27,1,'approved'),
(28,1,'approved');

INSERT INTO landlord_property_archives (property_id,landlord_id) VALUES
(16,10);

INSERT INTO property_photos (property_id,file_path,caption,is_primary) VALUES
(1,'assets/uploads/landlord/properties/property_01.jpg','Demo property photograph 1',1),
(2,'assets/uploads/landlord/properties/property_02.jpg','Demo property photograph 2',1),
(3,'assets/uploads/landlord/properties/property_03.jpg','Demo property photograph 3',1),
(4,'assets/uploads/landlord/properties/property_04.jpg','Demo property photograph 4',1),
(5,'assets/uploads/landlord/properties/property_05.jpg','Demo property photograph 5',1),
(6,'assets/uploads/landlord/properties/property_06.jpg','Demo property photograph 6',1),
(7,'assets/uploads/landlord/properties/property_07.jpg','Demo property photograph 7',1),
(8,'assets/uploads/landlord/properties/property_08.jpg','Demo property photograph 8',1),
(9,'assets/uploads/landlord/properties/property_09.jpg','Demo property photograph 9',1),
(10,'assets/uploads/landlord/properties/property_10.jpg','Demo property photograph 10',1),
(11,'assets/uploads/landlord/properties/property_11.jpg','Demo property photograph 11',1),
(12,'assets/uploads/landlord/properties/property_12.jpg','Demo property photograph 12',1);

INSERT INTO room_photos (room_id,file_path,caption,is_primary) VALUES
(1,'assets/uploads/landlord/rooms/room_01.jpg','Demo room photograph 1',1),
(2,'assets/uploads/landlord/rooms/room_02.jpg','Demo room photograph 2',1),
(3,'assets/uploads/landlord/rooms/room_03.jpg','Demo room photograph 3',1),
(4,'assets/uploads/landlord/rooms/room_04.jpg','Demo room photograph 4',1),
(5,'assets/uploads/landlord/rooms/room_05.jpg','Demo room photograph 5',1),
(6,'assets/uploads/landlord/rooms/room_06.jpg','Demo room photograph 6',1),
(7,'assets/uploads/landlord/rooms/room_07.jpg','Demo room photograph 7',1),
(8,'assets/uploads/landlord/rooms/room_08.jpg','Demo room photograph 8',1),
(9,'assets/uploads/landlord/rooms/room_09.jpg','Demo room photograph 9',1),
(10,'assets/uploads/landlord/rooms/room_10.jpg','Demo room photograph 10',1),
(11,'assets/uploads/landlord/rooms/room_11.jpg','Demo room photograph 11',1),
(12,'assets/uploads/landlord/rooms/room_12.jpg','Demo room photograph 12',1),
(13,'assets/uploads/landlord/rooms/room_13.jpg','Demo room photograph 13',1),
(14,'assets/uploads/landlord/rooms/room_14.jpg','Demo room photograph 14',1),
(15,'assets/uploads/landlord/rooms/room_15.jpg','Demo room photograph 15',1),
(16,'assets/uploads/landlord/rooms/room_16.jpg','Demo room photograph 16',1);

INSERT INTO listing_photos (listing_id,photo_url,is_primary) VALUES
(1,'/public/landlord/assets/uploads/landlord/properties/property_01.jpg',1),
(2,'/public/landlord/assets/uploads/landlord/properties/property_02.jpg',1),
(3,'/public/landlord/assets/uploads/landlord/properties/property_03.jpg',1),
(4,'/public/landlord/assets/uploads/landlord/properties/property_04.jpg',1),
(5,'/public/landlord/assets/uploads/landlord/properties/property_05.jpg',1),
(6,'/public/landlord/assets/uploads/landlord/properties/property_06.jpg',1),
(7,'/public/landlord/assets/uploads/landlord/properties/property_07.jpg',1),
(8,'/public/landlord/assets/uploads/landlord/properties/property_08.jpg',1),
(9,'/public/landlord/assets/uploads/landlord/properties/property_09.jpg',1),
(10,'/public/landlord/assets/uploads/landlord/properties/property_10.jpg',1),
(11,'/public/landlord/assets/uploads/landlord/properties/property_11.jpg',1),
(12,'/public/landlord/assets/uploads/landlord/properties/property_12.jpg',1);
-- V7 LOCATION DEMO: fictional address searches, NOT verified property GPS pins.
-- This field already exists in the original 27-table group schema.
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Wellawatte%20Colombo%20Sri%20Lanka' WHERE property_id=1;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Bambalapitiya%20Colombo%20Sri%20Lanka' WHERE property_id=2;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kirulapone%20Colombo%20Sri%20Lanka' WHERE property_id=3;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kelaniya%20Gampaha%20Sri%20Lanka' WHERE property_id=4;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Peliyagoda%20Gampaha%20Sri%20Lanka' WHERE property_id=5;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kadawatha%20Gampaha%20Sri%20Lanka' WHERE property_id=6;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Peradeniya%20Kandy%20Sri%20Lanka' WHERE property_id=7;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kandy%20Sri%20Lanka' WHERE property_id=8;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Katubedda%20Moratuwa%20Sri%20Lanka' WHERE property_id=9;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Moratuwa%20Sri%20Lanka' WHERE property_id=10;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Dehiwala%20Sri%20Lanka' WHERE property_id=11;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kotte%20Sri%20Lanka' WHERE property_id=12;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Galle%20Sri%20Lanka' WHERE property_id=13;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Matara%20Sri%20Lanka' WHERE property_id=14;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Negombo%20Sri%20Lanka' WHERE property_id=15;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kurunegala%20Sri%20Lanka' WHERE property_id=16;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Havelock%20Road%20Colombo%20Sri%20Lanka' WHERE property_id=17;
UPDATE properties SET maps_url='https://www.google.com/maps/search/?api=1&query=Kelaniya%20Sri%20Lanka' WHERE property_id=18;
COMMIT;
