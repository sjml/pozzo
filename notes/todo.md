- bugs
    - when upload fails, the wrong photos get flagged for retry... this is gonna be a pain to repro/test >:(
        - looks like it has something to do with the way the filtering is happening
        - I wrote all the frontend upload stuff when I was still learning Svelte, so might be worth an overhaul
    - video breaks nav? (or maybe is just stealing keystrokes)
    - regression: cmd-A and cmd-D are selecting photos instead of text during description editing
    - splitting groups often results in wrong ordering, and/or throws an error and doesn't reload, but the split actually happened. (seems to only happen in production build *sigh*)

* todos
    - tag albums
        - link from photo metadata
            - right now it goes haywire from ordering of component destruction :-/
        - pin tag to front page (automatically unpin if no photos?)
            - consider how tags are entered/stored -- maybe should always be in slug form? 
        - add/remove tags from photo page and context menu
    - frontend UX
        - make navbar breadcrumbs collapsible (down to icons)
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
            - set the "don't leave" flag during any mutating operations
        - shift-click to select ranges
        - add an "are you sure?" prompt to deleting albums, groups(, photos?)
        - see if photomap links can use navigate instead of anchors
        - test navigation on an album with lots (100s? thousands?) of photos to see if having to search for neighbors on each perusal load kills performance
        - move photos between groups in same album
        - edit photo title
    - requires backend support
        - make guards consistently part of db response instead of frontend having to remember to filter (cf the albumlist fetch explicitly saying whether to get privates or not)
        - license configuration at site creation

0. Granularity
    - album collections
1. Frontend pass
    - clean up hacks
        - check for all console error statements and handle gracefully in UI
            - test bad responses for everything... broken file upload hangs the UI?
    - UX niceties
        - things should respond to escape (from image back to album, from album back to list, dismissing menus, etc.)
        - uploadzone should tell you when it rejects a file and why
    - share cards
2. Backend pass
    - limit length of text things (titles, descriptions, tags, etc.)
    - centralize output functions
    - cleanup use of "isset" vs "array_key_exists"
    - check on use of is_numeric vs is_integer
    - don't trust pre-filtering for params
    - see if db's copypasta can be reduced without going full ORM
    - get to 100% test coverage, just 'cause
    - reorg API 
        - buncha group functions hang off album right now
        - "error" vs "message", response codes, JSON schema, etc.
        - some names are plural, others singular; some take GET query params, others only post; a little messy all around
        - add index actions to each endpoint to explain what they can do?
            - maybe the robustified Router can automatically do that?
        - afterwards: document API
    - look at for loops in db.php and investigate if there are single queries to handle them
    - cascade delete with foreign keys instead of manual? 

## future
* generate static site? 
    - could use 11ty and read the JSON from a server, recreating images from orig
    - would allow for offline processing (webp, etc), uploading fully static to CDN
    - still needing to run dynamic: justifiedlayout, leaflet+markercluster, blurhash
* frontend extra bonus points
    - smarter font loading: https://www.zachleat.com/web/comprehensive-webfonts/
        - matcher: https://meowni.ca/font-style-matcher/

