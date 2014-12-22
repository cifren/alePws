/**
 * @preserve jquery.fullscreen 1.1.4
 * https://github.com/kayahr/jquery-fullscreen-plugin
 * Copyright (C) 2012 Klaus Reimer <k@ailis.de>
 * Licensed under the MIT license
 * (See http://www.opensource.org/licenses/mit-license)
 */

(function() {

  /**
   * Sets or gets the fullscreen state.
   *
   * @param {boolean=} state
   *            True to enable fullscreen mode, false to disable it. If not
   *            specified then the current fullscreen state is returned.
   * @return {boolean|Element|jQuery|null}
   *            When querying the fullscreen state then the current fullscreen
   *            element (or true if browser doesn't support it) is returned
   *            when browser is currently in full screen mode. False is returned
   *            if browser is not in full screen mode. Null is returned if
   *            browser doesn't support fullscreen mode at all. When setting
   *            the fullscreen state then the current jQuery selection is
   *            returned for chaining.
   * @this {jQuery}
   */

  function fullScreen(state) {
    var e, func, doc;

    // Do nothing when nothing was selected
    if (!this.length) return this;

    e = ( /** @type {Element} */ this[0]);
    doc = getDocument(this);

    // When no state was specified then return the current state.
    if (state == null) {
      return isFullScreen(this);
    }

    // When state was specified then enter or exit fullscreen mode.
    if (state) {
      // Enter fullscreen
      func = (e["requestFullScreen"]) || (e["webkitRequestFullScreen"]) || (e["mozRequestFullScreen"]);
      if (func) {
        if (Element["ALLOW_KEYBOARD_INPUT"]) {
          func.call(e, Element["ALLOW_KEYBOARD_INPUT"]);
          // http://stackoverflow.com/questions/8427413/webkitrequestfullscreen-fails-when-passing-element-allow-keyboard-input-in-safar
        }
        if (!isFullScreen(this)) {
          func.call(e);
        }
      }
      return this;
    } else {
      // Exit fullscreen
      func = (doc["cancelFullScreen"]) || (doc["webkitCancelFullScreen"]) || (doc["mozCancelFullScreen"]);
      if (func) func.call(doc);
      return this;
    }
  }

  /**
   * Browser independent fullscreen state getter
   *
   * @param object element
   *
   * @param { bool }
   */

  function isFullScreen(element) {
    var doc = getDocument(element);

    // When fullscreen mode is not supported then return null
    if (!((doc["cancelFullScreen"]) || (doc["webkitCancelFullScreen"]) || (doc["mozCancelFullScreen"]))) {
      return null;
    }
    var element = (doc.fullscreenElement || doc.mozFullScreenElement || doc.webkitFullscreenElement);

    return element ? element : false;
  }

  /**
   * Browser independent document getter
   *
   * @param object element
   */

  function getDocument(element) {
    // We only use the first selected element because it doesn't make sense
    // to fullscreen multiple elements.
    e = ( /** @type {Element} */ element[0]);

    // Find the real element and the document (Depends on whether the
    // document itself or a HTML element was selected)
    if (e.ownerDocument) {
      doc = e.ownerDocument;
    } else {
      doc = e;
      e = doc.documentElement;
    }

    return doc;
  }

  /**
   * Toggles the fullscreen mode.
   *
   * @return {!jQuery}
   *            The jQuery selection for chaining.
   * @this {jQuery}
   */

  function toggleFullScreen() {
    return ( /** @type {!jQuery} */ fullScreen.call(this, !fullScreen.call(this)));
  }

  /**
   * Determine if the browser supports fullscreen mode
   */

  function fullScreenSupported() {
    if (typeof document.cancelFullScreen != 'undefined') {
      return true;
    } else {
      var browserPrefixes = 'webkit moz o ms khtml'.split(' ');
      // check for fullscreen support by vendor prefix
      for (var i = 0, il = browserPrefixes.length; i < il; i++) {
        prefix = browserPrefixes[i];
        if (typeof document[prefix + 'CancelFullScreen'] != 'undefined') {
          return true;
          break;
        }
      }
    }
  };

  /**
   * Handles the browser-specific fullscreenchange event and triggers
   * a jquery event for it.
   *
   * @param {?Event} event
   *            The fullscreenchange event.
   */

  function fullScreenChangeHandler(event) {
    jQuery(document).trigger(new jQuery.Event("fullscreenchange"));
  }

  /**
   * Handles the browser-specific fullscreenerror event and triggers
   * a jquery event for it.
   *
   * @param {?Event} event
   *            The fullscreenerror event.
   */

  function fullScreenErrorHandler(event) {
    jQuery(document).trigger(new jQuery.Event("fullscreenerror"));
  }

  /**
   * Installs the fullscreenchange event handler.
   */

  function installFullScreenHandlers() {
    var e, change, error;

    // Determine event name
    e = document;
    if (e["webkitCancelFullScreen"]) {
      change = "webkitfullscreenchange";
      error = "webkitfullscreenerror";
    } else if (e["mozCancelFullScreen"]) {
      change = "mozfullscreenchange";
      error = "mozfullscreenerror";
    } else {
      change = "fullscreenchange";
      error = "fullscreenerror";
    }

    // Install the event handlers
    jQuery(document).bind(change, fullScreenChangeHandler);
    jQuery(document).bind(error, fullScreenErrorHandler);
  }

  jQuery.fn["fullScreen"] = fullScreen;
  jQuery.fn["toggleFullScreen"] = toggleFullScreen;
  jQuery.fn["fullScreenSupported"] = fullScreenSupported;
  installFullScreenHandlers();

})();
