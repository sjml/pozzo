1. Frontend basic looks
   - make basic album view
2. Deployment setup
   - copying: app, lib, public (-img), scripts
   - can probably rsync it all for now
3. Upload flow
   - login flow first
4. New album frontend hookup
5. Create new album during sort
6. Nice transitions between albums
7. Individual picture view
   - exif w/map embed
8. Splash page
9. Remove test endpoints (reset, testImport)
10. Add admin+UI endpoint to do reset and other such things?
   - Config endpoint (read-only?) to give site name, img sizes, etc. for sure

## future
* special handling for unsorted album? (automatically removing from it)
* testing setup
* robustify router:
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
