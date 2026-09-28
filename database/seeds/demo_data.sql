-- BoardNest development demo data
-- Run this after schema.sql when sample accounts and listings are needed.
USE boardnest;

-- Default admin account (password: admin123)
INSERT INTO users (full_name, email, password_hash, role, status)
SELECT
    'Admin',
    'admin@boardnest.lk',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'active'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@boardnest.lk'
);

SET @demo_admin_user_id = (
    SELECT user_id FROM users WHERE email = 'admin@boardnest.lk' LIMIT 1
);

INSERT INTO admin (user_id)
SELECT @demo_admin_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM admin WHERE user_id = @demo_admin_user_id
);

-- ========================================================
-- DEVELOPMENT MOCK DATA FOR LISTING SEARCH AND DETAIL PAGES
-- ========================================================

INSERT INTO users (full_name, email, password_hash, role, status)
SELECT
    'Sunil Perera',
    'sunil@boardnest.lk',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'landlord',
    'active'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'sunil@boardnest.lk'
);

SET @demo_landlord_user_id = (
    SELECT user_id FROM users WHERE email = 'sunil@boardnest.lk' LIMIT 1
);

INSERT INTO landlords (user_id, nic_number, mobile, address, subsc_tier, consent_agreed)
SELECT
    @demo_landlord_user_id,
    '198812345678',
    '0771234567',
    'No 14, Galle Road, Moratuwa',
    'pro',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM landlords WHERE user_id = @demo_landlord_user_id
);

SET @demo_landlord_id = (
    SELECT landlord_id FROM landlords WHERE user_id = @demo_landlord_user_id LIMIT 1
);

-- Demo student account (password: password).
-- Upgrade older demo databases without creating a second student account.
UPDATE users AS old_student
LEFT JOIN users AS current_student
    ON current_student.email = 'pawanijayasinghe@stu.ucsc.cmb.ac.lk'
   AND current_student.user_id <> old_student.user_id
SET
    old_student.full_name = 'Pawani Jayasinghe',
    old_student.email = 'pawanijayasinghe@stu.ucsc.cmb.ac.lk'
WHERE old_student.email IN ('kavya@student.lk', 'pawani@student.lk')
  AND current_student.user_id IS NULL;

INSERT INTO users (full_name, email, password_hash, role, status)
SELECT
    'Pawani Jayasinghe',
    'pawanijayasinghe@stu.ucsc.cmb.ac.lk',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'active'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'pawanijayasinghe@stu.ucsc.cmb.ac.lk'
);

SET @demo_student_user_id = (
    SELECT user_id FROM users WHERE email = 'pawanijayasinghe@stu.ucsc.cmb.ac.lk' LIMIT 1
);

INSERT INTO students (user_id, nic_number, mobile, university, academic_year, verf_tier)
SELECT
    @demo_student_user_id,
    '200134567890',
    '0769876543',
    'University of Moratuwa',
    '2nd Year',
    'tier2'
WHERE NOT EXISTS (
    SELECT 1 FROM students WHERE user_id = @demo_student_user_id
);

SET @demo_student_id = (
    SELECT student_id FROM students WHERE user_id = @demo_student_user_id LIMIT 1
);

INSERT INTO properties (
    landlord_id, address, city, structural_type,
    latitude, longitude, maps_url, shared_facilities
)
SELECT
    @demo_landlord_id,
    'No 45, Station Road, Katubedda, Moratuwa',
    'Moratuwa',
    'annex',
    6.77320000,
    79.88210000,
    'https://maps.google.com/?q=6.7732,79.8821',
    'Parking, Common Kitchen, Garden, Security Gate'
WHERE NOT EXISTS (
    SELECT 1 FROM properties
    WHERE landlord_id = @demo_landlord_id
      AND address = 'No 45, Station Road, Katubedda, Moratuwa'
);

INSERT INTO properties (
    landlord_id, address, city, structural_type,
    latitude, longitude, maps_url, shared_facilities
)
SELECT
    @demo_landlord_id,
    'No 12, Temple Lane, Dehiwala',
    'Dehiwala',
    'boarding_house',
    6.84500000,
    79.86300000,
    'https://maps.google.com/?q=6.845,79.863',
    'Common Bathroom, Laundry Area, Common Lounge'
WHERE NOT EXISTS (
    SELECT 1 FROM properties
    WHERE landlord_id = @demo_landlord_id
      AND address = 'No 12, Temple Lane, Dehiwala'
);

