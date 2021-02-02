1. Deployment setup
   - copying: app, lib, public (-img), scripts
   - can probably rsync it all for now
2. Fix album view
   - calculate width, make responsive
2. Upload flow
   - login flow first
   - side note: login/check should return a fresh token
   - probably routing before everything
3. New album frontend hookup
   - while we're here, look into passing dictionaries for params to avoid so many defaults
4. Create new album during sort
5. Nice transitions between albums
6. Individual picture view
   - exif w/map embed
7. Splash page
8. Reorder album
9. Remove test endpoints (reset, testImport)
10. Add admin+UI endpoint to do reset and other such things?
   - Config endpoint (read-only?) to give site name, img sizes, etc. for sure

## future
* special handling for unsorted album? (automatically removing from it)
* testing setup
* robustify backend router:
    - handlers should be able to specify which methods they take
* sweep through API responses and make sure there's some consistency
    - "error" vs "message", response codes, JSON schema, etc.
    - add index actions to each endpoint to explain what they can do?
        - maybe the robustified Router can automatically do that?
* use webp/avif outputs with `<picture>` elements?
    - would mean retooling the image importing process to generate 2x as many outputs :-/
* 

## for funsies
* identify interesting parts of photos and center on them for cropping? 
    - how to avoid Twitter problem with ignoring black people
* have the blur image reload only do its fade if it took more than X ms to load the image
    - move preview DB storage to separate table, get via joins when needed
    - find predominant color and store that in photos table?
    - also, images are down to around ~490bytes, but that's still a lot. gotta be a way to get them smaller (plus base64 encoding adds some overhead)
        - smaller dimensions don't win much; JPEG overhead is small but meaningful at this size
        - should at least compress well when sending a bunch of them together -- first 240 characters seem to be shared, out of ~900-~1000
        - webp actually does a great job here -- can get it down to about 100 bytes at 16x16, but it's not supported in Safari pre Big Sur :(
            - detection methods like [this](https://gist.github.com/jakearchibald/6c43d5c454bc8f48f83d8471f45698fa) **also** don't work in Safari because webdev is cursed 
