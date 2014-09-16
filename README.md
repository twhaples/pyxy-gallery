# pyxy-gallery

Once upon a time, I had a bunch of .jpg files from a trip and a cheap
PHP-centric web hosting solution called Dreamhost. I thought it would
be nice to just put them up in a gallery. But I didn't feel like messing
around with some big fancy custom gallery software with its own database
of users and captions and nonsense. I just wanted to drop a page in the
gallery and use the magic of PHP to show all the images. Unfortunately,
most of the extant software which did that was absolutely terrible.

So I wrote this. It's still terrible, but not absolutely terrible, and
it at least makes use of basic HTML features like Expires: headers to
avoid overloading the server. The key feature was that installation
is very simple (one .php file) which did place a lower bound on
how terrible the software must be :)

Then someone saw what I was making and thought it was SO COOL and
it got on the front page of the programming portion of Digg or the
like and everyone was like "YAY AWESOME!!!" even though I was like
"wait I'm not sure this is actually 100% ready AARGH OMG DIGG EFFECT".
Next thing you know I had people in southeast Asia who didn't know
what they were doing not just installing it but also copying my
Google Analytics identifier tag from the demo gallery for no good
reason whatsoever. Exciting times.

Later, I actually got employed and learned how to develop software
for real. :P

## cool story bro

It's 2014. Moo.js is no longer the state of the art. PHP actually
is starting to quasi-resemble a real programming language these
days (still waiting on the lexical scope though). I'm reorganizing
and putting this sort of stuff under version control here on Github
just in case anyone wants it in the future. Some day I might actually
refactor this with a saner build process (instead of just editing
everything in the one file) and modern JavaScript and CSS and such.
Don't hold your breath, and don't expect support. :)

