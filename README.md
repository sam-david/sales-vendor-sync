# Stitch Lite
#### Sam David's Coding Challenge

## Setup
* Create DB and add credentials to .env (After speaking with Owen I actually used a postgres db for this exercise. I believe that everything should work the same if your env is config for mysql.)
* Configure rest of .env (SHOPIFY_API_KEY, SHOPIFY_PASSWORD, SHOPIFY_SECRET, VEND_CLIENT_ID, VEND_CLIENT_SECRET) or request from Sam.
* Migrate DB with `php artisan migrate`
* Seed the DB with `php artisan db:seed` (This seeds the two vendors, Shopify and Vend)
* Run server with `php artisan serve`
* Navigate to [Vend Auth](http://localhost:8000/vend) and click Authorize link. After authorizing your store you shoud be redirect and recieve a success response
* API is now ready to be accessed!
* [http://localhost:8000/api/sync](http://localhost:8000/api/sync) route will sync Shopify and Vend products and variants to local db
* [http://localhost:8000/api/products](http://localhost:8000/api/products) route will return a list of all local products and their variants
* http://localhost:8000/api/products/'id' route will return a product and it's variants

