- bugs
    - when upload fails, the wrong photos get flagged for retry... this is gonna be a pain to repro/test >:(
    - video breaks nav? (or maybe is just stealing keystrokes)

- granularity pass
    - frontend
        - make sure these affect perusal list:
            - adding/removing group description
            - rearranging groups
            - structural change
            - deleting photos
            - moving photos
            - adding photos
    - update endpoint index

dynamic albums (can't have groups, but can take metadata):
    - all
    - unsorted
    - delete groups/albums
    - tag albums
    - remove manual unsorted


* todos
    - test navigation on an album with lots (100s? thousands?) of photos to see if having to search for neighbors on each perusal load kills performance
    - re-org EXIF data display
    - darken overlay letter background or maybe add small outline; stuff still bleeds out
    - test if all the upheaval has left the editing frontend in a workable state
    - frontend UX
        - move photos between groups in same album
        - make navbar breadcrumbs collapsible (down to icons)
        - edit photo title/description
        - add/remove tags from photo page and context menu
        - add spinners for waiting on stuff (esp large image loading, long server operations, etc)
            - set the "don't leave" flag during any mutating operations
        - shift-click to select ranges
    - requires backend support
        - album list context menu (delete / dissolve)
        - groups need to be guarded against their album privacy (do with photo pass)

0. Granularity
    - album collections
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
    - reorg tests to move all group ops to their own file
    - don't trust pre-filtering for params
    - reorg API (buncha group functions hang off album right now)
        - then document API
    - see if db's copypasta can be reduced without going full ORM
    - cascade delete with foreign keys instead of manual? 
    - check on use of is_numeric vs is_integer
    - look at for loops in db.php and investigate if there are single queries to handle them
    - limit length of text things (titles, descriptions)
    - get to 100% test coverage, just 'cause
3. Frontend extra bonus points
    - smarter font loading: https://www.zachleat.com/web/comprehensive-webfonts/
        - match: https://meowni.ca/font-style-matcher/

## future
* share buttons? :-/
    - no, but generate share cards
* sweep through API responses and make sure there's some consistency
    - "error" vs "message", response codes, JSON schema, etc.
    - some names are plural, others singular; some take GET query params, others only post; a little messy all around
    - add index actions to each endpoint to explain what they can do?
        - maybe the robustified Router can automatically do that?