SET @demo_property_one = (
    SELECT property_id FROM properties
    WHERE landlord_id = @demo_landlord_id
      AND address = 'No 45, Station Road, Katubedda, Moratuwa'
    LIMIT 1
);

SET @demo_property_two = (
    SELECT property_id FROM properties
    WHERE landlord_id = @demo_landlord_id
      AND address = 'No 12, Temple Lane, Dehiwala'
    LIMIT 1
);

INSERT INTO listings (landlord_id, title, description, address, city, monthly_rent, status)
SELECT
    @demo_landlord_id,
    'Furnished Annex Room Near UoM',
    'A clean, fully furnished single room in a quiet annex close to the University of Moratuwa main gate. Ideal for female students. Includes attached bathroom and WiFi.',
    'No 45, Station Road, Katubedda, Moratuwa',
    'Moratuwa',
    18500.00,
    'live'
WHERE NOT EXISTS (
    SELECT 1 FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Furnished Annex Room Near UoM'
);

INSERT INTO listings (landlord_id, title, description, address, city, monthly_rent, status)
SELECT
    @demo_landlord_id,
    'Shared Room for 2 - Moratuwa',
    'A spacious shared room designed for two students. Currently one slot is available. Located 10 minutes walk from UoM.',
    'No 45, Station Road, Katubedda, Moratuwa',
    'Moratuwa',
    14000.00,
    'live'
WHERE NOT EXISTS (
    SELECT 1 FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Shared Room for 2 - Moratuwa'
);

INSERT INTO listings (landlord_id, title, description, address, city, monthly_rent, status)
SELECT
    @demo_landlord_id,
    'Budget Boarding Room - Dehiwala',
    'Affordable semi-furnished room in a managed boarding house. Shared bathroom. Suitable for male students.',
    'No 12, Temple Lane, Dehiwala',
    'Dehiwala',
    10000.00,
    'live'
WHERE NOT EXISTS (
    SELECT 1 FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Budget Boarding Room - Dehiwala'
);

UPDATE listings
SET monthly_rent = 10000.00
WHERE landlord_id = @demo_landlord_id
  AND title = 'Budget Boarding Room - Dehiwala'
  AND monthly_rent = 9500.00;

SET @demo_listing_one = (
    SELECT listing_id FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Furnished Annex Room Near UoM'
    LIMIT 1
);

SET @demo_listing_two = (
    SELECT listing_id FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Shared Room for 2 - Moratuwa'
    LIMIT 1
);

SET @demo_listing_three = (
    SELECT listing_id FROM listings
    WHERE landlord_id = @demo_landlord_id
      AND title = 'Budget Boarding Room - Dehiwala'
    LIMIT 1
);

INSERT INTO rooms (
    property_id, listing_id, room_type, slot_cap, partial_occupancy,
    deposit, sq_footage, furnishing, bathroom_type, wifi, house_rules, gender_pref
)
SELECT
    @demo_property_one, @demo_listing_one, 'single', 1, 0,
    18500.00, 180, 'furnished', 'attached', 1,
    'No visitors after 9pm. No smoking. Quiet hours after 10pm.',
    'female'
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE listing_id = @demo_listing_one);

INSERT INTO rooms (
    property_id, listing_id, room_type, slot_cap, partial_occupancy,
    deposit, sq_footage, furnishing, bathroom_type, wifi, house_rules, gender_pref
)
SELECT
    @demo_property_one, @demo_listing_two, 'shared', 2, 1,
    14000.00, 240, 'semi_furnished', 'shared', 1,
    'No alcohol. Keep common areas clean. Notify landlord before guests.',
    'any'
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE listing_id = @demo_listing_two);

INSERT INTO rooms (
    property_id, listing_id, room_type, slot_cap, partial_occupancy,
    deposit, sq_footage, furnishing, bathroom_type, wifi, house_rules, gender_pref
)
SELECT
    @demo_property_two, @demo_listing_three, 'single', 1, 0,
    9500.00, 150, 'semi_furnished', 'shared', 0,
    'Male students only. No cooking in room. Gates close at 10pm.',
    'male'
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE listing_id = @demo_listing_three);

SET @demo_room_one = (SELECT room_id FROM rooms WHERE listing_id = @demo_listing_one LIMIT 1);
SET @demo_room_two = (SELECT room_id FROM rooms WHERE listing_id = @demo_listing_two LIMIT 1);
SET @demo_room_three = (SELECT room_id FROM rooms WHERE listing_id = @demo_listing_three LIMIT 1);

