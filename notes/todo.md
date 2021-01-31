1. Deployment setup
   - copying: app, lib, public (-img), scripts
   - can probably rsync it all for now
2. Fix album view
   - calculate width, make responsive
2. Upload flow
   - login flow first
   - probably routing before everything
3. New album frontend hookup
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

## for funsies
* identify interesting parts of photos and center on them for cropping? 
    - how to avoid Twitter problem with ignoring black people
* do the cool thing where a low-res blurred version of the photo loads first
