1. Setup simple username/password thing
   - make router smarter -- certain routes require login, others take only certain methods, etc
    ```
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers      Authorization, X-Requested-With");
    ```
   - `Authorization` as key in header?
   - router methods
2. testing setup?
3. Frontend basic looks
   - choose dev tool (Vue? Svelte? Blergh.)
   - make basic album view
4. New album frontend hookup
5. Create new album during sort
6. Nice transitions between albums
7. Individual picture view
   - exif w/map embed
8. Splash page
