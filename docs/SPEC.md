# AGREDA PHASE III — Court Booking System

> Build spec for Claude Code. Stack: **Laravel 13 · Inertia + React (TS) · shadcn/ui · MySQL**.
> Read this file fully before starting. Build in the phases at the bottom — one phase per session, commit after each.

---

## 0 · Context & rules

- **One physical court, four sports**: Basketball, Volleyball, Futsal, Pickleball. Lines/equipment are adjusted per booking, so a booked slot is busy for **all** sports.
- **Booking = a request, not instant.** Visitor picks sport → date → time slot → submits. Status starts `pending`; the owner confirms/declines in the admin panel.
- **Payment is in person.** No online payment gateway. `total_price` is stored for reference only.
- **Notifications**: a new request fires (a) an in-app admin alert and (b) an automatic Facebook message to the owner — via a **queued job** so the visitor's submit isn't blocked.
- **Guests don't need accounts.** Bookings store `guest_name`/`guest_phone`. `user_id` is nullable, used only for the admin/owner login.
- Brand accent color: `#ef5b2b` (set as the shadcn `--primary` token).

---

## 1 · Database schema (migrations)

Generate one migration per table, in this order (FK dependencies).

### sports
| column | type |
|---|---|
| id | id() — PK |
| name | string |
| slug | string, unique |
| icon | string, nullable |
| rate_offpeak | decimal(8,2) |
| rate_peak | decimal(8,2) |
| is_active | boolean, default true |

### courts
| column | type |
|---|---|
| id | id() — PK |
| name | string |
| description | text, nullable |
| location | string, nullable |
| is_active | boolean, default true |

Single row to start; modeled as a table so a 2nd venue can be added later.

### court_sport (pivot)
| column | type |
|---|---|
| id | id() — PK |
| court_id | foreignId → courts, cascade |
| sport_id | foreignId → sports, cascade |

Add `$table->unique(['court_id','sport_id'])`. Which sports a court supports.

### bookings (core table)
| column | type |
|---|---|
| id | id() — PK |
| user_id | foreignId, nullable → users |
| court_id | foreignId → courts |
| sport_id | foreignId → sports |
| guest_name | string |
| guest_phone | string |
| booking_date | date |
| start_time | time |
| end_time | time |
| status | enum: pending · confirmed · declined · cancelled · completed |
| total_price | decimal(8,2), nullable |
| notes | text, nullable |

Index `['court_id','booking_date','status']` for fast availability lookups.

### operating_hours
| column | type |
|---|---|
| id | id() — PK |
| court_id | foreignId → courts |
| day_of_week | tinyint (0=Sun … 6=Sat) |
| open_time | time |
| close_time | time |

Drives which time slots are generated per weekday.

### court_closures
| column | type |
|---|---|
| id | id() — PK |
| court_id | foreignId → courts |
| date | date |
| reason | string, nullable |

Holidays / maintenance — blocks the whole day.

### booking_notifications
> Named `booking_notifications` to avoid clashing with Laravel's built-in notifications system.

| column | type |
|---|---|
| id | id() — PK |
| booking_id | foreignId → bookings, cascade |
| channel | enum: facebook · admin · email |
| status | enum: queued · sent · failed |
| sent_at | timestamp, nullable |

### CMS tables (admin-managed, no booking FKs)
- **carousel_slides**: id · title · image_path · caption · link_url · sort_order · is_visible
- **events**: id · title · description · image_path · event_date · is_featured
- **faqs**: id · category · question · answer · sort_order
- **pages**: id · slug · title · body · updated_at
- **settings**: id · key · value · group  *(phone, fb page, map, hours)*

---

## 2 · Models & relationships

| Model | Relationships |
|---|---|
| Sport | hasMany(Booking) · belongsToMany(Court) |
| Court | hasMany(Booking) · belongsToMany(Sport) · hasMany(OperatingHour) · hasMany(CourtClosure) |
| Booking | belongsTo(Court) · belongsTo(Sport) · belongsTo(User) · hasMany(BookingNotification) |
| OperatingHour | belongsTo(Court) |
| CourtClosure | belongsTo(Court) |
| BookingNotification | belongsTo(Booking) |
| User | hasMany(Booking) — add `role` enum (admin·owner·staff) |

Casts: `booking_date → date`, `start_time`/`end_time → 'H:i'`, enums → backed PHP enums (`BookingStatus`, `NotificationChannel`, `NotificationStatus`).

---

## 3 · Routes

