# Where to find each feature

Start at `../index.php`. It loads `lib.php`, sends POST forms to `actions.php`, then opens one page.

| Feature | Page/form | Form action file |
|---|---|---|
| Sign in and applications | `authPage.php` | `login.php`, `register.php`, `applyOfficer.php` |
| Farmer and officer dashboard | `dashboard.php` reads `dashboardData.php` | — |
| Summary numbers | `dashboardStats.php` | — |
| Appointment cards | `appointments.php` uses `appointmentCard.php` | `respondAppointment.php`, `cancelAppointment.php`, `deleteAppointment.php` |
| New appointment | `bookingForm.php` | `bookAppointment.php` |
| Notifications | `notifications.php` | `readNotifications.php` |
| Admin accounts | `adminDashboard.php` reads `adminData.php` | `approveAccount.php`, `deactivateAccount.php`, `reactivateAccount.php`, `deleteAccount.php` |

Each action file performs one job. SQL uses `query()` from `lib.php`, which prepares values safely. `database/schema.sql` defines the tables; `database/views.sql` defines the read-only SQL views.
