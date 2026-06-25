# Training Calendar for WooCommerce

A WordPress + WooCommerce plugin for scheduling and displaying training sessions on an interactive front-end calendar. Admins add one-off or recurring sessions — with dates, start/end times, weekdays, location, and an optional linked WooCommerce product — and visitors browse them in month, week, or day views.

Built for academies, sports clubs, and coaching centers.

**License:** GPL-2.0-or-later · **Version:** 1.0.0 · **Requires:** WordPress 5.8+, WooCommerce, PHP 7.4+

---

## Live demo

In production on a US client site — **Futbol Elite Training** (Denver, CO soccer/futsal academy):
[Calendar & Locations page](https://futbolelitetraining.com/calendar-and-locations/)

> The interactive calendar on that page is rendered by this plugin. (The location lists and maps below it are the site's own static content.)

---

## What it does

The plugin registers a **Training Sessions** custom post type. From the WordPress admin, an organizer creates each session, choosing either a manual set of dates or a recurring schedule, and the plugin renders all sessions on a front-end calendar placed with a shortcode.

## Features

- **Custom post type** for managing all training sessions in one place.
- **Two scheduling modes per session:**
  - **Manual dates** — add one or more specific days (via "Add Another Day"), each with its own start and end time.
  - **Recurring** — set a start and end date, start and end time, and the weekdays (Mon–Sun) the session repeats on.
- **Location** — a free-text address field per session.
- **WooCommerce product link** — optionally associate a session with a product via a dropdown in the admin.
- **Interactive calendar** — visitors view sessions in **month, week, and day** views.
- **Shortcode** — output the calendar on any page or post.

## A note on the WooCommerce link

Each session can be linked to a WooCommerce product in the admin. Whether clicking a calendar event sends the visitor to that product is handled in the plugin code — for the live client (Futbol Elite Training), the click-through was disabled at their request, so events on that site display without a purchase link.

## Installation

1. Copy this plugin into `wp-content/plugins/training-calendar`.
2. In **Plugins**, activate **Training Calendar for WooCommerce**.
3. Make sure **WooCommerce**  & **WooCommerce Subscription** plugins are installed and active.
4. Open **Training Sessions → Add Post** to create your first session.

## Usage

**Add a session:** WP admin → **Training Sessions → Add Post** → fill in **Manual Dates** *or* **Recurring Training**, set the **Location**, optionally pick a **WooCommerce Product** → **Publish**.

**Display the calendar** with the shortcode on any page:

```
[training_calendar]
```



## How it's built

- `includes/class-cpt.php` — registers the Training Sessions custom post type and the admin meta boxes (manual dates, recurring schedule, location, WooCommerce product).
- `includes/class-calendar.php` — renders the front-end month/week/day calendar and the shortcode output.
- Standard WordPress plugin structure with an `ABSPATH` guard and `plugin_dir_*` constants.

## Author

**Muhammad Hamad** — Full-Stack & WordPress Developer
[mrhammad.com](https://mrhammad.com) · [github.com/Hamad-mirza](https://github.com/Hamad-mirza)
Developed at [Innovatek Solutions](https://innovateksol.com).
