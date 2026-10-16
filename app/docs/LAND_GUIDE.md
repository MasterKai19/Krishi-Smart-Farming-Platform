# Land and expense workflows

Complete the [local setup](../../README.md#local-setup) first, then sign in and select **Land & costs**.

## Farmers

1. Add a plot with its location, area, tenure, and optional soil and irrigation details.
2. Add a crop season with dates, crop, variety, and budget.
3. Record expenses using quantity and unit price. The application calculates category totals and remaining budget.
4. Edit expenses or archive and restore plots as needed. Archived plots still count toward registered area for schemes.
5. Select a land/crop season when submitting a scheme application. The application stores a snapshot of the submitted land and costs.

Area units include katha, decimal, acre, hectare, and square feet. This application defines one katha as 720 square feet; use square feet if your local convention differs.

## Officers and administrators

Officers can inspect active farmers' land records in their registered district and review scheme applications for that district. Rejection requires a reason. Administrators manage accounts and schemes and can inspect land records.

For role demonstrations, use separate browser profiles or a private window because tabs in the same browser profile share the login session. Create your own local test accounts; no demo credentials are shipped.

## Main files

| File | Purpose |
| --- | --- |
| `land.php` | Land dashboard and expense history |
| `app/landActions.php` | Land, crop, and expense mutations |
| `app/landHelpers.php` | Access checks, conversions, and snapshots |
| `app/database/land_schema.sql` | Land tables and cost view |
| `app/applyScheme.php` | Application submission |
| `app/reviewScheme.php` | Officer decisions |
| `assets/land.js` | Calculator and search |

Personal land, contact, and financial records belong in the private runtime database, not in the repository.
