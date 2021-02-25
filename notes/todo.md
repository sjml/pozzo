- bugs
    - when upload fails, the wrong photos get flagged for retry... this is gonna be a pain to repro/test >:(
    - video breaks nav? (or maybe is just stealing keystrokes)
    
* immediate todos
    - play with code-splitting; figure out how to combine it with a router such that we don't get all the sliding around that's currently happening
    - re-org EXIF data display
    - test if all the upheaval has left the editing frontend in a workable state
    - UX niceties
        - edit photo title/description
        - add/remove tags from photo page and context menu
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
            - set the "don't leave" flag during any mutating operations
        - shift-click to select ranges
        - bulk delete
    - requires backend support
        - album list context menu (delete / dissolve)

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
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
        - uploadzone should tell you when it rejects a file and why
2. Backend pass
    - limit length of text things (titles, descriptions)
    - directly fetched photos need to check if they're in a public album
        - (basically all the /photo get endpoints)
    - get to 100% test coverage, just 'cause
3. Frontend extra bonus points
    - can I do something clever to try and pull all the images from an album as you're viewing the individual pages?
        - something-something web workers? BLERGH.
        - might be able to not do the blurs if the images are already loaded, though...
    - smarter font loading: https://www.zachleat.com/web/comprehensive-webfonts/
        - match: https://meowni.ca/font-style-matcher/

## future
* special handling for unsorted album? (automatically removing from it)
    - "smart" albums in general? (just unsorted and tags)
    - option for copying to album instead of moving to it
        - trivial on the backend, but UX of "this photo is in multiple albums" is questionable
* share buttons? :-/
    - no, but generate share cards
* sweep through API responses and make sure there's some consistency
    - "error" vs "message", response codes, JSON schema, etc.
    - some names are plural, others singular; some take GET query params, others only post; a little messy all around
    - add index actions to each endpoint to explain what they can do?
        - maybe the robustified Router can automatically do that?
