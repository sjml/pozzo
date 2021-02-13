1. Frontend pass
    - bugs
        - got some false positive error reporting on bulk upload... investigate what's up
            - more problematic was that I couldn't re-try :(
        - clicking on photo with context menu open should close context menu
        - upload to album, let photos render, then click back to main screen -- double
        - uploading to remote, never get the "processing" status
        - file uploader gets stuck behind map
        - making new album and signing out should refresh album list (does locally, but not remote? blergh.)
    - requires backend support
        - title/description editing for albums and pictures
        - album list context menu (delete / dissolve)
        - bulk moving/deleting operations
    - some research required
        - url thinks
            - image urls that aren't predictable? 
            - remove album numberic urls?
        - blur back on image pages? (look at stackblur/canvas solution)
        - see if we can not do the blurs if the images are already loaded?
        - look at video import, if it's easy
    - clean up hacks
        - redo fullscreen to actually listen for events and make sure it's worked
        - check for all console error statements and handle gracefully in UI
            - test bad responses for everything... broken file upload hangs the UI?
        - see if EditableLayout can be generalized so albumlist and album can share it
    - UX niceties
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
        - marker clusters on map (also increase padding but maybe to non-integer?)
            - maybe SVG markers?
            - add photo preview popups? or at least something when you click?
        - add license display option? (site config)
        - sync up date displays for metadata (requires either pulling the uploadedBy as an integer or not typing that column as a datetime :-/)
        - add little notification toasts for when people get redirected/bounced
        - transitions between albums and stuff
        - accessibility checks
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
    - UI polish
        - color choices
            - centralize into global file, everyone else use variables
        - style scrollbars
        - pick a font (playing with Montserrat for now since it matches the map tiles)
        - transition selection border appearance
        - make sure the viewing experience on a mobile-sized screen is decent
        - https://github.com/rikschennink/fitty
2. Backend pass
    - better EXIF extraction (https://github.com/PHPExif/php-exif ?)
        - will also pull in keywords from photos.app export, which is something that should be supported in the backend (along with stars)
        - note: once we've added a third library, it's time to properly set up composer
    - limit length of text things (titles, descriptions)
    - remove reset endpoint
    - complete test suite
3. Frontend caching
    - can I do something clever to try and pull all the images from an album as you're viewing the full-screen?
    - something-something web workers? BLERGH.
4. Video / live pictures?
    - let backend tell frontend what type of media it will accept
    - also file size upload limits, etc. (side note: can this be from code or does it have to be in php.ini?)

## future
* special handling for unsorted album? (automatically removing from it)
    - "smart" albums in general? (let's not get crazy)
    - option for copying to album instead of moving to it
        - trivial on the backend, but UX of "this photo is in multiple albums" is questionable
* investigate: https://github.com/tangrams/tangram
* share buttons? :-/
    - generate share cards
    - https://smcllns.github.io/css-social-buttons/
* testing setup
    - maybe setup automated lighthouse testing as GitHub action?
    - also check speed of import
* perf notes
    - looked at lighthouse score (now low-90s) which isn't bad considering it's mostly big images
        * set cache policy in .htaccess? (images should be easily cachable since their
          filenames *are* hashes... does the filepath become part of that?)
        * put normalize into bundle? 
        * maybe webp would be good? (would have to compile webp + imagemagick + imagick.so + maybe PHP on Dreamhost.... lotta annoyance there)
        * lighthouse tests with 85% quality and considers anything higher "optimizable"
            - not sure I want to make that tradeoff though........
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
* identify panoramas by aspect ratio and ensure they are in their own row
    - probably means chunking up the layout process, but that's not too bad
    - if last row ended with a widow, *maybe* don't want to do it? 
    - unnnnngh this would actually probably mean writing my own justified layout system which appears to be NP-hard so maybe what we got is ok
        - would still like to do better on the last row, tho
* have the blur image reload only do its fade if it took more than X ms to load the image
    - find predominant color and store that in photos table?
    - https://github.com/woltapp/blurhash
    - also, preview images are down to around ~490bytes, but that's still a lot. gotta be a way to get them smaller (plus base64 encoding adds some overhead)
        - [I made a test site to look at different compression options!](https://sjml.github.io/blur-load-test/)
        - smaller dimensions don't win much; JPEG overhead is small but meaningful at this size
        - should at least compress well when sending a bunch of them together -- first 240 characters seem to be shared, out of ~900-~1000
        - webp actually does a great job here -- can get it down to about 100 bytes at 16x16, but it's not supported in Safari pre Big Sur :(
            - detection methods like [this](https://gist.github.com/jakearchibald/6c43d5c454bc8f48f83d8471f45698fa) **also** don't work in Safari because webdev is cursed 
            - is this different? https://github.com/fregante/supports-webp/blob/master/dist/supports-webp.js 
