<wizard-report>
# PostHog post-wizard report

The wizard has completed a deep integration of PostHog analytics into the CMOS-HimatekkomITS Laravel application. The following changes were made:

- **Installed** `posthog/posthog-php` via Composer
- **Created** `config/posthog.php` — reads `POSTHOG_PROJECT_TOKEN`, `POSTHOG_HOST`, and `POSTHOG_DISABLED` from environment variables
- **Updated** `app/Providers/AppServiceProvider.php` — initializes PostHog on boot using config values
- **Created** `app/Services/PostHogService.php` — a thin service wrapper around `PostHog::capture` and `PostHog::identify` with `disabled` guard
- **Instrumented** 5 controllers with 11 events (see table below)
- **Added** user identification on login (calls `PostHog::identify` with name, email, role, department)

| Event | Description | File |
|---|---|---|
| `user_logged_in` | User successfully authenticates | `app/Http/Controllers/AuthController.php` |
| `user_logged_out` | User explicitly logs out | `app/Http/Controllers/AuthController.php` |
| `announcement_created` | New announcement posted (with/without poll) | `app/Http/Controllers/AnnouncementController.php` |
| `announcement_reacted` | Reaction added, changed, or removed on announcement | `app/Http/Controllers/AnnouncementController.php` |
| `poll_voted` | User casts vote on an announcement poll | `app/Http/Controllers/AnnouncementController.php` |
| `program_created` | New program kerja created | `app/Http/Controllers/ProgramController.php` |
| `program_updated` | Program kerja updated (tracks status transitions) | `app/Http/Controllers/ProgramController.php` |
| `evaluation_submitted` | Staff evaluation submitted by kabinet/BPH | `app/Http/Controllers/EvaluationController.php` |
| `evaluation_updated` | Existing staff evaluation revised | `app/Http/Controllers/EvaluationController.php` |
| `user_created` | Admin creates a new user account manually | `app/Http/Controllers/UserController.php` |
| `users_imported` | Admin bulk-imports users via CSV (tracks success/error counts) | `app/Http/Controllers/UserController.php` |

## Next steps

To view your events in PostHog, visit your project and navigate to **Product Analytics → Events** to confirm events are arriving. Suggested insights to build:

- **Login volume over time** — trend on `user_logged_in`
- **Active users funnel** — `user_logged_in` → `announcement_created` / `poll_voted`
- **Evaluation completion rate** — `evaluation_submitted` by `evaluator_type`
- **Program lifecycle** — `program_created` → `program_updated` where `status = completed`
- **User import success rate** — `users_imported` with `success_count` vs `error_count`

### Agent skill

We've left an agent skill folder in your project at `.claude/skills/integration-laravel/`. You can use this context for further agent development when using Claude Code. This will help ensure the model provides the most up-to-date approaches for integrating PostHog.

</wizard-report>
