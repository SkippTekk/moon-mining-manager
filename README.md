# Moon Mining Manager

This application manages moon-mining revenue and invoicing for EVE Online corporations and alliances.

## Requirements

* PHP 7.1+
* MySQL
* An [EVE app](https://developers.eveonline.com) with the following scopes: 
  - esi-mail.send_mail.v1 esi-universe.read_structures.v1 
  - esi-corporations.read_structures.v1 
  - esi-wallet.read_corporation_wallets.v1 
  - esi-industry.read_corporation_mining.v1

## Installation instructions

* Run `composer install` to install backend dependencies
* Run `npm install` to install frontend dependencies
* Rename the `.env.example` file to `.env` and add values for your application ID and secret, chosen prime characters 
  (must have director role within the corporation) and alliance, and whitelisted alliances/corporations
* Run `php artisan key:generate`.
* Run `php artisan migrate` to create the database tables
* Regenerate js/css with `npm run production`, if they have changed.

## Initial setup

- Add your admin user to the table `whitelist` with `is_admin` = `1`.
- Add mail templates to the table `templates`: weekly_invoice, receipt, renter_invoice, renter_notification, 
  renter_reminder.
- Login at http://your.domain/admin with a character of your corporation with the in-game roles Accountant
  and Station_Manager to create the required ESI token (that's your prime character from the configuration).

### EVE tables

You will need to import the following EVE dump tables into your database. They can be downloaded from
[Fuzzworks](https://www.fuzzwork.co.uk/dump/latest/).

* invTypes
* invTypeMaterials
* mapSolarSystems
* mapRegions

## Operation instructions

* Run `php artisan queue:work` to start the job queue process. See the 
  [Laravel documentation on Queues](https://laravel.com/docs/5.5/queues) for more information on how to use 
  Supervisor to manage job queues.
* Add a Cron for the [Task Scheduler](https://laravel.com/docs/5.5/scheduling)
* Have your primary users login to the application. They must have director roles within the corporation that owns 
  your refineries in order to retrieve citadel information.
* Manually add the primary user's ID to the `whitelist` table. They can now log in to view the application and 
  authorise any other users.

## License

This application is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
