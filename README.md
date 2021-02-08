# Pozzo

Because the world needed another web photo gallery. 

Goals:
* modern web frontend that loads fast, runs smoothly, and is easy to hack on
    * good-looking photo-centric interface, with EXIF data easily available
* JSON-only API backend, as simple as possible (but no simpler)
    * runnable on cheap shared hosting

Non-goals:
* Editing photos beyond giving titles, tags, and descriptions
* Upload/edit interface from mobile
    - viewing should be great on mobile, but any actions that require login are not prioritized, if indeed they function at all
* Multi-user collaboration
* Real-time displays (album gets updated elsewhere and it's pushed to your screen)
* Handling immense numbers of photos or albums
    - efficiency is definitely a goal, handling UI for these cases is not

I'm imagining this as a single-to-few-users-per-installation kind of thing. Not replacing Flickr, but just something to easily have your own personal gallery, maybe shared with a few folks. 

The backend is in PHP because it's easy and cheap to deploy almost anywhere. 

It only supports SQLite, also because it's easy and cheap. Until performance issues arise, I'll stick with it. 

The PHP code is very slapdash. There is no ORM, so `db.php` has lots of copypasta. The router is homemade, and probably very shaky as well. At some point I may overcome my mental hurdle of using other libraries... I already did for the JWT stuff, at least. 

All in all, it should run reasonably fast, though. 

Secure? Need to do a pass on that kind of thing. 