INSERT INTO room_slots (room_id, slot_no, status, student_id, price_type)
SELECT @demo_room_one, 1, 'available', NULL, 'full'
WHERE NOT EXISTS (SELECT 1 FROM room_slots WHERE room_id = @demo_room_one AND slot_no = 1);

INSERT INTO room_slots (room_id, slot_no, status, student_id, price_type)
SELECT @demo_room_two, 1, 'partially_occupied', @demo_student_id, 'partial'
WHERE NOT EXISTS (SELECT 1 FROM room_slots WHERE room_id = @demo_room_two AND slot_no = 1);

INSERT INTO room_slots (room_id, slot_no, status, student_id, price_type)
SELECT @demo_room_two, 2, 'available', NULL, 'partial'
WHERE NOT EXISTS (SELECT 1 FROM room_slots WHERE room_id = @demo_room_two AND slot_no = 2);

INSERT INTO room_slots (room_id, slot_no, status, student_id, price_type)
SELECT @demo_room_three, 1, 'available', NULL, 'full'
WHERE NOT EXISTS (SELECT 1 FROM room_slots WHERE room_id = @demo_room_three AND slot_no = 1);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_one,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAfF29Ivh6X67AMtPwHGTmTFrRMi3ZqaVQJVDAqc1NzKm7ufTyI4MYcHqFYf_PxXzNKx7s2FD3Jq_e3-dm1WviMoaqFuXxQq7b8wLRKiA8uGnNE8hjUQZ7o860YZO9x8Npj8Bc94XQ5sfPLAKu0RhM8Y-hANHL-OAvDYZHP284AoIVdBB-pUjw-nDlD9bIkfoTzwoFGbJzdUuysWpifI91I4w1oWR0D4SX70B7mWgCwatGm0R89by0l',
       1
WHERE NOT EXISTS (SELECT 1 FROM listing_photos WHERE listing_id = @demo_listing_one AND is_primary = 1);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_one,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuB85GwKnhLInxDGE78-UkXp59Sd5zRVbKWgDMiPFtPFZS7pO_g0MHOKPnluhVZtzweVnanhukDmSQNv14XyRa1j1MbrYtSGcFlvjenzdAFCNBjG6FqKkWrYIzF2lvJtUN9ZJ2FfrM8wgt0WleCNVIAxsbnvZWEh-1QtBtrLPhbtqQKc6U-bgv-a3OVSzL2i8fy_GQ9-eIXvkkwRy7UPLVx8By-GK7dSLh3XTYXCUC99D45zRLMWZ62A',
       0
WHERE NOT EXISTS (SELECT 1 FROM listing_photos WHERE listing_id = @demo_listing_one AND is_primary = 0);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_two,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAfF29Ivh6X67AMtPwHGTmTFrRMi3ZqaVQJVDAqc1NzKm7ufTyI4MYcHqFYf_PxXzNKx7s2FD3Jq_e3-dm1WviMoaqFuXxQq7b8wLRKiA8uGnNE8hjUQZ7o860YZO9x8Npj8Bc94XQ5sfPLAKu0RhM8Y-hANHL-OAvDYZHP284AoIVdBB-pUjw-nDlD9bIkfoTzwoFGbJzdUuysWpifI91I4w1oWR0D4SX70B7mWgCwatGm0R89by0l',
       1
WHERE NOT EXISTS (SELECT 1 FROM listing_photos WHERE listing_id = @demo_listing_two AND is_primary = 1);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_two,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAv8K2zqT_ruv2A0gtvUTFRhJI32VVI6WOrvua_BJrtXTQDLvRX3ghfyeZkbjlLsQvCRF0QGgVOaJV0fAM4oNtGy9Zp55lKPyJi_7PBE4PzmR5WI1HZjqfukVwc-TbBo23zGjPxnyJaRmyQSB_aKdy2WG0-J9d3lf4TMVSPxQA-qD3alwf788YDv-_U3s2S52Zz38CXJpqoNKgjIl1V0A2otBk5BtffY5Mt1uRtn5q31GPQoNCm8rMy',
       0
