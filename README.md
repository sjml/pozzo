# Pozzo

![GitHub Workflow Status](https://img.shields.io/github/workflow/status/sjml/pozzo/automated%20backend%20tests?label=automated%20backend%20tests)
![Coveralls github](https://img.shields.io/coveralls/github/sjml/pozzo?label=test%20coverage)

Because the world needed another web photo gallery. 

Goals:
* modern web frontend that loads fast, runs smoothly, and is easy to hack on
    * good-looking photo-centric interface, with EXIF data easily available
* JSON-only API backend, as simple as possible (but no simpler)
    * runnable on cheap shared hosting
    * as solid and reliable as is feasible (note automated testing and code coverage analysis, but not fuzzing because I have my limits)
        - frontend, in contrast, is a land of dancing chaos, and that's ok

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

The PHP code is a bit slapdash. There is no ORM, so `db.php` has lots of copypasta. The router is homemade, and probably very shaky as well. 

All in all, it should run reasonably fast, though. 

And if you're curious: [Andrea Pozzo, SJ](https://en.wikipedia.org/wiki/Andrea_Pozzo)
