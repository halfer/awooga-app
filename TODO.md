To-do items
===

Upgrade to PHPUnit 4.8
---

This didn't seem very stable for me on Travis, and I don't have time to fix at the moment. I tried adding screenshots in the affected test, but PhantomJS doesn't seem to want to play ball.

Upgrading to 5.x will be possible once I'm off PHP 5.5, as this is now no longer supported.

Drop separate Travis Composer file
---

I tried this, but Travis seemed to hang on the downloaded PhantomJS binary, so I have reverted back to the separate file for now.