WHERE NOT EXISTS (SELECT 1 FROM listing_photos WHERE listing_id = @demo_listing_two AND is_primary = 0);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_one,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAfClAhcK7yluduYwA89akedhsaqORnGsLcpUt6AnSHL1iPMvGhS8M32qvBmY3LWElYr10y5HsZ45zDXIkefOCRXlEWbccENrlvt_qPrOeDK11oAb7ZTr4mWTd4Lab77sbwJXNlfBkxBxwrQAMvHxOFxWwL5PVbEqH32DsmIZBccN7z8rk4adCzVFJIgdpLFd1amFhQTiLA8g1XIrXydldecV8Cm-rH_vbT_VTxr33wyrlyCywtMCxA',
       0
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_one
      AND photo_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAfClAhcK7yluduYwA89akedhsaqORnGsLcpUt6AnSHL1iPMvGhS8M32qvBmY3LWElYr10y5HsZ45zDXIkefOCRXlEWbccENrlvt_qPrOeDK11oAb7ZTr4mWTd4Lab77sbwJXNlfBkxBxwrQAMvHxOFxWwL5PVbEqH32DsmIZBccN7z8rk4adCzVFJIgdpLFd1amFhQTiLA8g1XIrXydldecV8Cm-rH_vbT_VTxr33wyrlyCywtMCxA'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_one,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAv8K2zqT_ruv2A0gtvUTFRhJI32VVI6WOrvua_BJrtXTQDLvRX3ghfyeZkbjlLsQvCRF0QGgVOaJV0fAM4oNtGy9Zp55lKPyJi_7PBE4PzmR5WI1HZjqfukVwc-TbBo23zGjPxnyJaRmyQSB_aKdy2WG0-J9d3lf4TMVSPxQA-qD3alwf788YDv-_U3s2S52Zz38CXJpqoNKgjIl1V0A2otBk5BtffY5Mt1uRtn5q31GPQoNCm8rMy',
       0
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_one
      AND photo_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAv8K2zqT_ruv2A0gtvUTFRhJI32VVI6WOrvua_BJrtXTQDLvRX3ghfyeZkbjlLsQvCRF0QGgVOaJV0fAM4oNtGy9Zp55lKPyJi_7PBE4PzmR5WI1HZjqfukVwc-TbBo23zGjPxnyJaRmyQSB_aKdy2WG0-J9d3lf4TMVSPxQA-qD3alwf788YDv-_U3s2S52Zz38CXJpqoNKgjIl1V0A2otBk5BtffY5Mt1uRtn5q31GPQoNCm8rMy'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_two,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuAfClAhcK7yluduYwA89akedhsaqORnGsLcpUt6AnSHL1iPMvGhS8M32qvBmY3LWElYr10y5HsZ45zDXIkefOCRXlEWbccENrlvt_qPrOeDK11oAb7ZTr4mWTd4Lab77sbwJXNlfBkxBxwrQAMvHxOFxWwL5PVbEqH32DsmIZBccN7z8rk4adCzVFJIgdpLFd1amFhQTiLA8g1XIrXydldecV8Cm-rH_vbT_VTxr33wyrlyCywtMCxA',
       0
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_two
      AND photo_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAfClAhcK7yluduYwA89akedhsaqORnGsLcpUt6AnSHL1iPMvGhS8M32qvBmY3LWElYr10y5HsZ45zDXIkefOCRXlEWbccENrlvt_qPrOeDK11oAb7ZTr4mWTd4Lab77sbwJXNlfBkxBxwrQAMvHxOFxWwL5PVbEqH32DsmIZBccN7z8rk4adCzVFJIgdpLFd1amFhQTiLA8g1XIrXydldecV8Cm-rH_vbT_VTxr33wyrlyCywtMCxA'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_two,
       'https://lh3.googleusercontent.com/aida-public/AB6AXuB85GwKnhLInxDGE78-UkXp59Sd5zRVbKWgDMiPFtPFZS7pO_g0MHOKPnluhVZtzweVnanhukDmSQNv14XyRa1j1MbrYtSGcFlvjenzdAFCNBjG6FqKkWrYIzF2lvJtUN9ZJ2FfrM8wgt0WleCNVIAxsbnvZWEh-1QtBtrLPhbtqQKc6U-bgv-a3OVSzL2i8fy_GQ9-eIXvkkwRy7UPLVx8By-GK7dSLh3XTYXCUC99D45zRLMWZ62A',
       0
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_two
      AND photo_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuB85GwKnhLInxDGE78-UkXp59Sd5zRVbKWgDMiPFtPFZS7pO_g0MHOKPnluhVZtzweVnanhukDmSQNv14XyRa1j1MbrYtSGcFlvjenzdAFCNBjG6FqKkWrYIzF2lvJtUN9ZJ2FfrM8wgt0WleCNVIAxsbnvZWEh-1QtBtrLPhbtqQKc6U-bgv-a3OVSzL2i8fy_GQ9-eIXvkkwRy7UPLVx8By-GK7dSLh3XTYXCUC99D45zRLMWZ62A'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_three,
       '/boardnest/public/assets/uploads/homepageimage.png',
       1
