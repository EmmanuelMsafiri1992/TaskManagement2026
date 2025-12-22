# Countries & Websites Management - Implementation Status

## ✅ Completed Backend Implementation

### 1. Database Schema
- ✅ Created migration `2025_10_23_154004_create_user_countries_and_websites_tables.php`
- ✅ Tables created:
  - `websites` - Store website information
  - `user_countries` - Pivot table linking users to countries
  - `user_websites` - Pivot table linking users to websites
  - Modified `adsense_reports` - Added `domain` column for website tracking

### 2. Models
- ✅ `Website` model (`app/Models/Website.php`)
- ✅ `UserCountry` model (`app/Models/UserCountry.php`)
- ✅ `User` model updated with relationships

### 3. Controller
- ✅ `SettingsCountriesController` (`app/Http/Controllers/Admin/SettingsCountriesController.php`)
  - GET `/api/settings/countries` - Get all users with their countries and websites
  - PUT `/api/settings/countries/users/{userId}/countries` - Update user's countries
  - PUT `/api/settings/countries/users/{userId}/websites` - Update user's websites
  - GET `/api/settings/countries/websites` - Get all websites
  - POST `/api/settings/countries/websites` - Create website
  - PUT `/api/settings/countries/websites/{id}` - Update website
  - DELETE `/api/settings/countries/websites/{id}` - Delete website

### 4. Routes
- ✅ Added routes in `packages/admin/routes/api.php`

## 📝 Next Steps (Frontend & Integration)

### 5. Vue Component (TODO)
Create `packages/admin/resources/js/views/settings/Countries.vue` with:
- List of all users
- For each user:
  - Multi-select for countries (with search)
  - Multi-select for websites
- Website management section:
  - Add new website form
  - Edit existing websites
  - Delete websites

### 6. Settings Menu Integration (TODO)
Add "Countries" link to settings navigation in:
- `packages/admin/resources/js/views/settings/SettingsLayout.vue`

### 7. Router Configuration (TODO)
Add route in `packages/admin/resources/js/router/index.ts`:
```javascript
{
  path: '/settings/countries',
  name: 'settings.countries',
  component: () => import('../views/settings/Countries.vue'),
}
```

### 8. AdSense Reports Integration (TODO)
Update `AdSenseReportController.php` to:
- Filter reports by logged-in user's assigned countries
- Group/filter reports by website domain
- Show user which of their assigned countries/websites are performing best

### 9. AdSense Service Update (TODO)
Update `app/Services/AdSenseService.php`:
- Modify `fetchReports()` to include domain dimension
- Parse domain data from API response

## 🚀 Quick Start for Frontend

To complete the implementation, you need to:

1. Create the Vue component for managing countries/websites
2. Add it to the settings menu
3. Update AdSense reports to filter by user's assigned countries
4. Build frontend assets: `cd packages/admin && npx vite build`

## API Endpoints Available

```
GET    /api/settings/countries                      - List users with countries & websites
PUT    /api/settings/countries/users/{id}/countries - Update user countries
PUT    /api/settings/countries/users/{id}/websites  - Update user websites
GET    /api/settings/countries/websites             - List websites
POST   /api/settings/countries/websites             - Create website
PUT    /api/settings/countries/websites/{id}        - Update website
DELETE /api/settings/countries/websites/{id}        - Delete website
```

## Database Tables

```sql
websites:
- id, name, url, domain, description, is_active

user_countries:
- id, user_id, country_code, country_name

user_websites:
- id, user_id, website_id

adsense_reports (modified):
- added: domain column
- unique key changed to: [report_date, country_code, domain]
```
