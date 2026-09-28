<?php

function studentSearchFilters(array $input): array
{
    $allowedSorts = ['recommended', 'price_asc', 'price_desc', 'distance', 'newest'];
    $allowedRoomTypes = ['single_room', 'shared_room', 'entire_boarding', 'annex', 'studio'];

    $universities = array_values(array_filter(
        array_map('trim', (array) ($input['universities'] ?? [])),
        static fn ($value) => $value !== ''
    ));

    $roomTypes = array_values(array_intersect(
        $allowedRoomTypes,
        (array) ($input['room_types'] ?? [])
    ));

    $minPrice = filter_var($input['min_price'] ?? 10000, FILTER_VALIDATE_INT);
    $maxPrice = filter_var($input['max_price'] ?? 45000, FILTER_VALIDATE_INT);
    $groupSize = filter_var($input['group_size'] ?? 1, FILTER_VALIDATE_INT);
    $page = filter_var($input['page'] ?? 1, FILTER_VALIDATE_INT);
    $sort = (string) ($input['sort'] ?? 'recommended');

    $minPrice = $minPrice === false ? 10000 : max(0, $minPrice);
    $maxPrice = $maxPrice === false ? 45000 : max($minPrice, $maxPrice);

    return [
        'q' => trim((string) ($input['q'] ?? '')),
        'mode' => ($input['mode'] ?? 'university') === 'city' ? 'city' : 'university',
        'universities' => $universities,
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
        'room_types' => $roomTypes,
        'group_size' => $groupSize === false ? 1 : max(1, min(20, $groupSize)),
        'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'recommended',
        'page' => $page === false ? 1 : max(1, $page),
    ];
}

