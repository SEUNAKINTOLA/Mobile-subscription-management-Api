MOBILE APPLICATION SUBSCRIPTION MANAGEMENT API / CALLBACK / WORKER from iOS or Android App

• One device can only have one subscription for one app.
• One device can have multiple subscriptions as long as they are in different apps.
• Every app has different iOS and Google credentials. (When sending requests to the mocking
API, you can use username:password in the header.)
• The DB schema is in sql file format.
• DB tables, especially the device table, can run with millions of records.

#### Running Application
Run the following commands
``php artisan migrate``

``php artisan serve``

#### API DOCUMENTATION
Api documentation available on http://127.0.0.1:8000/api/documentation
