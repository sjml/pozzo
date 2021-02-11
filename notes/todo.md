1. Frontend pass
    - bugs
        - got some false positive error reporting on bulk upload... investigate what's up
            - more problematic was that I couldn't re-try :(
    - requires backend support
        - title/description editing for albums and pictures
        - allow setting overall site title, add pozzo branding note at bottom
        - rotate image maybe needs to be a concession to the "no editing"
        - delete album UI
        - bulk moving/deleting operations
    - some research required
        - url thinks
            - image urls that aren't predictable? 
            - remove album numberic urls?
        - blur back on image pages? (look at stackblur/canvas solution)
        - see if we can not do the blurs if the images are already loaded?
    - make it more svelte-y
        - make buttons into their own component? 
        - review reactive functions and make sure they're being used right
            - probably not
        - can photo navigation just stay on the same component and update the url? 
            - *should* be able to! that's the whole promise of this thing!
        - try and sveltify the drag and drop a little bit
    - clean up hacks
        - organize script sections of component files; good lord
        - CSS class name consistency (kebab vs camel)
        - fix chattiness of album metadata
        - redo fullscreen to actually listen for events and make sure it's worked
        - check for all console error statements and handle gracefully in UI
            - test bad responses for everything... broken file upload hangs the UI?
            - also might need to add spinners for waiting on stuff
    - UX niceties
        - link to download at original resolution
            - add license display option? (site config)
        - set map to not be pannable until it's been clicked
            - most people just wanna scroll down
        - find a nicer set of map tiles
            - also maybe SVG markers?
            - add photo preview popups?
        - add "no photos" notice to empty albums
        - album previews
        - sync up date displays for metadata (requires either pulling the uploadedBy as an integer or not typing that column as a datetime :-/)
        - add little notification toasts for when people get redirected/bounced
        - figure out an actual strategy for breadcrumbing the navbar
        - transitions between albums and stuff
        - accessibility checks
        - splash page
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
    - testing
        - play with forward/back navigation and make sure the router works the way it should
    - UI polish
        - color choices
            - centralize into global file, everyone else use variables
        - also pick a few consistent sizes for icon usages
            - rollover reactions?
            - animation of stroke-width?
        - navbar alignment is borked
        - transition selection border appearance
        - dotted line for upload: https://kovart.github.io/dashed-border-generator/
        - swap icons to phosphor
        - make sure the viewing experience on a mobile-sized screen is decent
2. Backend pass
    - better EXIF extraction (https://github.com/PHPExif/php-exif ?)
    - limit length of text things (titles, descriptions)
    - remove reset endpoint
    - make test suite
3. Frontend caching
    - can I do something clever to try and pull all the images from an album as you're viewing the full-screen?
    - something-something web workers? BLERGH.
4. Add admin endpoint (+frontend UI) to do reset and other such things?
    - maybe add setup GUI? at least command-line script
    - Config endpoint to give site name, get img sizes, etc. for sure
        - some set at read-only (can't change sizes without reimporting everything, for instance)
5. Video / live pictures?
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
* robustify backend router:
    - handlers should be able to specify which methods they take
        - creation actions should return 201
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