### Public — `routes/web.php` (Inertia)
```
GET   /                      HomeController@index         → Home
GET   /about                 PageController@show('about') → About
GET   /facilities            FacilityController@index     → Facilities
GET   /pricing               PricingController@index      → Pricing
GET   /faqs                  FaqController@index          → Faqs
GET   /contact               ContactController@show       → Contact
GET   /book                  BookingController@create     → Booking
GET   /api/availability      BookingController@slots      (sport_id, date) → JSON
POST  /book                  BookingController@store      → request submitted
GET   /book/{booking}/done   BookingController@confirm    → Confirmation
```

### Admin — auth + role middleware, prefix `/admin`
```
GET    /admin                      Admin\DashboardController
GET    /admin/bookings             Admin\BookingController@index
PATCH  /admin/bookings/{b}/confirm @confirm   → notify requester
PATCH  /admin/bookings/{b}/decline @decline   → free the slot
GET    /admin/calendar             Admin\CalendarController
resource /admin/sports /admin/slides /admin/events /admin/faqs /admin/pages
GET    /admin/settings             Admin\SettingController@edit
PUT    /admin/settings             Admin\SettingController@update
```

---

## 4 · Availability logic (`AvailabilityService`)

`GET /api/availability?date=&sport_id=` returns each slot's state for the booking grid.

1. Look up `operating_hours` for that weekday → generate hourly slots (open→close).
2. If a `court_closures` row exists for the date → whole day unavailable.
3. Load `bookings` for that court + date where status ∈ {pending, confirmed}. Mark overlapping slots **booked** (confirmed) or **pending**. Sport-agnostic — one court, so any sport's booking blocks the slot.
4. Past slots (today, before now) → disabled.
5. Everything else → **free**.

**⚠ Race condition:** on `POST /book`, re-check the slot inside a DB transaction with `lockForUpdate()` before inserting — two people can submit the same slot seconds apart.

---

## 5 · Owner notification (queued job)

After `$booking->save()`:
```php
NotifyOwnerOfBooking::dispatch($booking);
```
```php
// app/Jobs/NotifyOwnerOfBooking.php (implements ShouldQueue)
public function handle(FacebookService $fb): void {
    $fb->sendOwnerMessage(
        "New booking request: {$this->booking->sport->name} on "
        . "{$this->booking->booking_date->format('M j')} "
        . "{$this->booking->start_time}. From {$this->booking->guest_name}."
    );
    $this->booking->notifications()->create([
        'channel' => 'facebook', 'status' => 'sent', 'sent_at' => now(),
    ]);
}
```
- **Facebook delivery**: use the Messenger Send API with a Page access token (or post to a page/group via Graph API for v1). Wrap in `FacebookService` so the channel can be swapped. **Stub it first** (just log) — wire the real token later.
- **Laravel 13**: centralise with `Queue::route(NotifyOwnerOfBooking::class, queue: 'notifications')`; add `#[Tries(3)]` + `#[Backoff]` so a flaky FB API retries.
- **Admin alert** is immediate — a `pending` count badge, no job needed.
- **Confirm/decline** notifies the requester back and writes a `booking_notifications` row.
- Note: `.env` already has `QUEUE_CONNECTION=database` — run `php artisan queue:table` + migrate, then `php artisan queue:work`.

---

## 6 · Frontend — pages & components

Inertia pages in `resources/js/pages/`; shadcn UI in `resources/js/components/ui/`.

| Page | Key components / shadcn |
|---|---|
| Home | `Carousel` hero · sport chips · featured `Card` grid · quick-check bar |
| Booking | sport `ToggleGroup` · `Calendar` · slot list · details `Form` · stepper |
| Confirmation | success `Card` · summary rows · "payment in person" `Alert` |
| Pricing | `Table` (sport × off-peak/peak) |
| Faqs | `Accordion` |
| Contact | Google Maps embed · info list · message `Form` |
| Admin/* | sidebar layout · stat tiles · bookings `DataTable` · `Dialog` · `Tabs` · drag-sort `Card` list for slides |

Shared: `PublicLayout` (navbar + footer) and `AdminLayout` (starter kit ships one).

---

## 7 · Build order (one phase per session — commit after each)

1. **Commit clean starter.** Confirm `php -v` ≥ 8.3, pick one package manager (pnpm per `pnpm-workspace.yaml`).
2. **Migrations + models + enums + seeders** (1 court, 4 sports, operating hours, sample rates).
3. **AvailabilityService + `/api/availability`**, tested against seeded data.
4. **Booking flow** — page, store, confirmation, race-condition guard.
5. **Admin bookings** — table, confirm/decline, dashboard counts.
6. **Queued notification job** + `FacebookService` (stub first, real token later).
7. **Public pages + CMS** — Home/carousel, About, Facilities, Pricing, FAQ, Contact + their admin editors.
