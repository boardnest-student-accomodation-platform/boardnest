# V7 Landlord Google Maps link

Property create/edit requires a Google Maps share link rather than manual latitude/longitude. `modules/landlord/mvc/core/MapLink.php` validates the URL and derives coordinates only from explicit GPS pin strings; the existing `properties.maps_url` column stores the URL (max 500). `modules/landlord/mvc/modules/property/services/PropertyService.php` handles validation; the model inserts/updates URL and preserves/clears coordinates carefully when editing a link.

**No database ALTER needed.** Group SQL and legacy GPS fields remain unchanged. `DATABASE_UNIFIED/03_REALISTIC_FICTIONAL_MOCK_DATA.sql` and `00_ONE_STEP_FRESH_DEMO_DO_NOT_IMPORT_EXISTING.sql` now contain 18 illustrative Maps *area searches*; do not treat them as accurate location pins. Do not import demo fixture over live data. Field Agent GPS verification needs an independently confirmed coordinate if a short Maps link was supplied.

Source and QA: ZIP root `README_START_HERE.md`, `QA_REPORT_V7.md`, `Other/QA_Tools/test_map_links.php`.