function studentBrowseListings(PDO $pdo, array $filters, int $perPage = 6): array
{
    $where = ["l.status = 'live'"];
    $params = [];

    if ($filters['q'] !== '') {
        $where[] = '(l.title LIKE :query OR l.address LIKE :query OR l.city LIKE :query OR p.address LIKE :query)';
        $params['query'] = '%' . $filters['q'] . '%';
    }

    if ($filters['universities']) {
        $universityCities = [
            'University of Moratuwa' => 'Moratuwa',
            'University of Colombo' => 'Colombo',
            'SLIIT Malabe' => 'Malabe',
            'NSBM Green University' => 'Homagama',
        ];
        $cities = [];
        foreach ($filters['universities'] as $university) {
            if (isset($universityCities[$university])) {
                $cities[] = $universityCities[$university];
            }
        }
        if ($cities) {
            $placeholders = [];
            foreach (array_values(array_unique($cities)) as $index => $city) {
                $key = 'university_city_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $city;
            }
            $where[] = 'p.city IN (' . implode(', ', $placeholders) . ')';
        }
    }

    if ($filters['room_types']) {
        $roomConditions = [];
        foreach ($filters['room_types'] as $roomType) {
            if ($roomType === 'single_room') {
                $roomConditions[] = "r.room_type = 'single'";
            } elseif ($roomType === 'shared_room') {
                $roomConditions[] = "r.room_type = 'shared'";
            } elseif ($roomType === 'annex') {
                $roomConditions[] = "p.structural_type = 'annex'";
            } elseif ($roomType === 'entire_boarding') {
                $roomConditions[] = "p.structural_type IN ('house', 'apartment', 'boarding_house')";
            }
        }
        if ($roomConditions) {
            $where[] = '(' . implode(' OR ', $roomConditions) . ')';
        }
    }

    $where[] = 'l.monthly_rent BETWEEN :min_price AND :max_price';
    $where[] = 'r.slot_cap >= :group_size';
    $params['min_price'] = $filters['min_price'];
    $params['max_price'] = $filters['max_price'];
    $params['group_size'] = $filters['group_size'];

    $whereSql = implode(' AND ', $where);
    $sortSql = [
        'recommended' => 'rating DESC, l.created_at DESC',
        'price_asc' => 'l.monthly_rent ASC, rating DESC',
        'price_desc' => 'l.monthly_rent DESC, rating DESC',
        'distance' => 'l.city ASC, l.address ASC',
        'newest' => 'l.created_at DESC, l.listing_id DESC',
    ][$filters['sort']];

    $countStatement = $pdo->prepare("
        SELECT COUNT(DISTINCT l.listing_id)
        FROM listings l
        INNER JOIN rooms r ON r.listing_id = l.listing_id
        INNER JOIN properties p ON p.property_id = r.property_id
        WHERE $whereSql
    ");
    $countStatement->execute($params);
    $total = (int) $countStatement->fetchColumn();
    $pageCount = max(1, (int) ceil($total / $perPage));
    $page = min($filters['page'], $pageCount);
    $offset = ($page - 1) * $perPage;

    $sql = "
        SELECT
            l.*,
            u.full_name AS landlord_name,
            p.structural_type,
            p.latitude,
            p.longitude,
            p.maps_url,
            CASE
                WHEN p.city = 'Moratuwa' THEN 'University of Moratuwa'
                WHEN p.city = 'Colombo' THEN 'University of Colombo'
                WHEN p.city = 'Malabe' THEN 'SLIIT Malabe'
                WHEN p.city = 'Homagama' THEN 'NSBM Green University'
                ELSE p.city
            END AS university,
            CASE WHEN r.room_type = 'single' THEN 'single_room' ELSE 'shared_room' END AS room_type,
            r.gender_pref AS gender_preference,
            r.slot_cap AS capacity,
            CASE WHEN r.wifi = 1 THEN 'WiFi Included' ELSE 'No WiFi' END AS wifi_label,
            CONCAT(UPPER(LEFT(r.bathroom_type, 1)), SUBSTRING(r.bathroom_type, 2), ' Bathroom') AS bathroom_label,
            COALESCE(MAX(CASE WHEN lp.is_primary = 1 THEN lp.photo_url END), MAX(lp.photo_url), '') AS image_url,
            COALESCE(AVG(rv.rating), 0) AS rating,
            COUNT(DISTINCT CASE WHEN rs.status = 'available' THEN rs.slot_id END) AS beds_available,
            1 AS is_field_verified,
            0 AS distance_m
        FROM listings l
        INNER JOIN rooms r ON r.listing_id = l.listing_id
        INNER JOIN properties p ON p.property_id = r.property_id
        INNER JOIN landlords ld ON ld.landlord_id = l.landlord_id
        INNER JOIN users u ON u.user_id = ld.user_id
        LEFT JOIN listing_photos lp ON lp.listing_id = l.listing_id
        LEFT JOIN reviews rv ON rv.listing_id = l.listing_id
        LEFT JOIN room_slots rs ON rs.room_id = r.room_id
        WHERE $whereSql
        GROUP BY l.listing_id, r.room_id, p.property_id, u.user_id
        ORDER BY $sortSql
        LIMIT :limit OFFSET :offset
    ";

    $statement = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'page' => $page,
        'page_count' => $pageCount,
    ];
}

function studentBrowseUniversities(PDO $pdo): array
{
    $statement = $pdo->query("SELECT DISTINCT city FROM properties ORDER BY city");
    $cities = $statement->fetchAll(PDO::FETCH_COLUMN);
    $universityByCity = [
        'Moratuwa' => 'University of Moratuwa',
        'Colombo' => 'University of Colombo',
        'Malabe' => 'SLIIT Malabe',
        'Homagama' => 'NSBM Green University',
    ];

    return array_values(array_filter(array_map(
        static fn ($city) => $universityByCity[$city] ?? null,
        $cities
    )));
}

