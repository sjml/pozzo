- bugs
    - got some false positive error reporting on bulk upload... investigate what's up
        - more problematic was that I couldn't re-try :(
    - uploading to remote, never get the "processing" status

0. Before starting actual use
    - requires backend support
        - album list context menu (delete / dissolve)
    - research needed
        - see if we can not do the blurs if the images are already loaded?
        - look at video import, if it's easy
    - UX niceties
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
        - map polish
            - add "click/tap to interact" when it's locked
            - maybe SVG markers?
            - add photo preview popups? or at least something when you click?
        - once keywords in, get metadata displayed again
        - do blur load when navigating pages (right now it's not smart enough to reset itself)
    - UI polish
        - color choices
            - centralize into global file, everyone else use variables
        - make sure the viewing experience on a mobile-sized screen is decent
        - https://github.com/rikschennink/fitty

1. Frontend pass
    - clean up hacks
        - check for all console error statements and handle gracefully in UI
            - test bad responses for everything... broken file upload hangs the UI?
    - UX niceties
        - add license display option? (site config)
        - sync up date displays for metadata (requires either pulling the uploadedBy as an integer or not typing that column as a datetime :-/)
        - add little notification toasts for when people get redirected/bounced
        - transitions between albums and stuff
        - accessibility checks
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
2. Backend pass
    - limit length of text things (titles, descriptions)
    - pagination or just trust user to break stuff up into separate albums?
    - directly fetched photos need to check if they're in a public album
        - (basically all the /photo get endpoints)
    - bulk delete
3. Frontend extra bonus points
    - can I do something clever to try and pull all the images from an album as you're viewing the full-screen?
        - something-something web workers? BLERGH.
    - smarter font loading: https://www.zachleat.com/web/comprehensive-webfonts/
        - match: https://meowni.ca/font-style-matcher/
4. Video / live pictures?

## future
* special handling for unsorted album? (automatically removing from it)
    - "smart" albums in general? (let's not get crazy)
    - option for copying to album instead of moving to it
        - trivial on the backend, but UX of "this photo is in multiple albums" is questionable
* share buttons? :-/
    - generate share cards
* testing setup
    - maybe setup automated lighthouse testing as GitHub action?
    - also check speed of import
* perf notes
    - looked at lighthouse score (now low-90s) which isn't bad considering it's mostly big images
        * set cache policy in .htaccess? (images should be easily cachable since their
          filenames *are* hashes... does the filepath become part of that?)
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
* have the blur image reload only do its fade if it took more than X ms to load the image
