1. Fix album view
    - calculate width, make responsive
    - experiment to see if widows are actually ending up justified
            - `{widowLayoutStyle: "justify"}`
    - identify panoramas by aspect ratio and ensure they are in their own row
        - probably means chunking up the layout process, but that's not too bad
        - if last row ended with a widow, *maybe* don't want to do it? 
    - might want to split the loads up, too... with the blur coverings, we can chunk up the actual image fetches by position on the page
        - not sure I want to do lazy-loading per se -- once you have time, load it all up, but prioritize stuff that's visible
        - so maybe chunk the loads into a priority queue, then reorder it on scroll? 
        - important to not get *too* fancy here. 
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
    - new column on albums_photos table to indicate order
9. Remove test endpoints (reset, testImport)
10. Add admin endpoint (+frontend UI) to do reset and other such things?
    - Config endpoint (read-only?) to give site name, img sizes, etc. for sure
11. Look at directory layout for images -- how to split better, etc. if there's going to be different formats eventually (and there will be) should they get their own directories or be alongside?
    - with image import -- do we have to read the image anew each time? not sure if that's the bottleneck, but I/O is a likely culprit

## future
* special handling for unsorted album? (automatically removing from it)
* testing setup
    - maybe setup automated lighthouse testing as GitHub action?
    - also check speed of import
* perf notes
    - looked at lighthouse score (mid-80s) which isn't bad considering it's mostly big images
        * set cache policy in .htaccess? (images should be easily cachable since their
          filenames *are* hashes... does the filepath become part of that?)
        * put normalize into bundle? 
        * maybe webp would be good?
        * lighthouse tests with 85% quality and considers anything higher "optimizable"
            - not sure I want to make that tradeoff though........
* robustify backend router:
    - handlers should be able to specify which methods they take
        - creation actions should return 201
    - also, gzip should be enabled at the router level, which should probably
      also have it's own output function
    - man.... maybe I should just use a library
    - might eventually want DB migrations, too, and at that point I don't know
      if I want to roll it myself..........
* sweep through API responses and make sure there's some consistency
    - "error" vs "message", response codes, JSON schema, etc.
    - add index actions to each endpoint to explain what they can do?
        - maybe the robustified Router can automatically do that?
* use webp/avif outputs with `<picture>` elements?
    - would mean retooling the image importing process to generate 2x as many outputs :-/
    - disk space is cheap, though...

## for funsies
* identify interesting parts of photos and center on them for cropping? 
    - how to avoid Twitter problem with ignoring black people
* have the blur image reload only do its fade if it took more than X ms to load the image
    - find predominant color and store that in photos table?
    - also, preview images are down to around ~490bytes, but that's still a lot. gotta be a way to get them smaller (plus base64 encoding adds some overhead)
        - [I made a test site to look at different compression options!](https://sjml.github.io/blur-load-test/)
        - smaller dimensions don't win much; JPEG overhead is small but meaningful at this size
        - should at least compress well when sending a bunch of them together -- first 240 characters seem to be shared, out of ~900-~1000
        - webp actually does a great job here -- can get it down to about 100 bytes at 16x16, but it's not supported in Safari pre Big Sur :(
            - detection methods like [this](https://gist.github.com/jakearchibald/6c43d5c454bc8f48f83d8471f45698fa) **also** don't work in Safari because webdev is cursed 