function studentSavedListingIds(PDO $pdo, ?int $userId): array
{
    if (!$userId) {
        return [];
    }

    $statement = $pdo->prepare('
        SELECT sl.listing_id
        FROM saved_listings sl
        INNER JOIN students s ON s.student_id = sl.student_id
        WHERE s.user_id = ?
    ');
    $statement->execute([$userId]);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

function studentFindListing(PDO $pdo, int $listingId): ?array
{
    $detail = getListingById($pdo, $listingId);
    return $detail['listing'] ?? null;
}

function studentRoomTypeLabel(string $roomType): string
{
    return [
        'single' => 'Single Room',
        'shared' => 'Shared Room',
        'single_room' => 'Single Room',
        'shared_room' => 'Shared Room',
        'entire_boarding' => 'Entire Boarding',
        'annex' => 'Annex',
        'studio' => 'Studio',
    ][$roomType] ?? ucwords(str_replace('_', ' ', $roomType));
}

function studentGenderLabel(string $gender): string
{
    return [
        'male' => 'Male Only',
        'female' => 'Female Only',
        'any' => 'Any Student',
    ][$gender] ?? 'Any Student';
}

function studentDistanceLabel(int $distanceMetres): string
{
    if ($distanceMetres < 1000) {
        return $distanceMetres . ' m';
    }

    return rtrim(rtrim(number_format($distanceMetres / 1000, 1), '0'), '.') . ' km';
}

function getListingById(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare("
        SELECT
            l.*,
            r.room_id,
            r.room_type,
            r.slot_cap,
            r.partial_occupancy,
            r.deposit,
            r.sq_footage,
            r.furnishing,
            r.bathroom_type,
            r.wifi,
            r.house_rules,
            r.gender_pref,
            p.property_id,
            p.maps_url,
            p.shared_facilities,
            p.structural_type,
            p.latitude,
            p.longitude,
            ld.mobile AS landlord_mobile,
            lu.full_name AS landlord_name,
            (
                SELECT COUNT(*)
                FROM room_slots available_slots
                WHERE available_slots.room_id = r.room_id
                  AND available_slots.status = 'available'
            ) AS available_slots
        FROM listings l
        INNER JOIN rooms r ON r.listing_id = l.listing_id
        INNER JOIN properties p ON r.property_id = p.property_id
        INNER JOIN landlords ld ON l.landlord_id = ld.landlord_id
        INNER JOIN users lu ON ld.user_id = lu.user_id
        WHERE l.listing_id = :id
          AND l.status = 'live'
        LIMIT 1
    ");
    $statement->execute(['id' => $id]);
    $listing = $statement->fetch();

    if (!$listing) {
        return null;
    }

    $photoStatement = $pdo->prepare("
        SELECT photo_id, listing_id, photo_url, is_primary, uploaded_at
        FROM listing_photos
        WHERE listing_id = :id
        ORDER BY is_primary DESC, photo_id ASC
    ");
    $photoStatement->execute(['id' => $id]);

    $reviewStatement = $pdo->prepare("
        SELECT rv.*, u.full_name AS reviewer_name
        FROM reviews rv
        INNER JOIN students s ON rv.student_id = s.student_id
        INNER JOIN users u ON s.user_id = u.user_id
        WHERE rv.listing_id = :id
        ORDER BY rv.created_at DESC
    ");
    $reviewStatement->execute(['id' => $id]);

    $ratingStatement = $pdo->prepare("
        SELECT AVG(rating) AS avg_rating, COUNT(review_id) AS review_count
        FROM reviews
        WHERE listing_id = :id
    ");
    $ratingStatement->execute(['id' => $id]);
    $rating = $ratingStatement->fetch();

    $occupant = null;
    if ($listing['room_type'] === 'shared' && (int) $listing['partial_occupancy'] === 1) {
        $occupantStatement = $pdo->prepare("
            SELECT u.full_name, s.university, s.academic_year
            FROM room_slots rs
            INNER JOIN students s ON rs.student_id = s.student_id
            INNER JOIN users u ON s.user_id = u.user_id
            WHERE rs.room_id = :room_id
              AND rs.status = 'partially_occupied'
            LIMIT 1
        ");
        $occupantStatement->execute(['room_id' => $listing['room_id']]);
        $occupant = $occupantStatement->fetch() ?: null;
    }

    return [
        'listing' => $listing,
        'photos' => $photoStatement->fetchAll(),
        'reviews' => $reviewStatement->fetchAll(),
        'avg_rating' => $rating['avg_rating'] !== null ? (float) $rating['avg_rating'] : null,
        'review_count' => (int) $rating['review_count'],
        'occupant' => $occupant,
    ];
}
