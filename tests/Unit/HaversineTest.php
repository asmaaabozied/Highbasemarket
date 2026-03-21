<?php

// Office in Bahrain (reference point)
$officeLat = 26.2535193;
$officeLon = 50.5313088;

// Nearby landmark in Bahrain (e.g., City Centre Bahrain)
$landmarkLat = 26.2535428;
$landmarkLon = 50.6137103; // ~7.3 km east

// Riyadh, Saudi Arabia (Kingdom Centre)
$riyadhLat = 24.7136;
$riyadhLon = 46.6753; // ~400 km

// Jeddah, Saudi Arabia (King Abdulaziz Fountain)
$jeddahLat = 21.4858;
$jeddahLon = 39.1925; // ~1,200 km

/*
|--------------------------------------------------------------------------
| Haversine Formula Tests
|--------------------------------------------------------------------------
| These test the accuracy of the haversineDistance() function for calculating
| great-circle distances between two points on Earth using latitude/longitude.
*/

test('returns zero distance for identical coordinates', function () use ($officeLat, $officeLon) {
    // Same point → zero distance
    expect(haversineDistance($officeLat, $officeLon, $officeLat, $officeLon))->toBe(0.0);
});

test('calculates small distance between two close points in Bahrain',
    function () use ($officeLat, $officeLon, $landmarkLat, $landmarkLon) {
        $distance = haversineDistance($officeLat, $officeLon, $landmarkLat, $landmarkLon);

        // Verified using great-circle distance calculators (e.g., movable-type.co.uk)
        $expected  = 8200; // meters
        $tolerance = 150; // ±100m acceptable due to route vs straight-line

        expect($distance)
            ->toBeGreaterThanOrEqual($expected - $tolerance)
            ->toBeLessThanOrEqual($expected + $tolerance);
    })->group('geo', 'distance');

test('calculates distance from Bahrain to Riyadh accurately',
    function () use ($officeLat, $officeLon, $riyadhLat, $riyadhLon) {
        $distance = haversineDistance($officeLat, $officeLon, $riyadhLat, $riyadhLon);

        // Verified: ~422 km straight line
        expect($distance)
            ->toBeGreaterThanOrEqual(400_000)
            ->toBeLessThanOrEqual(430_000);
    })->group('geo', 'long-distance');

test('calculates distance from Bahrain to Jeddah accurately',
    function () use ($officeLat, $officeLon, $jeddahLat, $jeddahLon) {
        $distance = haversineDistance($officeLat, $officeLon, $jeddahLat, $jeddahLon);

        // Verified: ~1,252 km straight line
        expect($distance)
            ->toBeGreaterThanOrEqual(1_240_000)
            ->toBeLessThanOrEqual(1_270_000);
    })->group('geo', 'long-distance');

test('is symmetric: distance A→B equals B→A', function () use ($officeLat, $officeLon, $riyadhLat, $riyadhLon) {
    $forward  = haversineDistance($officeLat, $officeLon, $riyadhLat, $riyadhLon);
    $backward = haversineDistance($riyadhLat, $riyadhLon, $officeLat, $officeLon);

    // Allow tiny floating-point variance
    expect(abs($forward - $backward))->toBeLessThan(0.0001);
});
test('handles negative coordinates (South America example)', function () {
    $lat1 = -34.6037;
    $lon1 = -58.3816;
    $lat2 = -33.4489;
    $lon2 = -70.6693;

    $distance = haversineDistance($lat1, $lon1, $lat2, $lon2);

    // Verified: ~1,140 km straight line
    expect($distance)
        ->toBeGreaterThanOrEqual(1_120_000)
        ->toBeLessThanOrEqual(1_160_000);
});

test('handles antipodal points (nearly opposite Earth)', function () {
    $lat1 = -41.2865;
    $lon1 = 174.7762;
    $lat2 = 37.1765;
    $lon2 = -6.2025;

    $distance = haversineDistance($lat1, $lon1, $lat2, $lon2);

    // Nearly half Earth's circumference (~19.8M meters)
    expect($distance)
        ->toBeGreaterThanOrEqual(19_500_000)
        ->toBeLessThan(20_000_000);
});

test('throws no errors with extreme but valid coordinates', function () {
    $distance = haversineDistance(90, 0, -90, 0);

    // Half Earth's circumference: ~20,015 km
    expect($distance)
        ->toBeGreaterThanOrEqual(20_000_000)
        ->toBeLessThan(20_020_000);
});