WHERE NOT EXISTS (SELECT 1 FROM listing_photos WHERE listing_id = @demo_listing_three AND is_primary = 1);

-- Use the listing-specific local uploads as the primary catalogue images.
UPDATE listing_photos
SET is_primary = 0
WHERE listing_id IN (@demo_listing_one, @demo_listing_two, @demo_listing_three);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_one,
       '/boardnest/public/assets/uploads/furnished%20annex%20room%20near%20uom.avif',
       1
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_one
      AND photo_url = '/boardnest/public/assets/uploads/furnished%20annex%20room%20near%20uom.avif'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_two,
       '/boardnest/public/assets/uploads/sharedroomfor2.jpe',
       1
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_two
      AND photo_url = '/boardnest/public/assets/uploads/sharedroomfor2.jpe'
);

INSERT INTO listing_photos (listing_id, photo_url, is_primary)
SELECT @demo_listing_three,
       '/boardnest/public/assets/uploads/budegt%20boarding%20room%20dehiwala.jpg',
       1
WHERE NOT EXISTS (
    SELECT 1 FROM listing_photos
    WHERE listing_id = @demo_listing_three
      AND photo_url = '/boardnest/public/assets/uploads/budegt%20boarding%20room%20dehiwala.jpg'
);

UPDATE listing_photos
SET is_primary = CASE
    WHEN listing_id = @demo_listing_one
         AND photo_url = '/boardnest/public/assets/uploads/furnished%20annex%20room%20near%20uom.avif' THEN 1
    WHEN listing_id = @demo_listing_two
         AND photo_url = '/boardnest/public/assets/uploads/sharedroomfor2.jpe' THEN 1
    WHEN listing_id = @demo_listing_three
         AND photo_url = '/boardnest/public/assets/uploads/budegt%20boarding%20room%20dehiwala.jpg' THEN 1
    ELSE 0
END
WHERE listing_id IN (@demo_listing_one, @demo_listing_two, @demo_listing_three);

INSERT INTO reviews (listing_id, student_id, rating, review_text)
SELECT @demo_listing_one, @demo_student_id, 5,
       'Excellent room. Very clean and the landlord is very responsive. Highly recommended for students near UoM.'
WHERE NOT EXISTS (
    SELECT 1 FROM reviews
    WHERE listing_id = @demo_listing_one
      AND student_id = @demo_student_id
      AND rating = 5
);

INSERT INTO reviews (listing_id, student_id, rating, review_text)
SELECT @demo_listing_one, @demo_student_id, 4,
       'Good location and nice facilities. The attached bathroom is a big plus.'
WHERE NOT EXISTS (
    SELECT 1 FROM reviews
    WHERE listing_id = @demo_listing_one
      AND student_id = @demo_student_id
      AND rating = 4
);

INSERT INTO reviews (listing_id, student_id, rating, review_text)
SELECT @demo_listing_two, @demo_student_id, 4,
       'Great value for a shared room. The WiFi is fast enough for online lectures.'
WHERE NOT EXISTS (
    SELECT 1 FROM reviews
    WHERE listing_id = @demo_listing_two
      AND student_id = @demo_student_id
);

-- Leave saved listings and complaint history empty so CRUD demonstrations
-- can create and remove those records from the student interface.
INSERT INTO notifications (user_id, type, message, link_url, is_read)
SELECT @demo_student_user_id, 'booking_accepted',
       'Your booking request for Shared Room for 2 - Moratuwa was accepted.',
       'dashboard.php', 0
WHERE NOT EXISTS (
    SELECT 1 FROM notifications
    WHERE user_id = @demo_student_user_id AND type = 'booking_accepted'
);

INSERT INTO notifications (user_id, type, message, link_url, is_read)
SELECT @demo_student_user_id, 'account_update',
       'Your student identity is verified. Your Tier 2 badge is now visible to landlords.',
       'profile.php', 1
WHERE NOT EXISTS (
    SELECT 1 FROM notifications
    WHERE user_id = @demo_student_user_id AND type = 'account_update'
);
