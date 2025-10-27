/* -*- Mode: Java; tab-width: 2; indent-tabs-mode: nil; c-basic-offset: 2 -*- */
/* vim: set shiftwidth=2 tabstop=2 autoindent cindent expandtab: */
/* Copyright 2012 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/* globals PDFJS, PDFBug, FirefoxCom, Stats, Cache, PDFFindBar, CustomStyle,
           PDFFindController, ProgressBar, TextLayerBuilder, DownloadManager,
           getFileName, scrollIntoView, getPDFFileNameFromURL, PDFHistory,
           Preferences, SidebarView, ViewHistory, PageView, ThumbnailView, URL,
           noContextMenuHandler, SecondaryToolbar, PasswordPrompt,
           PresentationMode, HandTool, Promise, DocumentProperties,
           DocumentOutlineView, DocumentAttachmentsView, OverlayManager */

'use strict';

var DEFAULT_URL = 'https://v4.ikase.org/fileupload/server/php/files/46/33882.pdf';
var DEFAULT_SCALE = 'auto';
var DEFAULT_SCALE_DELTA = 1.1;
var UNKNOWN_SCALE = 0;
var CACHE_SIZE = 10;
var CSS_UNITS = 96.0 / 72.0;
var SCROLLBAR_PADDING = 40;
var VERTICAL_PADDING = 5;
var MAX_AUTO_SCALE = 1.25;
var MIN_SCALE = 0.25;
var MAX_SCALE = 10.0;
var VIEW_HISTORY_MEMORY = 20;
var SCALE_SELECT_CONTAINER_PADDING = 8;
var SCALE_SELECT_PADDING = 22;
var THUMBNAIL_SCROLL_MARGIN = -19;
var CLEANUP_TIMEOUT = 30000;
var IGNORE_CURRENT_POSITION_ON_ZOOM = false;
var RenderingStates = {
  INITIAL: 0,
  RUNNING: 1,
  PAUSED: 2,
  FINISHED: 3
};
var FindStates = {
  FIND_FOUND: 0,
  FIND_NOTFOUND: 1,
  FIND_WRAPPED: 2,
  FIND_PENDING: 3
};

PDFJS.imageResourcesPath = './images/';
  PDFJS.workerSrc = '../build/pdf.worker.js';
  PDFJS.cMapUrl = '../web/cmaps/';
  PDFJS.cMapPacked = true;

var mozL10n = document.mozL10n || document.webL10n;


// optimised CSS custom property getter/setter
var CustomStyle = (function CustomStyleClosure() {

  // As noted on: http://www.zachstronaut.com/posts/2009/02/17/
  //              animate-css-transforms-firefox-webkit.html
  // in some versions of IE9 it is critical that ms appear in this list
  // before Moz
  var prefixes = ['ms', 'Moz', 'Webkit', 'O'];
  var _cache = {};

  function CustomStyle() {}

  CustomStyle.getProp = function get(propName, element) {
    // check cache only when no element is given
    if (arguments.length == 1 && typeof _cache[propName] == 'string') {
      return _cache[propName];
    }

    element = element || document.documentElement;
    var style = element.style, prefixed, uPropName;

    // test standard property first
    if (typeof style[propName] == 'string') {
      return (_cache[propName] = propName);
    }

    // capitalize
    uPropName = propName.charAt(0).toUpperCase() + propName.slice(1);

    // test vendor specific properties
    for (var i = 0, l = prefixes.length; i < l; i++) {
      prefixed = prefixes[i] + uPropName;
      if (typeof style[prefixed] == 'string') {
        return (_cache[propName] = prefixed);
      }
    }

    //if all fails then set to undefined
    return (_cache[propName] = 'undefined');
  };

  CustomStyle.setProp = function set(propName, element, str) {
    var prop = this.getProp(propName);
    if (prop != 'undefined') {
      element.style[prop] = str;
    }
  };

  return CustomStyle;
})();

function getFileName(url) {
  var anchor = url.indexOf('#');
  var query = url.indexOf('?');
  var end = Math.min(
    anchor > 0 ? anchor : url.length,
    query > 0 ? query : url.length);
  return url.substring(url.lastIndexOf('/', end) + 1, end);
}

/**
 * Returns scale factor for the canvas. It makes sense for the HiDPI displays.
 * @return {Object} The object with horizontal (sx) and vertical (sy)
                    scales. The scaled property is set to false if scaling is
                    not required, true otherwise.
 */
function getOutputScale(ctx) {
  var devicePixelRatio = window.devicePixelRatio || 1;
  var backingStoreRatio = ctx.webkitBackingStorePixelRatio ||
                          ctx.mozBackingStorePixelRatio ||
                          ctx.msBackingStorePixelRatio ||
                          ctx.oBackingStorePixelRatio ||
                          ctx.backingStorePixelRatio || 1;
  var pixelRatio = devicePixelRatio / backingStoreRatio;
  return {
    sx: pixelRatio,
    sy: pixelRatio,
    scaled: pixelRatio != 1
  };
}

/**
 * Scrolls specified element into view of its parent.
 * element {Object} The element to be visible.
 * spot {Object} An object with optional top and left properties,
 *               specifying the offset from the top left edge.
 */
function scrollIntoView(element, spot) {
  // Assuming offsetParent is available (it's not available when viewer is in
  // hidden iframe or object). We have to scroll: if the offsetParent is not set
  // producing the error. See also animationStartedClosure.
  var parent = element.offsetParent;
  var offsetY = element.offsetTop + element.clientTop;
  var offsetX = element.offsetLeft + element.clientLeft;
  if (!parent) {
    console.error('offsetParent is not set -- cannot scroll');
    return;
  }
  while (parent.clientHeight === parent.scrollHeight) {
    if (parent.dataset._scaleY) {
      offsetY /= parent.dataset._scaleY;
      offsetX /= parent.dataset._scaleX;
    }
    offsetY += parent.offsetTop;
    offsetX += parent.offsetLeft;
    parent = parent.offsetParent;
    if (!parent) {
      return; // no need to scroll
    }
  }
  if (spot) {
    if (spot.top !== undefined) {
      offsetY += spot.top;
    }
    if (spot.left !== undefined) {
      offsetX += spot.left;
      parent.scrollLeft = offsetX;
    }
  }
  parent.scrollTop = offsetY;
}

/**
 * Event handler to suppress context menu.
 */
function noContextMenuHandler(e) {
  e.preventDefault();
}

/**
 * Returns the filename or guessed filename from the url (see issue 3455).
 * url {String} The original PDF location.
 * @return {String} Guessed PDF file name.
 */
