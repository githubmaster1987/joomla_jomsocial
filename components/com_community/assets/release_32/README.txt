Requirements:
  1. Underscore must be available as global object "window._".
     - Needed by JST template and jQuery textntag plugin.

Step to import:
  1. Build 3.2 package WITHOUT minification.
  2. Copy bundle.js.
     a. Replace "und = window._" with "und = window.joms._".
     b. Replace "bbe = window.Backbone" with "bbe = window.joms.Backbone".
     c. Replace "$LAB." with "joml.$LAB.".
  3. Copy templates/jst.js if needed.
  4. Copy toolkit.js if needed, and REMOVE Underscore and Backbone from there.
  5. Copy bundle.css if needed.
  6. Add some missing legacy script to patch.js.
