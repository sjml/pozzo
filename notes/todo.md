- bugs
    - EXIF: aperture seems to not correspond to f-stop in all cameras :-/
    - reordering larger album throws miscount error >:(

immediate todos
    - requires backend support
        - album list context menu (delete / dissolve)
    - UX niceties
        - add/remove tags from photo page and context menu
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
        - shift-click to select ranges
        - map polish
            - add "click/tap to interact" when it's locked
            - maybe SVG markers?
            - add photo preview popups? or at least something when you click?
        - do blur load when navigating pages (right now it's not smart enough to reset itself)
    - UI polish
        - color choices
            - centralize into global file, everyone else use variables
        - make sure the viewing experience on a mobile-sized screen is decent
        - for album titles: https://github.com/rikschennink/fitty

0. Granularity
    - album collections
        - open question -- how to set cover photo?
    - sub-groups within albums
        - can probably just manage as interstitial descriptions that the frontend will break up
1. Frontend pass
    - clean up hacks
        - check for all console error statements and handle gracefully in UI
            - test bad responses for everything... broken file upload hangs the UI?
    - UX niceties
        - add license display option? (site config)
        - add little notification toasts for when people get redirected/bounced
        - transitions between albums and stuff
        - accessibility checks
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
        - uploadzone should tell you when it rejects a file and why
2. Backend pass
    - limit length of text things (titles, descriptions)
    - pagination or just trust user to break stuff up into separate albums?
    - directly fetched photos need to check if they're in a public album
        - (basically all the /photo get endpoints)
    - bulk delete
3. Frontend extra bonus points
    - can I do something clever to try and pull all the images from an album as you're viewing the individual pages?
        - something-something web workers? BLERGH.
        - might be able to not do the blurs if the images are already loaded, though...
    - smarter font loading: https://www.zachleat.com/web/comprehensive-webfonts/
        - match: https://meowni.ca/font-style-matcher/

## future
* special handling for unsorted album? (automatically removing from it)
    - "smart" albums in general? (let's not get crazy)
    - option for copying to album instead of moving to it
        - trivial on the backend, but UX of "this photo is in multiple albums" is questionable
* share buttons? :-/
    - no, but generate share cards
* perf notes
    - looked at lighthouse score (now low-90s) which isn't bad considering it's mostly big images
        * set cache policy in .htaccess? (images should be easily cachable since their filenames *are* hashes... does the filepath become part of that?)
* sweep through API responses and make sure there's some consistency
    - "error" vs "message", response codes, JSON schema, etc.
    - some names are plural, others singular; some take GET query params, others only post; a little messy all around
    - add index actions to each endpoint to explain what they can do?
        - maybe the robustified Router can automatically do that?