function getPDFFileNameFromURL(url) {
  var reURI = /^(?:([^:]+:)?\/\/[^\/]+)?([^?#]*)(\?[^#]*)?(#.*)?$/;
  //            SCHEME      HOST         1.PATH  2.QUERY   3.REF
  // Pattern to get last matching NAME.pdf
  var reFilename = /[^\/?#=]+\.pdf\b(?!.*\.pdf\b)/i;
  var splitURI = reURI.exec(url);
  var suggestedFilename = reFilename.exec(splitURI[1]) ||
                           reFilename.exec(splitURI[2]) ||
                           reFilename.exec(splitURI[3]);
  if (suggestedFilename) {
    suggestedFilename = suggestedFilename[0];
    if (suggestedFilename.indexOf('%') != -1) {
      // URL-encoded %2Fpath%2Fto%2Ffile.pdf should be file.pdf
      try {
        suggestedFilename =
          reFilename.exec(decodeURIComponent(suggestedFilename))[0];
      } catch(e) { // Possible (extremely rare) errors:
        // URIError "Malformed URI", e.g. for "%AA.pdf"
        // TypeError "null has no properties", e.g. for "%2F.pdf"
      }
    }
  }
  return suggestedFilename || 'document.pdf';
}

var ProgressBar = (function ProgressBarClosure() {

  function clamp(v, min, max) {
    return Math.min(Math.max(v, min), max);
  }

  function ProgressBar(id, opts) {

    // Fetch the sub-elements for later.
    this.div = document.querySelector(id + ' .progress');

    // Get the loading bar element, so it can be resized to fit the viewer.
    this.bar = this.div.parentNode;

    // Get options, with sensible defaults.
    this.height = opts.height || 100;
    this.width = opts.width || 100;
    this.units = opts.units || '%';

    // Initialize heights.
    this.div.style.height = this.height + this.units;
    this.percent = 0;
  }

  ProgressBar.prototype = {

    updateBar: function ProgressBar_updateBar() {
      if (this._indeterminate) {
        this.div.classList.add('indeterminate');
        this.div.style.width = this.width + this.units;
        return;
      }

      this.div.classList.remove('indeterminate');
      var progressSize = this.width * this._percent / 100;
      this.div.style.width = progressSize + this.units;
    },

    get percent() {
      return this._percent;
    },

    set percent(val) {
      this._indeterminate = isNaN(val);
      this._percent = clamp(val, 0, 100);
      this.updateBar();
    },

    setWidth: function ProgressBar_setWidth(viewer) {
      if (viewer) {
        var container = viewer.parentNode;
        var scrollbarWidth = container.offsetWidth - viewer.offsetWidth;
        if (scrollbarWidth > 0) {
          this.bar.setAttribute('style', 'width: calc(100% - ' +
                                         scrollbarWidth + 'px);');
        }
      }
    },

    hide: function ProgressBar_hide() {
      this.bar.classList.add('hidden');
      this.bar.removeAttribute('style');
    }
  };

  return ProgressBar;
})();

var Cache = function cacheCache(size) {
  var data = [];
  this.push = function cachePush(view) {
    var i = data.indexOf(view);
    if (i >= 0) {
      data.splice(i, 1);
    }
    data.push(view);
    if (data.length > size) {
      data.shift().destroy();
    }
  };
};




var DEFAULT_PREFERENCES = {
  showPreviousViewOnLoad: true,
  defaultZoomValue: '',
  sidebarViewOnLoad: 0,
  enableHandToolOnLoad: false,
  enableWebGL: false,
  disableRange: false,
  disableAutoFetch: false,
  disableFontFace: false,
  disableTextLayer: false,
  useOnlyCssZoom: false
};


var SidebarView = {
  NONE: 0,
  THUMBS: 1,
  OUTLINE: 2,
  ATTACHMENTS: 3
};

/**
 * Preferences - Utility for storing persistent settings.
 *   Used for settings that should be applied to all opened documents,
 *   or every time the viewer is loaded.
 */
var Preferences = {
  prefs: Object.create(DEFAULT_PREFERENCES),
  isInitializedPromiseResolved: false,
  initializedPromise: null,

  /**
   * Initialize and fetch the current preference values from storage.
   * @return {Promise} A promise that is resolved when the preferences
   *                   have been initialized.
   */
  initialize: function preferencesInitialize() {
    return this.initializedPromise =
        this._readFromStorage(DEFAULT_PREFERENCES).then(function(prefObj) {
      this.isInitializedPromiseResolved = true;
      if (prefObj) {
        this.prefs = prefObj;
      }
    }.bind(this));
  },

  /**
   * Stub function for writing preferences to storage.
   * NOTE: This should be overridden by a build-specific function defined below.
   * @param {Object} prefObj The preferences that should be written to storage.
   * @return {Promise} A promise that is resolved when the preference values
   *                   have been written.
   */
  _writeToStorage: function preferences_writeToStorage(prefObj) {
    return Promise.resolve();
  },

  /**
   * Stub function for reading preferences from storage.
   * NOTE: This should be overridden by a build-specific function defined below.
   * @param {Object} prefObj The preferences that should be read from storage.
   * @return {Promise} A promise that is resolved with an {Object} containing
   *                   the preferences that have been read.
   */
  _readFromStorage: function preferences_readFromStorage(prefObj) {
    return Promise.resolve();
  },

  /**
   * Reset the preferences to their default values and update storage.
   * @return {Promise} A promise that is resolved when the preference values
   *                   have been reset.
   */
  reset: function preferencesReset() {
    return this.initializedPromise.then(function() {
      this.prefs = Object.create(DEFAULT_PREFERENCES);
      return this._writeToStorage(DEFAULT_PREFERENCES);
    }.bind(this));
  },

  /**
   * Replace the current preference values with the ones from storage.
   * @return {Promise} A promise that is resolved when the preference values
   *                   have been updated.
   */
  reload: function preferencesReload() {
    return this.initializedPromise.then(function () {
      this._readFromStorage(DEFAULT_PREFERENCES).then(function(prefObj) {
        if (prefObj) {
          this.prefs = prefObj;
        }
      }.bind(this));
    }.bind(this));
  },

  /**
   * Set the value of a preference.
   * @param {string} name The name of the preference that should be changed.
   * @param {boolean|number|string} value The new value of the preference.
   * @return {Promise} A promise that is resolved when the value has been set,
   *                   provided that the preference exists and the types match.
   */
  set: function preferencesSet(name, value) {
    return this.initializedPromise.then(function () {
      if (DEFAULT_PREFERENCES[name] === undefined) {
        throw new Error('preferencesSet: \'' + name + '\' is undefined.');
      } else if (value === undefined) {
        throw new Error('preferencesSet: no value is specified.');
      }
      var valueType = typeof value;
      var defaultType = typeof DEFAULT_PREFERENCES[name];

      if (valueType !== defaultType) {
        if (valueType === 'number' && defaultType === 'string') {
          value = value.toString();
        } else {
          throw new Error('Preferences_set: \'' + value + '\' is a \"' +
                          valueType + '\", expected \"' + defaultType + '\".');
        }
      } else {
        if (valueType === 'number' && (value | 0) !== value) {
          throw new Error('Preferences_set: \'' + value +
                          '\' must be an \"integer\".');
        }
      }
      this.prefs[name] = value;
      return this._writeToStorage(this.prefs);
    }.bind(this));
  },

  /**
   * Get the value of a preference.
   * @param {string} name The name of the preference whose value is requested.
   * @return {Promise} A promise that is resolved with a {boolean|number|string}
   *                   containing the value of the preference.
   */
  get: function preferencesGet(name) {
    return this.initializedPromise.then(function () {
      var defaultValue = DEFAULT_PREFERENCES[name];

      if (defaultValue === undefined) {
        throw new Error('preferencesGet: \'' + name + '\' is undefined.');
      } else {
        var prefValue = this.prefs[name];

        if (prefValue !== undefined) {
          return prefValue;
        }
      }
      return defaultValue;
    }.bind(this));
  }
};


Preferences._writeToStorage = function (prefObj) {
  return new Promise(function (resolve) {
    localStorage.setItem('pdfjs.preferences', JSON.stringify(prefObj));
    resolve();
  });
};

Preferences._readFromStorage = function (prefObj) {
  return new Promise(function (resolve) {
    var readPrefs = JSON.parse(localStorage.getItem('pdfjs.preferences'));
    resolve(readPrefs);
  });
};


(function mozPrintCallbackPolyfillClosure() {
  if ('mozPrintCallback' in document.createElement('canvas')) {
    return;
  }
  // Cause positive result on feature-detection:
  HTMLCanvasElement.prototype.mozPrintCallback = undefined;

  var canvases;   // During print task: non-live NodeList of <canvas> elements
  var index;      // Index of <canvas> element that is being processed

  var print = window.print;
  window.print = function print() {
    if (canvases) {
      console.warn('Ignored window.print() because of a pending print job.');
      return;
    }
    try {
      dispatchEvent('beforeprint');
    } finally {
      canvases = document.querySelectorAll('canvas');
      index = -1;
      next();
    }
  };

  function dispatchEvent(eventType) {
    var event = document.createEvent('CustomEvent');
    event.initCustomEvent(eventType, false, false, 'custom');
    window.dispatchEvent(event);
  }

  function next() {
    if (!canvases) {
      return; // Print task cancelled by user (state reset in abort())
    }

    renderProgress();
    if (++index < canvases.length) {
      var canvas = canvases[index];
      if (typeof canvas.mozPrintCallback === 'function') {
        canvas.mozPrintCallback({
          context: canvas.getContext('2d'),
          abort: abort,
          done: next
        });
      } else {
        next();
      }
    } else {
      renderProgress();
      print.call(window);
      setTimeout(abort, 20); // Tidy-up
    }
  }

  function abort() {
    if (canvases) {
      canvases = null;
      renderProgress();
      dispatchEvent('afterprint');
    }
  }

  function renderProgress() {
    var progressContainer = document.getElementById('mozPrintCallback-shim');
    if (canvases) {
      var progress = Math.round(100 * index / canvases.length);
      var progressBar = progressContainer.querySelector('progress');
      var progressPerc = progressContainer.querySelector('.relative-progress');
      progressBar.value = progress;
      progressPerc.textContent = progress + '%';
      progressContainer.removeAttribute('hidden');
      progressContainer.onclick = abort;
    } else {
      progressContainer.setAttribute('hidden', '');
    }
  }

  var hasAttachEvent = !!document.attachEvent;

  window.addEventListener('keydown', function(event) {
    // Intercept Cmd/Ctrl + P in all browsers.
    // Also intercept Cmd/Ctrl + Shift + P in Chrome and Opera
    if (event.keyCode === 80/*P*/ && (event.ctrlKey || event.metaKey) &&
        !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
      window.print();
      if (hasAttachEvent) {
        // Only attachEvent can cancel Ctrl + P dialog in IE <=10
        // attachEvent is gone in IE11, so the dialog will re-appear in IE11.
        return;
      }
      event.preventDefault();
      if (event.stopImmediatePropagation) {
        event.stopImmediatePropagation();
      } else {
        event.stopPropagation();
      }
      return;
    }
    if (event.keyCode === 27 && canvases) { // Esc
      abort();
    }
  }, true);
  if (hasAttachEvent) {
    document.attachEvent('onkeydown', function(event) {
      event = event || window.event;
      if (event.keyCode === 80/*P*/ && event.ctrlKey) {
        event.keyCode = 0;
        return false;
      }
    });
  }

  if ('onbeforeprint' in window) {
    // Do not propagate before/afterprint events when they are not triggered
    // from within this polyfill. (FF/IE).
    var stopPropagationIfNeeded = function(event) {
      if (event.detail !== 'custom' && event.stopImmediatePropagation) {
        event.stopImmediatePropagation();
      }
    };
    window.addEventListener('beforeprint', stopPropagationIfNeeded, false);
    window.addEventListener('afterprint', stopPropagationIfNeeded, false);
  }
})();



var DownloadManager = (function DownloadManagerClosure() {

  function download(blobUrl, filename) {
    var a = document.createElement('a');
    if (a.click) {
      // Use a.click() if available. Otherwise, Chrome might show
      // "Unsafe JavaScript attempt to initiate a navigation change
      //  for frame with URL" and not open the PDF at all.
      // Supported by (not mentioned = untested):
      // - Firefox 6 - 19 (4- does not support a.click, 5 ignores a.click)
      // - Chrome 19 - 26 (18- does not support a.click)
      // - Opera 9 - 12.15
      // - Internet Explorer 6 - 10
      // - Safari 6 (5.1- does not support a.click)
      a.href = blobUrl;
      a.target = '_parent';
      // Use a.download if available. This increases the likelihood that
      // the file is downloaded instead of opened by another PDF plugin.
      if ('download' in a) {
        a.download = filename;
      }
      // <a> must be in the document for IE and recent Firefox versions.
      // (otherwise .click() is ignored)
      (document.body || document.documentElement).appendChild(a);
      a.click();
      a.parentNode.removeChild(a);
    } else {
      if (window.top === window &&
          blobUrl.split('#')[0] === window.location.href.split('#')[0]) {
        // If _parent == self, then opening an identical URL with different
        // location hash will only cause a navigation, not a download.
        var padCharacter = blobUrl.indexOf('?') === -1 ? '?' : '&';
        blobUrl = blobUrl.replace(/#|$/, padCharacter + '$&');
      }
      window.open(blobUrl, '_parent');
    }
  }

  function DownloadManager() {}

  DownloadManager.prototype = {
    downloadUrl: function DownloadManager_downloadUrl(url, filename) {
      if (!PDFJS.isValidUrl(url, true)) {
        return; // restricted/invalid URL
      }

      download(url + '#pdfjs.action=download', filename);
    },

    downloadData: function DownloadManager_downloadData(data, filename,
                                                        contentType) {
      if (navigator.msSaveBlob) { // IE10 and above
        return navigator.msSaveBlob(new Blob([data], { type: contentType }),
                                    filename);
      }

      var blobUrl = PDFJS.createObjectURL(data, contentType);
      download(blobUrl, filename);
    },

    download: function DownloadManager_download(blob, url, filename) {
      if (!URL) {
        // URL.createObjectURL is not supported
        this.downloadUrl(url, filename);
        return;
      }

      if (navigator.msSaveBlob) {
        // IE10 / IE11
        if (!navigator.msSaveBlob(blob, filename)) {
          this.downloadUrl(url, filename);
        }
        return;
      }

      var blobUrl = URL.createObjectURL(blob);
      download(blobUrl, filename);
    }
  };

  return DownloadManager;
})();




var cache = new Cache(CACHE_SIZE);
var currentPageNumber = 1;


/**
 * View History - This is a utility for saving various view parameters for
 *                recently opened files.
 *
 * The way that the view parameters are stored depends on how PDF.js is built,
 * for 'node make <flag>' the following cases exist:
 *  - FIREFOX or MOZCENTRAL - uses sessionStorage.
 *  - B2G                   - uses asyncStorage.
 *  - GENERIC or CHROME     - uses localStorage, if it is available.
 */
var ViewHistory = (function ViewHistoryClosure() {
  function ViewHistory(fingerprint) {
    this.fingerprint = fingerprint;
    this.isInitializedPromiseResolved = false;
    this.initializedPromise =
        this._readFromStorage().then(function (databaseStr) {
      this.isInitializedPromiseResolved = true;

      var database = JSON.parse(databaseStr || '{}');
      if (!('files' in database)) {
        database.files = [];
      }
      if (database.files.length >= VIEW_HISTORY_MEMORY) {
        database.files.shift();
      }
      var index;
      for (var i = 0, length = database.files.length; i < length; i++) {
        var branch = database.files[i];
        if (branch.fingerprint === this.fingerprint) {
          index = i;
          break;
        }
      }
      if (typeof index !== 'number') {
        index = database.files.push({fingerprint: this.fingerprint}) - 1;
      }
      this.file = database.files[index];
      this.database = database;
    }.bind(this));
  }

  ViewHistory.prototype = {
    _writeToStorage: function ViewHistory_writeToStorage() {
      return new Promise(function (resolve) {
        var databaseStr = JSON.stringify(this.database);



        localStorage.setItem('database', databaseStr);
        resolve();
      }.bind(this));
    },

    _readFromStorage: function ViewHistory_readFromStorage() {
      return new Promise(function (resolve) {


        resolve(localStorage.getItem('database'));
      });
    },

    set: function ViewHistory_set(name, val) {
      if (!this.isInitializedPromiseResolved) {
        return;
      }
      this.file[name] = val;
      return this._writeToStorage();
    },

    setMultiple: function ViewHistory_setMultiple(properties) {
      if (!this.isInitializedPromiseResolved) {
        return;
      }
      for (var name in properties) {
        this.file[name] = properties[name];
      }
      return this._writeToStorage();
    },

    get: function ViewHistory_get(name, defaultValue) {
      if (!this.isInitializedPromiseResolved) {
        return defaultValue;
      }
      return this.file[name] || defaultValue;
    }
  };

  return ViewHistory;
})();


/**
 * Creates a "search bar" given set of DOM elements
 * that act as controls for searching, or for setting
 * search preferences in the UI. This object also sets
 * up the appropriate events for the controls. Actual
 * searching is done by PDFFindController
 */
var PDFFindBar = {
  opened: false,
  bar: null,
  toggleButton: null,
  findField: null,
  highlightAll: null,
  caseSensitive: null,
  findMsg: null,
  findStatusIcon: null,
  findPreviousButton: null,
  findNextButton: null,

  initialize: function(options) {
    if(typeof PDFFindController === 'undefined' || PDFFindController === null) {
      throw 'PDFFindBar cannot be initialized ' +
            'without a PDFFindController instance.';
    }

    this.bar = options.bar;
    this.toggleButton = options.toggleButton;
    this.findField = options.findField;
    this.highlightAll = options.highlightAllCheckbox;
    this.caseSensitive = options.caseSensitiveCheckbox;
    this.findMsg = options.findMsg;
    this.findStatusIcon = options.findStatusIcon;
    this.findPreviousButton = options.findPreviousButton;
    this.findNextButton = options.findNextButton;

    var self = this;
    this.toggleButton.addEventListener('click', function() {
      self.toggle();
    });

    this.findField.addEventListener('input', function() {
      self.dispatchEvent('');
    });

    this.bar.addEventListener('keydown', function(evt) {
      switch (evt.keyCode) {
        case 13: // Enter
          if (evt.target === self.findField) {
            self.dispatchEvent('again', evt.shiftKey);
          }
          break;
        case 27: // Escape
          self.close();
          break;
      }
    });

    this.findPreviousButton.addEventListener('click',
      function() { self.dispatchEvent('again', true); }
    );

    this.findNextButton.addEventListener('click', function() {
      self.dispatchEvent('again', false);
    });

    this.highlightAll.addEventListener('click', function() {
      self.dispatchEvent('highlightallchange');
    });

    this.caseSensitive.addEventListener('click', function() {
      self.dispatchEvent('casesensitivitychange');
    });
  },

  dispatchEvent: function(aType, aFindPrevious) {
    var event = document.createEvent('CustomEvent');
    event.initCustomEvent('find' + aType, true, true, {
      query: this.findField.value,
      caseSensitive: this.caseSensitive.checked,
      highlightAll: this.highlightAll.checked,
      findPrevious: aFindPrevious
    });
    return window.dispatchEvent(event);
  },

  updateUIState: function(state, previous) {
    var notFound = false;
    var findMsg = '';
    var status = '';

    switch (state) {
      case FindStates.FIND_FOUND:
        break;

      case FindStates.FIND_PENDING:
        status = 'pending';
        break;

      case FindStates.FIND_NOTFOUND:
        findMsg = mozL10n.get('find_not_found', null, 'Phrase not found');
        notFound = true;
        break;

      case FindStates.FIND_WRAPPED:
        if (previous) {
          findMsg = mozL10n.get('find_reached_top', null,
                      'Reached top of document, continued from bottom');
        } else {
          findMsg = mozL10n.get('find_reached_bottom', null,
                                'Reached end of document, continued from top');
        }
        break;
    }

    if (notFound) {
      this.findField.classList.add('notFound');
    } else {
      this.findField.classList.remove('notFound');
    }

    this.findField.setAttribute('data-status', status);
    this.findMsg.textContent = findMsg;
  },

  open: function() {
    if (!this.opened) {
      this.opened = true;
      this.toggleButton.classList.add('toggled');
      this.bar.classList.remove('hidden');
    }

    this.findField.select();
    this.findField.focus();
  },

  close: function() {
    if (!this.opened) {
      return;
    }
    this.opened = false;
    this.toggleButton.classList.remove('toggled');
    this.bar.classList.add('hidden');

    PDFFindController.active = false;
  },

  toggle: function() {
    if (this.opened) {
      this.close();
    } else {
      this.open();
    }
  }
};



/**
 * Provides a "search" or "find" functionality for the PDF.
 * This object actually performs the search for a given string.
 */

var PDFFindController = {
  startedTextExtraction: false,

  extractTextPromises: [],

  pendingFindMatches: {},

  // If active, find results will be highlighted.
  active: false,

  // Stores the text for each page.
  pageContents: [],

  pageMatches: [],

  // Currently selected match.
  selected: {
    pageIdx: -1,
    matchIdx: -1
  },

  // Where find algorithm currently is in the document.
  offset: {
    pageIdx: null,
    matchIdx: null
  },

  resumePageIdx: null,

  state: null,

  dirtyMatch: false,

  findTimeout: null,

  pdfPageSource: null,

  integratedFind: false,

  initialize: function(options) {
    if(typeof PDFFindBar === 'undefined' || PDFFindBar === null) {
      throw 'PDFFindController cannot be initialized ' +
            'without a PDFFindBar instance';
    }

    this.pdfPageSource = options.pdfPageSource;
    this.integratedFind = options.integratedFind;

    var events = [
      'find',
      'findagain',
      'findhighlightallchange',
      'findcasesensitivitychange'
    ];

    this.firstPagePromise = new Promise(function (resolve) {
      this.resolveFirstPage = resolve;
    }.bind(this));
    this.handleEvent = this.handleEvent.bind(this);

    for (var i = 0; i < events.length; i++) {
      window.addEventListener(events[i], this.handleEvent);
    }
  },

  reset: function pdfFindControllerReset() {
    this.startedTextExtraction = false;
    this.extractTextPromises = [];
    this.active = false;
  },

  calcFindMatch: function(pageIndex) {
    var pageContent = this.pageContents[pageIndex];
    var query = this.state.query;
    var caseSensitive = this.state.caseSensitive;
    var queryLen = query.length;

    if (queryLen === 0) {
      // Do nothing the matches should be wiped out already.
      return;
    }

    if (!caseSensitive) {
      pageContent = pageContent.toLowerCase();
      query = query.toLowerCase();
    }

    var matches = [];

    var matchIdx = -queryLen;
    while (true) {
      matchIdx = pageContent.indexOf(query, matchIdx + queryLen);
      if (matchIdx === -1) {
        break;
      }

      matches.push(matchIdx);
    }
    this.pageMatches[pageIndex] = matches;
    this.updatePage(pageIndex);
    if (this.resumePageIdx === pageIndex) {
      this.resumePageIdx = null;
      this.nextPageMatch();
    }
  },

  extractText: function() {
    if (this.startedTextExtraction) {
      return;
    }
    this.startedTextExtraction = true;

    this.pageContents = [];
    var extractTextPromisesResolves = [];
    for (var i = 0, ii = this.pdfPageSource.pdfDocument.numPages; i < ii; i++) {
      this.extractTextPromises.push(new Promise(function (resolve) {
        extractTextPromisesResolves.push(resolve);
      }));
    }

    var self = this;
    function extractPageText(pageIndex) {
      self.pdfPageSource.pages[pageIndex].getTextContent().then(
        function textContentResolved(textContent) {
          var textItems = textContent.items;
          var str = '';

          for (var i = 0; i < textItems.length; i++) {
            str += textItems[i].str;
          }

          // Store the pageContent as a string.
          self.pageContents.push(str);

          extractTextPromisesResolves[pageIndex](pageIndex);
          if ((pageIndex + 1) < self.pdfPageSource.pages.length) {
            extractPageText(pageIndex + 1);
          }
        }
      );
    }
    extractPageText(0);
  },

  handleEvent: function(e) {
    if (this.state === null || e.type !== 'findagain') {
      this.dirtyMatch = true;
    }
    this.state = e.detail;
    this.updateUIState(FindStates.FIND_PENDING);

    this.firstPagePromise.then(function() {
      this.extractText();

      clearTimeout(this.findTimeout);
      if (e.type === 'find') {
        // Only trigger the find action after 250ms of silence.
        this.findTimeout = setTimeout(this.nextMatch.bind(this), 250);
      } else {
        this.nextMatch();
      }
    }.bind(this));
  },

  updatePage: function(idx) {
    var page = this.pdfPageSource.pages[idx];

    if (this.selected.pageIdx === idx) {
      // If the page is selected, scroll the page into view, which triggers
      // rendering the page, which adds the textLayer. Once the textLayer is
      // build, it will scroll onto the selected match.
      page.scrollIntoView();
    }

    if (page.textLayer) {
      page.textLayer.updateMatches();
    }
  },

  nextMatch: function() {
    var previous = this.state.findPrevious;
    var currentPageIndex = this.pdfPageSource.page - 1;
    var numPages = this.pdfPageSource.pages.length;

    this.active = true;

    if (this.dirtyMatch) {
      // Need to recalculate the matches, reset everything.
      this.dirtyMatch = false;
      this.selected.pageIdx = this.selected.matchIdx = -1;
      this.offset.pageIdx = currentPageIndex;
      this.offset.matchIdx = null;
      this.hadMatch = false;
      this.resumePageIdx = null;
      this.pageMatches = [];
      var self = this;

      for (var i = 0; i < numPages; i++) {
        // Wipe out any previous highlighted matches.
        this.updatePage(i);

        // As soon as the text is extracted start finding the matches.
        if (!(i in this.pendingFindMatches)) {
          this.pendingFindMatches[i] = true;
          this.extractTextPromises[i].then(function(pageIdx) {
            delete self.pendingFindMatches[pageIdx];
            self.calcFindMatch(pageIdx);
          });
        }
      }
    }

    // If there's no query there's no point in searching.
    if (this.state.query === '') {
      this.updateUIState(FindStates.FIND_FOUND);
      return;
    }

    // If we're waiting on a page, we return since we can't do anything else.
    if (this.resumePageIdx) {
      return;
    }

    var offset = this.offset;
    // If there's already a matchIdx that means we are iterating through a
    // page's matches.
    if (offset.matchIdx !== null) {
      var numPageMatches = this.pageMatches[offset.pageIdx].length;
      if ((!previous && offset.matchIdx + 1 < numPageMatches) ||
          (previous && offset.matchIdx > 0)) {
        // The simple case, we just have advance the matchIdx to select the next
        // match on the page.
        this.hadMatch = true;
        offset.matchIdx = previous ? offset.matchIdx - 1 : offset.matchIdx + 1;
        this.updateMatch(true);
        return;
      }
      // We went beyond the current page's matches, so we advance to the next
      // page.
      this.advanceOffsetPage(previous);
    }
    // Start searching through the page.
    this.nextPageMatch();
  },

  matchesReady: function(matches) {
    var offset = this.offset;
    var numMatches = matches.length;
    var previous = this.state.findPrevious;
    if (numMatches) {
      // There were matches for the page, so initialize the matchIdx.
      this.hadMatch = true;
      offset.matchIdx = previous ? numMatches - 1 : 0;
      this.updateMatch(true);
      // matches were found
      return true;
    } else {
      // No matches attempt to search the next page.
      this.advanceOffsetPage(previous);
      if (offset.wrapped) {
        offset.matchIdx = null;
        if (!this.hadMatch) {
          // No point in wrapping there were no matches.
          this.updateMatch(false);
          // while matches were not found, searching for a page 
          // with matches should nevertheless halt.
          return true;
        }
      }
      // matches were not found (and searching is not done)
      return false;
    }
  },

  nextPageMatch: function() {
    if (this.resumePageIdx !== null) {
      console.error('There can only be one pending page.');
    }
    do {
      var pageIdx = this.offset.pageIdx;
      var matches = this.pageMatches[pageIdx];
      if (!matches) {
        // The matches don't exist yet for processing by "matchesReady",
        // so set a resume point for when they do exist.
        this.resumePageIdx = pageIdx;
        break;
      }
    } while (!this.matchesReady(matches));
  },

  advanceOffsetPage: function(previous) {
    var offset = this.offset;
    var numPages = this.extractTextPromises.length;
    offset.pageIdx = previous ? offset.pageIdx - 1 : offset.pageIdx + 1;
    offset.matchIdx = null;
    if (offset.pageIdx >= numPages || offset.pageIdx < 0) {
      offset.pageIdx = previous ? numPages - 1 : 0;
      offset.wrapped = true;
      return;
    }
  },

  updateMatch: function(found) {
    var state = FindStates.FIND_NOTFOUND;
    var wrapped = this.offset.wrapped;
    this.offset.wrapped = false;
    if (found) {
      var previousPage = this.selected.pageIdx;
      this.selected.pageIdx = this.offset.pageIdx;
      this.selected.matchIdx = this.offset.matchIdx;
      state = wrapped ? FindStates.FIND_WRAPPED : FindStates.FIND_FOUND;
      // Update the currently selected page to wipe out any selected matches.
      if (previousPage !== -1 && previousPage !== this.selected.pageIdx) {
        this.updatePage(previousPage);
      }
    }
    this.updateUIState(state, this.state.findPrevious);
    if (this.selected.pageIdx !== -1) {
      this.updatePage(this.selected.pageIdx, true);
    }
  },

  updateUIState: function(state, previous) {
    if (this.integratedFind) {
      FirefoxCom.request('updateFindControlState',
                         {result: state, findPrevious: previous});
      return;
    }
    PDFFindBar.updateUIState(state, previous);
  }
};



var PDFHistory = {
  initialized: false,
  initialDestination: null,

  initialize: function pdfHistoryInitialize(fingerprint) {
    if (PDFJS.disableHistory || PDFView.isViewerEmbedded) {
      // The browsing history is only enabled when the viewer is standalone,
      // i.e. not when it is embedded in a web page.
      return;
    }
    this.initialized = true;
    this.reInitialized = false;
    this.allowHashChange = true;
    this.historyUnlocked = true;

    this.previousHash = window.location.hash.substring(1);
    this.currentBookmark = '';
    this.currentPage = 0;
    this.updatePreviousBookmark = false;
    this.previousBookmark = '';
    this.previousPage = 0;
    this.nextHashParam = '';

    this.fingerprint = fingerprint;
    this.currentUid = this.uid = 0;
    this.current = {};

    var state = window.history.state;
    if (this._isStateObjectDefined(state)) {
      // This corresponds to navigating back to the document
      // from another page in the browser history.
      if (state.target.dest) {
        this.initialDestination = state.target.dest;
      } else {
        PDFView.initialBookmark = state.target.hash;
      }
      this.currentUid = state.uid;
      this.uid = state.uid + 1;
      this.current = state.target;
    } else {
      // This corresponds to the loading of a new document.
      if (state && state.fingerprint &&
          this.fingerprint !== state.fingerprint) {
        // Reinitialize the browsing history when a new document
        // is opened in the web viewer.
        this.reInitialized = true;
      }
      this._pushOrReplaceState({ fingerprint: this.fingerprint }, true);
    }

    var self = this;
    window.addEventListener('popstate', function pdfHistoryPopstate(evt) {
      evt.preventDefault();
      evt.stopPropagation();

      if (!self.historyUnlocked) {
        return;
      }
      if (evt.state) {
        // Move back/forward in the history.
        self._goTo(evt.state);
      } else {
        // Handle the user modifying the hash of a loaded document.
        self.previousHash = window.location.hash.substring(1);

        // If the history is empty when the hash changes,
        // update the previous entry in the browser history.
        if (self.uid === 0) {
          var previousParams = (self.previousHash && self.currentBookmark &&
                                self.previousHash !== self.currentBookmark) ?
            { hash: self.currentBookmark, page: self.currentPage } :
            { page: 1 };
          self.historyUnlocked = false;
          self.allowHashChange = false;
          window.history.back();
          self._pushToHistory(previousParams, false, true);
          window.history.forward();
          self.historyUnlocked = true;
        }
        self._pushToHistory({ hash: self.previousHash }, false, true);
        self._updatePreviousBookmark();
      }
    }, false);

    function pdfHistoryBeforeUnload() {
      var previousParams = self._getPreviousParams(null, true);
      if (previousParams) {
        var replacePrevious = (!self.current.dest &&
                               self.current.hash !== self.previousHash);
        self._pushToHistory(previousParams, false, replacePrevious);
        self._updatePreviousBookmark();
      }
      // Remove the event listener when navigating away from the document,
      // since 'beforeunload' prevents Firefox from caching the document.
      window.removeEventListener('beforeunload', pdfHistoryBeforeUnload, false);
    }
    window.addEventListener('beforeunload', pdfHistoryBeforeUnload, false);

    window.addEventListener('pageshow', function pdfHistoryPageShow(evt) {
      // If the entire viewer (including the PDF file) is cached in the browser,
      // we need to reattach the 'beforeunload' event listener since
      // the 'DOMContentLoaded' event is not fired on 'pageshow'.
      window.addEventListener('beforeunload', pdfHistoryBeforeUnload, false);
    }, false);
  },

  _isStateObjectDefined: function pdfHistory_isStateObjectDefined(state) {
    return (state && state.uid >= 0 &&
            state.fingerprint && this.fingerprint === state.fingerprint &&
            state.target && state.target.hash) ? true : false;
  },

  _pushOrReplaceState: function pdfHistory_pushOrReplaceState(stateObj,
                                                              replace) {
    if (replace) {
      window.history.replaceState(stateObj, '', document.URL);
    } else {
      window.history.pushState(stateObj, '', document.URL);
    }
  },

  get isHashChangeUnlocked() {
    if (!this.initialized) {
      return true;
    }
    // If the current hash changes when moving back/forward in the history,
    // this will trigger a 'popstate' event *as well* as a 'hashchange' event.
    // Since the hash generally won't correspond to the exact the position
    // stored in the history's state object, triggering the 'hashchange' event
    // can thus corrupt the browser history.
    //
    // When the hash changes during a 'popstate' event, we *only* prevent the
    // first 'hashchange' event and immediately reset allowHashChange.
    // If it is not reset, the user would not be able to change the hash.

    var temp = this.allowHashChange;
    this.allowHashChange = true;
    return temp;
  },

  _updatePreviousBookmark: function pdfHistory_updatePreviousBookmark() {
    if (this.updatePreviousBookmark &&
        this.currentBookmark && this.currentPage) {
      this.previousBookmark = this.currentBookmark;
      this.previousPage = this.currentPage;
      this.updatePreviousBookmark = false;
    }
  },

  updateCurrentBookmark: function pdfHistoryUpdateCurrentBookmark(bookmark,
                                                                  pageNum) {
    if (this.initialized) {
      this.currentBookmark = bookmark.substring(1);
      this.currentPage = pageNum | 0;
      this._updatePreviousBookmark();
    }
  },

  updateNextHashParam: function pdfHistoryUpdateNextHashParam(param) {
    if (this.initialized) {
      this.nextHashParam = param;
    }
  },

  push: function pdfHistoryPush(params, isInitialBookmark) {
    if (!(this.initialized && this.historyUnlocked)) {
      return;
    }
    if (params.dest && !params.hash) {
      params.hash = (this.current.hash && this.current.dest &&
                     this.current.dest === params.dest) ?
        this.current.hash :
        PDFView.getDestinationHash(params.dest).split('#')[1];
    }
    if (params.page) {
      params.page |= 0;
    }
    if (isInitialBookmark) {
      var target = window.history.state.target;
      if (!target) {
        // Invoked when the user specifies an initial bookmark,
        // thus setting PDFView.initialBookmark, when the document is loaded.
        this._pushToHistory(params, false);
        this.previousHash = window.location.hash.substring(1);
      }
      this.updatePreviousBookmark = this.nextHashParam ? false : true;
      if (target) {
        // If the current document is reloaded,
        // avoid creating duplicate entries in the history.
        this._updatePreviousBookmark();
      }
      return;
    }
    if (this.nextHashParam) {
      if (this.nextHashParam === params.hash) {
        this.nextHashParam = null;
        this.updatePreviousBookmark = true;
        return;
      } else {
        this.nextHashParam = null;
      }
    }

    if (params.hash) {
      if (this.current.hash) {
        if (this.current.hash !== params.hash) {
          this._pushToHistory(params, true);
        } else {
          if (!this.current.page && params.page) {
            this._pushToHistory(params, false, true);
          }
          this.updatePreviousBookmark = true;
        }
      } else {
        this._pushToHistory(params, true);
      }
    } else if (this.current.page && params.page &&
               this.current.page !== params.page) {
      this._pushToHistory(params, true);
    }
  },

  _getPreviousParams: function pdfHistory_getPreviousParams(onlyCheckPage,
                                                            beforeUnload) {
    if (!(this.currentBookmark && this.currentPage)) {
      return null;
    } else if (this.updatePreviousBookmark) {
      this.updatePreviousBookmark = false;
    }
    if (this.uid > 0 && !(this.previousBookmark && this.previousPage)) {
      // Prevent the history from getting stuck in the current state,
      // effectively preventing the user from going back/forward in the history.
      //
      // This happens if the current position in the document didn't change when
      // the history was previously updated. The reasons for this are either:
      // 1. The current zoom value is such that the document does not need to,
      //    or cannot, be scrolled to display the destination.
      // 2. The previous destination is broken, and doesn't actally point to a
      //    position within the document.
      //    (This is either due to a bad PDF generator, or the user making a
      //     mistake when entering a destination in the hash parameters.)
      return null;
    }
    if ((!this.current.dest && !onlyCheckPage) || beforeUnload) {
      if (this.previousBookmark === this.currentBookmark) {
        return null;
      }
    } else if (this.current.page || onlyCheckPage) {
      if (this.previousPage === this.currentPage) {
        return null;
      }
    } else {
      return null;
    }
    var params = { hash: this.currentBookmark, page: this.currentPage };
    if (PresentationMode.active) {
      params.hash = null;
    }
    return params;
  },

  _stateObj: function pdfHistory_stateObj(params) {
    return { fingerprint: this.fingerprint, uid: this.uid, target: params };
  },

  _pushToHistory: function pdfHistory_pushToHistory(params,
                                                    addPrevious, overwrite) {
    if (!this.initialized) {
      return;
    }
    if (!params.hash && params.page) {
      params.hash = ('page=' + params.page);
    }
    if (addPrevious && !overwrite) {
      var previousParams = this._getPreviousParams();
      if (previousParams) {
        var replacePrevious = (!this.current.dest &&
                               this.current.hash !== this.previousHash);
        this._pushToHistory(previousParams, false, replacePrevious);
      }
    }
    this._pushOrReplaceState(this._stateObj(params),
                             (overwrite || this.uid === 0));
    this.currentUid = this.uid++;
    this.current = params;
    this.updatePreviousBookmark = true;
  },

  _goTo: function pdfHistory_goTo(state) {
    if (!(this.initialized && this.historyUnlocked &&
          this._isStateObjectDefined(state))) {
      return;
    }
    if (!this.reInitialized && state.uid < this.currentUid) {
      var previousParams = this._getPreviousParams(true);
      if (previousParams) {
        this._pushToHistory(this.current, false);
        this._pushToHistory(previousParams, false);
        this.currentUid = state.uid;
        window.history.back();
        return;
      }
    }
    this.historyUnlocked = false;

    if (state.target.dest) {
      PDFView.navigateTo(state.target.dest);
    } else {
      PDFView.setHash(state.target.hash);
    }
    this.currentUid = state.uid;
    if (state.uid > this.uid) {
      this.uid = state.uid;
    }
    this.current = state.target;
    this.updatePreviousBookmark = true;

    var currentHash = window.location.hash.substring(1);
    if (this.previousHash !== currentHash) {
      this.allowHashChange = false;
    }
    this.previousHash = currentHash;

    this.historyUnlocked = true;
  },

  back: function pdfHistoryBack() {
    this.go(-1);
  },

  forward: function pdfHistoryForward() {
    this.go(1);
  },

  go: function pdfHistoryGo(direction) {
    if (this.initialized && this.historyUnlocked) {
      var state = window.history.state;
      if (direction === -1 && state && state.uid > 0) {
        window.history.back();
      } else if (direction === 1 && state && state.uid < (this.uid - 1)) {
        window.history.forward();
      }
    }
  }
};


var SecondaryToolbar = {
  opened: false,
  previousContainerHeight: null,
  newContainerHeight: null,

  initialize: function secondaryToolbarInitialize(options) {
    this.toolbar = options.toolbar;
    this.presentationMode = options.presentationMode;
    this.documentProperties = options.documentProperties;
    this.buttonContainer = this.toolbar.firstElementChild;

    // Define the toolbar buttons.
    this.toggleButton = options.toggleButton;
    this.presentationModeButton = options.presentationModeButton;
    this.openFile = options.openFile;
    this.print = options.print;
    this.download = options.download;
    this.viewBookmark = options.viewBookmark;
    this.firstPage = options.firstPage;
    this.lastPage = options.lastPage;
    this.pageRotateCw = options.pageRotateCw;
    this.pageRotateCcw = options.pageRotateCcw;
    this.documentPropertiesButton = options.documentPropertiesButton;

    // Attach the event listeners.
    var elements = [
      // Button to toggle the visibility of the secondary toolbar:
      { element: this.toggleButton, handler: this.toggle },
      // All items within the secondary toolbar
      // (except for toggleHandTool, hand_tool.js is responsible for it):
      { element: this.presentationModeButton,
        handler: this.presentationModeClick },
      { element: this.openFile, handler: this.openFileClick },
      { element: this.print, handler: this.printClick },
      { element: this.download, handler: this.downloadClick },
      { element: this.viewBookmark, handler: this.viewBookmarkClick },
      { element: this.firstPage, handler: this.firstPageClick },
      { element: this.lastPage, handler: this.lastPageClick },
      { element: this.pageRotateCw, handler: this.pageRotateCwClick },
      { element: this.pageRotateCcw, handler: this.pageRotateCcwClick },
      { element: this.documentPropertiesButton,
        handler: this.documentPropertiesClick }
    ];

    for (var item in elements) {
      var element = elements[item].element;
      if (element) {
        element.addEventListener('click', elements[item].handler.bind(this));
      }
    }
  },

  // Event handling functions.
  presentationModeClick: function secondaryToolbarPresentationModeClick(evt) {
    this.presentationMode.request();
    this.close();
  },

  openFileClick: function secondaryToolbarOpenFileClick(evt) {
    document.getElementById('fileInput').click();
    this.close();
  },

  printClick: function secondaryToolbarPrintClick(evt) {
    window.print();
    this.close();
  },

  downloadClick: function secondaryToolbarDownloadClick(evt) {
    PDFView.download();
    this.close();
  },

  viewBookmarkClick: function secondaryToolbarViewBookmarkClick(evt) {
    this.close();
  },

  firstPageClick: function secondaryToolbarFirstPageClick(evt) {
    PDFView.page = 1;
    this.close();
  },

  lastPageClick: function secondaryToolbarLastPageClick(evt) {
    PDFView.page = PDFView.pdfDocument.numPages;
    this.close();
  },

  pageRotateCwClick: function secondaryToolbarPageRotateCwClick(evt) {
    PDFView.rotatePages(90);
  },

  pageRotateCcwClick: function secondaryToolbarPageRotateCcwClick(evt) {
    PDFView.rotatePages(-90);
  },

  documentPropertiesClick: function secondaryToolbarDocumentPropsClick(evt) {
    this.documentProperties.open();
    this.close();
  },

  // Misc. functions for interacting with the toolbar.
  setMaxHeight: function secondaryToolbarSetMaxHeight(container) {
    if (!container || !this.buttonContainer) {
      return;
    }
    this.newContainerHeight = container.clientHeight;
    if (this.previousContainerHeight === this.newContainerHeight) {
      return;
    }
    this.buttonContainer.setAttribute('style',
      'max-height: ' + (this.newContainerHeight - SCROLLBAR_PADDING) + 'px;');
    this.previousContainerHeight = this.newContainerHeight;
  },

  open: function secondaryToolbarOpen() {
    if (this.opened) {
      return;
    }
    this.opened = true;
    this.toggleButton.classList.add('toggled');
    this.toolbar.classList.remove('hidden');
  },

  close: function secondaryToolbarClose(target) {
    if (!this.opened) {
      return;
    } else if (target && !this.toolbar.contains(target)) {
      return;
    }
    this.opened = false;
    this.toolbar.classList.add('hidden');
    this.toggleButton.classList.remove('toggled');
  },

  toggle: function secondaryToolbarToggle() {
    if (this.opened) {
      this.close();
    } else {
      this.open();
    }
  }
};


var DELAY_BEFORE_HIDING_CONTROLS = 3000; // in ms
var SELECTOR = 'presentationControls';
var DELAY_BEFORE_RESETTING_SWITCH_IN_PROGRESS = 1000; // in ms

var PresentationMode = {
  active: false,
  args: null,
  contextMenuOpen: false,
  prevCoords: { x: null, y: null },

  initialize: function presentationModeInitialize(options) {
    this.container = options.container;
    this.secondaryToolbar = options.secondaryToolbar;

    this.viewer = this.container.firstElementChild;

    this.firstPage = options.firstPage;
    this.lastPage = options.lastPage;
    this.pageRotateCw = options.pageRotateCw;
    this.pageRotateCcw = options.pageRotateCcw;

    this.firstPage.addEventListener('click', function() {
      this.contextMenuOpen = false;
      this.secondaryToolbar.firstPageClick();
    }.bind(this));
    this.lastPage.addEventListener('click', function() {
      this.contextMenuOpen = false;
      this.secondaryToolbar.lastPageClick();
    }.bind(this));

    this.pageRotateCw.addEventListener('click', function() {
      this.contextMenuOpen = false;
      this.secondaryToolbar.pageRotateCwClick();
    }.bind(this));
    this.pageRotateCcw.addEventListener('click', function() {
      this.contextMenuOpen = false;
      this.secondaryToolbar.pageRotateCcwClick();
    }.bind(this));
  },

  get isFullscreen() {
    return (document.fullscreenElement ||
            document.mozFullScreen ||
            document.webkitIsFullScreen ||
            document.msFullscreenElement);
  },

  /**
   * Initialize a timeout that is used to reset PDFView.currentPosition when the
   * browser transitions to fullscreen mode. Since resize events are triggered
   * multiple times during the switch to fullscreen mode, this is necessary in
   * order to prevent the page from being scrolled partially, or completely,
   * out of view when Presentation Mode is enabled.
   * Note: This is only an issue at certain zoom levels, e.g. 'page-width'.
   */
  _setSwitchInProgress: function presentationMode_setSwitchInProgress() {
    if (this.switchInProgress) {
      clearTimeout(this.switchInProgress);
    }
    this.switchInProgress = setTimeout(function switchInProgressTimeout() {
      delete this.switchInProgress;
    }.bind(this), DELAY_BEFORE_RESETTING_SWITCH_IN_PROGRESS);

    PDFView.currentPosition = null;
  },

  _resetSwitchInProgress: function presentationMode_resetSwitchInProgress() {
    if (this.switchInProgress) {
      clearTimeout(this.switchInProgress);
      delete this.switchInProgress;
    }
  },

  request: function presentationModeRequest() {
    if (!PDFView.supportsFullscreen || this.isFullscreen ||
        !this.viewer.hasChildNodes()) {
      return false;
    }
    this._setSwitchInProgress();

    if (this.container.requestFullscreen) {
      this.container.requestFullscreen();
    } else if (this.container.mozRequestFullScreen) {
      this.container.mozRequestFullScreen();
    } else if (this.container.webkitRequestFullScreen) {
      this.container.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
    } else if (this.container.msRequestFullscreen) {
      this.container.msRequestFullscreen();
    } else {
      return false;
    }

    this.args = {
      page: PDFView.page,
      previousScale: PDFView.currentScaleValue
    };

    return true;
  },

  enter: function presentationModeEnter() {
    this.active = true;
    this._resetSwitchInProgress();

    // Ensure that the correct page is scrolled into view when entering
    // Presentation Mode, by waiting until fullscreen mode in enabled.
    // Note: This is only necessary in non-Mozilla browsers.
    setTimeout(function enterPresentationModeTimeout() {
      PDFView.page = this.args.page;
      PDFView.setScale('page-fit', true);
    }.bind(this), 0);

    window.addEventListener('mousemove', this.mouseMove, false);
    window.addEventListener('mousedown', this.mouseDown, false);
    window.addEventListener('contextmenu', this.contextMenu, false);

    this.showControls();
    HandTool.enterPresentationMode();
    this.contextMenuOpen = false;
    this.container.setAttribute('contextmenu', 'viewerContextMenu');
  },

  exit: function presentationModeExit() {
    var page = PDFView.page;

    // Ensure that the correct page is scrolled into view when exiting
    // Presentation Mode, by waiting until fullscreen mode is disabled.
    // Note: This is only necessary in non-Mozilla browsers.
    setTimeout(function exitPresentationModeTimeout() {
      this.active = false;
      PDFView.setScale(this.args.previousScale);
      PDFView.page = page;
      this.args = null;
    }.bind(this), 0);

    window.removeEventListener('mousemove', this.mouseMove, false);
    window.removeEventListener('mousedown', this.mouseDown, false);
    window.removeEventListener('contextmenu', this.contextMenu, false);

    this.hideControls();
    PDFView.clearMouseScrollState();
    HandTool.exitPresentationMode();
    this.container.removeAttribute('contextmenu');
    this.contextMenuOpen = false;

    // Ensure that the thumbnail of the current page is visible
    // when exiting presentation mode.
    scrollIntoView(document.getElementById('thumbnailContainer' + page));
  },

  showControls: function presentationModeShowControls() {
    if (this.controlsTimeout) {
      clearTimeout(this.controlsTimeout);
    } else {
      this.container.classList.add(SELECTOR);
    }
    this.controlsTimeout = setTimeout(function hideControlsTimeout() {
      this.container.classList.remove(SELECTOR);
      delete this.controlsTimeout;
    }.bind(this), DELAY_BEFORE_HIDING_CONTROLS);
  },

  hideControls: function presentationModeHideControls() {
    if (!this.controlsTimeout) {
      return;
    }
    this.container.classList.remove(SELECTOR);
    clearTimeout(this.controlsTimeout);
    delete this.controlsTimeout;
  },

  mouseMove: function presentationModeMouseMove(evt) {
    // Workaround for a bug in WebKit browsers that causes the 'mousemove' event
    // to be fired when the cursor is changed. For details, see:
    // http://code.google.com/p/chromium/issues/detail?id=103041.

    var currCoords = { x: evt.clientX, y: evt.clientY };
    var prevCoords = PresentationMode.prevCoords;
    PresentationMode.prevCoords = currCoords;

    if (currCoords.x === prevCoords.x && currCoords.y === prevCoords.y) {
      return;
    }
    PresentationMode.showControls();
  },

  mouseDown: function presentationModeMouseDown(evt) {
    var self = PresentationMode;
    if (self.contextMenuOpen) {
      self.contextMenuOpen = false;
      evt.preventDefault();
      return;
    }

    if (evt.button === 0) {
      // Enable clicking of links in presentation mode. Please note:
      // Only links pointing to destinations in the current PDF document work.
      var isInternalLink = (evt.target.href &&
                            evt.target.classList.contains('internalLink'));
      if (!isInternalLink) {
        // Unless an internal link was clicked, advance one page.
        evt.preventDefault();
        PDFView.page += (evt.shiftKey ? -1 : 1);
      }
    }
  },

  contextMenu: function presentationModeContextMenu(evt) {
    PresentationMode.contextMenuOpen = true;
  }
};

(function presentationModeClosure() {
  function presentationModeChange(e) {
    if (PresentationMode.isFullscreen) {
      PresentationMode.enter();
    } else {
      PresentationMode.exit();
    }
  }

  window.addEventListener('fullscreenchange', presentationModeChange, false);
  window.addEventListener('mozfullscreenchange', presentationModeChange, false);
  window.addEventListener('webkitfullscreenchange', presentationModeChange,
                          false);
  window.addEventListener('MSFullscreenChange', presentationModeChange, false);
})();


/* Copyright 2013 Rob Wu <gwnRob@gmail.com>
 * https://github.com/Rob--W/grab-to-pan.js
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

'use strict';

var GrabToPan = (function GrabToPanClosure() {
  /**
   * Construct a GrabToPan instance for a given HTML element.
   * @param options.element {Element}
   * @param options.ignoreTarget {function} optional. See `ignoreTarget(node)`
   * @param options.onActiveChanged {function(boolean)} optional. Called
   *  when grab-to-pan is (de)activated. The first argument is a boolean that
   *  shows whether grab-to-pan is activated.
   */
  function GrabToPan(options) {
    this.element = options.element;
    this.document = options.element.ownerDocument;
    if (typeof options.ignoreTarget === 'function') {
      this.ignoreTarget = options.ignoreTarget;
    }
    this.onActiveChanged = options.onActiveChanged;

    // Bind the contexts to ensure that `this` always points to
    // the GrabToPan instance.
    this.activate = this.activate.bind(this);
    this.deactivate = this.deactivate.bind(this);
    this.toggle = this.toggle.bind(this);
    this._onmousedown = this._onmousedown.bind(this);
    this._onmousemove = this._onmousemove.bind(this);
    this._endPan = this._endPan.bind(this);

    // This overlay will be inserted in the document when the mouse moves during
    // a grab operation, to ensure that the cursor has the desired appearance.
    var overlay = this.overlay = document.createElement('div');
    overlay.className = 'grab-to-pan-grabbing';
  }
  GrabToPan.prototype = {
    /**
     * Class name of element which can be grabbed
     */
    CSS_CLASS_GRAB: 'grab-to-pan-grab',

    /**
     * Bind a mousedown event to the element to enable grab-detection.
     */
    activate: function GrabToPan_activate() {
      if (!this.active) {
        this.active = true;
        this.element.addEventListener('mousedown', this._onmousedown, true);
        this.element.classList.add(this.CSS_CLASS_GRAB);
        if (this.onActiveChanged) {
          this.onActiveChanged(true);
        }
      }
    },

    /**
     * Removes all events. Any pending pan session is immediately stopped.
     */
    deactivate: function GrabToPan_deactivate() {
      if (this.active) {
        this.active = false;
        this.element.removeEventListener('mousedown', this._onmousedown, true);
        this._endPan();
        this.element.classList.remove(this.CSS_CLASS_GRAB);
        if (this.onActiveChanged) {
          this.onActiveChanged(false);
        }
      }
    },

    toggle: function GrabToPan_toggle() {
      if (this.active) {
        this.deactivate();
      } else {
        this.activate();
      }
    },

    /**
     * Whether to not pan if the target element is clicked.
     * Override this method to change the default behaviour.
     *
     * @param node {Element} The target of the event
     * @return {boolean} Whether to not react to the click event.
     */
    ignoreTarget: function GrabToPan_ignoreTarget(node) {
      // Use matchesSelector to check whether the clicked element
      // is (a child of) an input element / link
      return node[matchesSelector](
        'a[href], a[href] *, input, textarea, button, button *, select, option'
      );
    },

    /**
     * @private
     */
    _onmousedown: function GrabToPan__onmousedown(event) {
      if (event.button !== 0 || this.ignoreTarget(event.target)) {
        return;
      }
      if (event.originalTarget) {
        try {
          /* jshint expr:true */
          event.originalTarget.tagName;
        } catch (e) {
          // Mozilla-specific: element is a scrollbar (XUL element)
          return;
        }
      }

      this.scrollLeftStart = this.element.scrollLeft;
      this.scrollTopStart = this.element.scrollTop;
      this.clientXStart = event.clientX;
      this.clientYStart = event.clientY;
      this.document.addEventListener('mousemove', this._onmousemove, true);
      this.document.addEventListener('mouseup', this._endPan, true);
      // When a scroll event occurs before a mousemove, assume that the user
      // dragged a scrollbar (necessary for Opera Presto, Safari and IE)
      // (not needed for Chrome/Firefox)
      this.element.addEventListener('scroll', this._endPan, true);
      event.preventDefault();
      event.stopPropagation();
      this.document.documentElement.classList.add(this.CSS_CLASS_GRABBING);
    },

    /**
     * @private
     */
    _onmousemove: function GrabToPan__onmousemove(event) {
      this.element.removeEventListener('scroll', this._endPan, true);
      if (isLeftMouseReleased(event)) {
        this._endPan();
        return;
      }
      var xDiff = event.clientX - this.clientXStart;
      var yDiff = event.clientY - this.clientYStart;
      this.element.scrollTop = this.scrollTopStart - yDiff;
      this.element.scrollLeft = this.scrollLeftStart - xDiff;
      if (!this.overlay.parentNode) {
        document.body.appendChild(this.overlay);
      }
    },

    /**
     * @private
     */
    _endPan: function GrabToPan__endPan() {
      this.element.removeEventListener('scroll', this._endPan, true);
      this.document.removeEventListener('mousemove', this._onmousemove, true);
      this.document.removeEventListener('mouseup', this._endPan, true);
      if (this.overlay.parentNode) {
        this.overlay.parentNode.removeChild(this.overlay);
      }
    }
  };

  // Get the correct (vendor-prefixed) name of the matches method.
  var matchesSelector;
  ['webkitM', 'mozM', 'msM', 'oM', 'm'].some(function(prefix) {
    var name = prefix + 'atches';
    if (name in document.documentElement) {
      matchesSelector = name;
    }
    name += 'Selector';
    if (name in document.documentElement) {
      matchesSelector = name;
    }
    return matchesSelector; // If found, then truthy, and [].some() ends.
  });

  // Browser sniffing because it's impossible to feature-detect
  // whether event.which for onmousemove is reliable
  var isNotIEorIsIE10plus = !document.documentMode || document.documentMode > 9;
  var chrome = window.chrome;
  var isChrome15OrOpera15plus = chrome && (chrome.webstore || chrome.app);
  //                                       ^ Chrome 15+       ^ Opera 15+
  var isSafari6plus = /Apple/.test(navigator.vendor) &&
                      /Version\/([6-9]\d*|[1-5]\d+)/.test(navigator.userAgent);

  /**
   * Whether the left mouse is not pressed.
   * @param event {MouseEvent}
   * @return {boolean} True if the left mouse button is not pressed.
   *                   False if unsure or if the left mouse button is pressed.
   */
  function isLeftMouseReleased(event) {
    if ('buttons' in event && isNotIEorIsIE10plus) {
      // http://www.w3.org/TR/DOM-Level-3-Events/#events-MouseEvent-buttons
      // Firefox 15+
      // Internet Explorer 10+
      return !(event.buttons | 1);
    }
    if (isChrome15OrOpera15plus || isSafari6plus) {
      // Chrome 14+
      // Opera 15+
      // Safari 6.0+
      return event.which === 0;
    }
  }

  return GrabToPan;
})();

var HandTool = {
  initialize: function handToolInitialize(options) {
    var toggleHandTool = options.toggleHandTool;
    this.handTool = new GrabToPan({
      element: options.container,
      onActiveChanged: function(isActive) {
        if (!toggleHandTool) {
          return;
        }
        if (isActive) {
          toggleHandTool.title =
            mozL10n.get('hand_tool_disable.title', null, 'Disable hand tool');
          toggleHandTool.firstElementChild.textContent =
            mozL10n.get('hand_tool_disable_label', null, 'Disable hand tool');
        } else {
          toggleHandTool.title =
            mozL10n.get('hand_tool_enable.title', null, 'Enable hand tool');
          toggleHandTool.firstElementChild.textContent =
            mozL10n.get('hand_tool_enable_label', null, 'Enable hand tool');
        }
      }
    });
    if (toggleHandTool) {
      toggleHandTool.addEventListener('click', this.toggle.bind(this), false);

      window.addEventListener('localized', function (evt) {
        Preferences.get('enableHandToolOnLoad').then(function resolved(value) {
          if (value) {
            this.handTool.activate();
          }
        }.bind(this), function rejected(reason) {});
      }.bind(this));
    }
  },

  toggle: function handToolToggle() {
    this.handTool.toggle();
    SecondaryToolbar.close();
  },

  enterPresentationMode: function handToolEnterPresentationMode() {
    if (this.handTool.active) {
      this.wasActive = true;
      this.handTool.deactivate();
    }
  },

  exitPresentationMode: function handToolExitPresentationMode() {
    if (this.wasActive) {
      this.wasActive = null;
      this.handTool.activate();
    }
  }
};


var OverlayManager = {
  overlays: {},
  active: null,

  /**
   * @param {string} name The name of the overlay that is registered. This must
   *                 be equal to the ID of the overlay's DOM element.
   * @param {function} callerCloseMethod (optional) The method that, if present,
   *                   will call OverlayManager.close from the Object
   *                   registering the overlay. Access to this method is
   *                   necessary in order to run cleanup code when e.g.
   *                   the overlay is force closed. The default is null.
   * @param {boolean} canForceClose (optional) Indicates if opening the overlay
   *                  will close an active overlay. The default is false.
   * @returns {Promise} A promise that is resolved when the overlay has been
   *                    registered.
   */
  register: function overlayManagerRegister(name,
                                            callerCloseMethod, canForceClose) {
    return new Promise(function (resolve) {
      var element, container;
      if (!name || !(element = document.getElementById(name)) ||
          !(container = element.parentNode)) {
        throw new Error('Not enough parameters.');
      } else if (this.overlays[name]) {
        throw new Error('The overlay is already registered.');
      }
      this.overlays[name] = { element: element,
                              container: container,
                              callerCloseMethod: (callerCloseMethod || null),
                              canForceClose: (canForceClose || false) };
      resolve();
    }.bind(this));
  },

  /**
   * @param {string} name The name of the overlay that is unregistered.
   * @returns {Promise} A promise that is resolved when the overlay has been
   *                    unregistered.
   */
  unregister: function overlayManagerUnregister(name) {
    return new Promise(function (resolve) {
      if (!this.overlays[name]) {
        throw new Error('The overlay does not exist.');
      } else if (this.active === name) {
        throw new Error('The overlay cannot be removed while it is active.');
      }
      delete this.overlays[name];

      resolve();
    }.bind(this));
  },

  /**
   * @param {string} name The name of the overlay that should be opened.
   * @returns {Promise} A promise that is resolved when the overlay has been
   *                    opened.
   */
  open: function overlayManagerOpen(name) {
    return new Promise(function (resolve) {
      if (!this.overlays[name]) {
        throw new Error('The overlay does not exist.');
      } else if (this.active) {
        if (this.overlays[name].canForceClose) {
          this._closeThroughCaller();
        } else if (this.active === name) {
          throw new Error('The overlay is already active.');
        } else {
          throw new Error('Another overlay is currently active.');
        }
      }
      this.active = name;
      this.overlays[this.active].element.classList.remove('hidden');
      this.overlays[this.active].container.classList.remove('hidden');

      window.addEventListener('keydown', this._keyDown);
      resolve();
    }.bind(this));
  },

  /**
   * @param {string} name The name of the overlay that should be closed.
   * @returns {Promise} A promise that is resolved when the overlay has been
   *                    closed.
   */
  close: function overlayManagerClose(name) {
    return new Promise(function (resolve) {
      if (!this.overlays[name]) {
        throw new Error('The overlay does not exist.');
      } else if (!this.active) {
        throw new Error('The overlay is currently not active.');
      } else if (this.active !== name) {
        throw new Error('Another overlay is currently active.');
      }
      this.overlays[this.active].container.classList.add('hidden');
      this.overlays[this.active].element.classList.add('hidden');
      this.active = null;

      window.removeEventListener('keydown', this._keyDown);
      resolve();
    }.bind(this));
  },

  /**
   * @private
   */
  _keyDown: function overlayManager_keyDown(evt) {
    var self = OverlayManager;
    if (self.active && evt.keyCode === 27) { // Esc key.
      self._closeThroughCaller();
      evt.preventDefault();
    }
  },

  /**
   * @private
   */
  _closeThroughCaller: function overlayManager_closeThroughCaller() {
    if (this.overlays[this.active].callerCloseMethod) {
      this.overlays[this.active].callerCloseMethod();
    }
    if (this.active) {
      this.close(this.active);
    }
  }
};


var PasswordPrompt = {
  overlayName: null,
  updatePassword: null,
  reason: null,
  passwordField: null,
  passwordText: null,
  passwordSubmit: null,
  passwordCancel: null,

  initialize: function secondaryToolbarInitialize(options) {
    this.overlayName = options.overlayName;
    this.passwordField = options.passwordField;
    this.passwordText = options.passwordText;
    this.passwordSubmit = options.passwordSubmit;
    this.passwordCancel = options.passwordCancel;

    // Attach the event listeners.
    this.passwordSubmit.addEventListener('click',
      this.verifyPassword.bind(this));

    this.passwordCancel.addEventListener('click', this.close.bind(this));

    this.passwordField.addEventListener('keydown', function (e) {
      if (e.keyCode === 13) { // Enter key
        this.verifyPassword();
      }
    }.bind(this));

    OverlayManager.register(this.overlayName, this.close.bind(this), true);
  },

  open: function passwordPromptOpen() {
    OverlayManager.open(this.overlayName).then(function () {
      this.passwordField.focus();

      var promptString = mozL10n.get('password_label', null,
        'Enter the password to open this PDF file.');

      if (this.reason === PDFJS.PasswordResponses.INCORRECT_PASSWORD) {
        promptString = mozL10n.get('password_invalid', null,
          'Invalid password. Please try again.');
      }

      this.passwordText.textContent = promptString;
    }.bind(this));
  },

  close: function passwordPromptClose() {
    OverlayManager.close(this.overlayName).then(function () {
      this.passwordField.value = '';
    }.bind(this));
  },

  verifyPassword: function passwordPromptVerifyPassword() {
    var password = this.passwordField.value;
    if (password && password.length > 0) {
      this.close();
      return this.updatePassword(password);
    }
  }
};


var DocumentProperties = {
  overlayName: null,
  fileName: '',
  fileSize: '',

  // Document property fields (in the viewer).
  fileNameField: null,
  fileSizeField: null,
  titleField: null,
  authorField: null,
  subjectField: null,
  keywordsField: null,
  creationDateField: null,
  modificationDateField: null,
  creatorField: null,
  producerField: null,
  versionField: null,
  pageCountField: null,

  initialize: function documentPropertiesInitialize(options) {
    this.overlayName = options.overlayName;

    // Set the document property fields.
    this.fileNameField = options.fileNameField;
    this.fileSizeField = options.fileSizeField;
    this.titleField = options.titleField;
    this.authorField = options.authorField;
    this.subjectField = options.subjectField;
    this.keywordsField = options.keywordsField;
    this.creationDateField = options.creationDateField;
    this.modificationDateField = options.modificationDateField;
    this.creatorField = options.creatorField;



