// src/utils/JavaScriptHelpers.mjs
function errorCatch(err) {
  if (err.stack) {
    if (err.lineNumber) {
      console.error("ERROR[%s:%s] ", err.name, err.lineNumber, err);
    } else if (err.line) {
      console.error("ERROR[%s:%s] ", err.name, err.line, err);
    } else {
      console.error("ERROR[%s] ", err.name, err);
    }
  } else if (err.number) {
    console.error("ERROR[%s:%s] %s", err.name, err.number, err.message);
    console.error("ERROR[description] %s", err.description);
  } else {
    console.error("ERROR[%s] %s", err.name, err.message);
  }
}
function isFunction(name) {
  if (typeof window[name] !== "undefined" && typeof window[name] === "function") {
    return true;
  } else {
    return false;
  }
}
function executeFunctionByName(functionName, context) {
  var args = Array.prototype.slice.call(arguments, 2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
  if (func == void 0) {
    throw new Error("Cannot get function from namespaces: " + functionName);
  }
  for (var i = 0; i < namespaces.length; i++) {
    context = context[namespaces[i]];
  }
  return context[func].apply(context, args);
}
function runFunction(name) {
  var args = Array.prototype.slice.call(arguments, 1);
  runFunctionArgsArray(name, args);
}
function runFunctionArgsArray(name, args) {
  var fn = window[name];
  if (typeof fn !== "function") {
    return;
  }
  fn.apply(window, args);
}
function isObject(val) {
  return val !== null && typeof val === "object" && !Array.isArray(val);
}
function isArray(val) {
  return val !== null && Array.isArray(val);
}
function isIterable(val) {
  if (val == null) return false;
  if (typeof val[Symbol.iterator] === "function" && typeof val !== "string") return true;
  return typeof val === "object" && val.constructor === Object;
}
function getObjectCount(object) {
  if (!isObject(object)) {
    return -1;
  }
  return Object.keys(object).length;
}
function keyInObject(key, object) {
  return objectKeyExists(object, key);
}
function objectKeyExists(object, key) {
  return Object.prototype.hasOwnProperty.call(object, key) ? true : false;
}
function getKeyByValue(object, value) {
  return Object.keys(object).find((key) => object[key] === value) ?? "";
}
function valueInObject(object, value) {
  return objectValueExists(object, value);
}
function objectValueExists(object, value) {
  return Object.keys(object).find((key) => object[key] === value) ? true : false;
}
function deepCopyFunction(inObject) {
  var outObject, value, key;
  if (typeof inObject !== "object" || inObject === null) {
    return inObject;
  }
  outObject = Array.isArray(inObject) ? [] : {};
  for (key in inObject) {
    value = inObject[key];
    outObject[key] = deepCopyFunction(value);
  }
  return outObject;
}

// src/utils/DomHelpers.mjs
function loadEl(el_id) {
  let el = document.getElementById(el_id);
  if (el === null) {
    throw new Error("Cannot find: " + el_id);
  }
  return el;
}
function pop(theURL, winName, features) {
  let __winName = window.open(theURL, winName, features);
  if (__winName == null) {
    return;
  }
  __winName.focus();
}
function expandTA(ta_id) {
  let ta = this.loadEl(ta_id);
  if (ta instanceof HTMLElement && ta.getAttribute("type") !== "textarea") {
    throw new Error("Element is not a textarea: " + ta_id);
  }
  let maxChars = parseInt(ta.getAttribute("cols") ?? "0");
  let ta_value = ta.getAttribute("value");
  let theRows = [];
  if (ta_value != null) {
    theRows = ta_value.split("\n");
  }
  var numNewRows = 0;
  for (var i = 0; i < theRows.length; i++) {
    if (theRows[i].length + 2 > maxChars) {
      numNewRows += Math.ceil((theRows[i].length + 2) / maxChars);
    }
  }
  ta.setAttribute("row", (numNewRows + theRows.length).toString());
}
function exists(id) {
  return $("#" + id).length > 0 ? true : false;
}

// src/utils/HtmlElementCreator.mjs
var HtmlElementCreator = class {
  /**
   * reates object for DOM element creation flow
   * @param  {String} tag          must set tag (div, span, etc)
   * @param  {String} [id='']      optional set for id, if input, select will be used for name
   * @param  {String} [content=''] text content inside, is skipped if sub elements exist
   * @param  {Array}  [css=[]]     array for css tags
   * @param  {Object} [options={}] anything else (value, placeholder, OnClick, style)
   * @return {Object}              created element as an object
   */
  cel(tag, id = "", content = "", css = [], options = {}) {
    return {
      tag,
      id,
      // override name if set, else id is used. Only for input/button
      name: options.name,
      content,
      css,
      options,
      sub: []
    };
  }
  /**
   * attach a cel created object to another to create a basic DOM tree
   * @param  {Object} base    object where to attach/search
   * @param  {Object} attach  the object to be attached
   * @param  {String} [id=''] optional id, if given search in base for this id and attach there
   * @return {Object}         "none", technically there is no return needed as it is global attach
   */
  ael(base, attach, id = "") {
    if (id) {
      if (base.id == id) {
        base.sub.push(deepCopyFunction(attach));
      } else {
        if (isArray(base.sub) && base.sub.length > 0) {
          for (var i = 0; i < base.sub.length; i++) {
            this.ael(base.sub[i], attach, id);
          }
        }
      }
    } else {
      base.sub.push(deepCopyFunction(attach));
    }
    return base;
  }
  /**
   * directly attach n elements to one master base element
   * this type does not support attach with optional id
   * @param  {Object}    base   object to where we attach the elements
   * @param  {...Object} attach attach 1..n: attach directly to the base element those attachments
   * @return {Object}           "none", technically there is no return needed, global attach
   */
  aelx(base, ...attach) {
    for (var i = 0; i < attach.length; i++) {
      base.sub.push(deepCopyFunction(attach[i]));
    }
    return base;
  }
  /**
   * same as aelx, but instead of using objects as parameters
   * get an array of objects to attach
   * @param  {Object} base   object to where we attach the elements
   * @param  {Array}  attach array of objects to attach
   * @return {Object}        "none", technically there is no return needed, global attach
   */
  aelxar(base, attach) {
    for (var i = 0; i < attach.length; i++) {
      base.sub.push(deepCopyFunction(attach[i]));
    }
    return base;
  }
  /**
   * resets the sub elements of the base element given
   * @param  {Object} base cel created element
   * @return {Object}      returns reset base element
   */
  rel(base) {
    base.sub = [];
    return base;
  }
  /**
   * searches and removes style from css array
   * @param  {Object} _element element to work one
   * @param  {String} css      style sheet to remove (name)
   * @return {Object}          returns full element
   */
  rcssel(_element, css) {
    var css_index = _element.css.indexOf(css);
    if (css_index > -1) {
      _element.css.splice(css_index, 1);
    }
    return _element;
  }
  /**
   * adds a new style sheet to the element given
   * @param  {Object} _element element to work on
   * @param  {String} css      style sheet to add (name)
   * @return {Object}         returns full element
   */
  acssel(_element, css) {
    var css_index = _element.css.indexOf(css);
    if (css_index == -1) {
      _element.css.push(css);
    }
    return _element;
  }
  /**
   * removes one css and adds another
   * is a wrapper around rcssel/acssel
   * @param  {Object} _element element to work on
   * @param  {String} rcss     style to remove (name)
   * @param  {String} acss     style to add (name)
   * @return {Object}          returns full element
   */
  scssel(_element, rcss, acss) {
    this.rcssel(_element, rcss);
    this.acssel(_element, acss);
    return _element;
  }
  /**
   * parses the object tree created with cel/ael and converts it into an HTML string
   * that can be inserted into the page
   * @param  {Object} tree object tree with dom element declarations
   * @return {String}      HTML string that can be used as innerHTML
   */
  phfo(tree) {
    let name_elements = [
      "button",
      "fieldset",
      "form",
      "iframe",
      "input",
      "map",
      "meta",
      "object",
      "output",
      "param",
      "select",
      "textarea"
    ];
    let skip_options = [
      "id",
      "name",
      "class"
    ];
    let no_close = [
      "input",
      "br",
      "img",
      "hr",
      "area",
      "col",
      "keygen",
      "wbr",
      "track",
      "source",
      "param",
      "command",
      // only in header
      "base",
      "meta",
      "link",
      "embed"
    ];
    var content = [];
    var line = "<" + tree.tag;
    var i;
    if (tree.id) {
      line += ' id="' + tree.id + '"';
      if (name_elements.includes(tree.tag)) {
        line += ' name="' + (tree.name ? tree.name : tree.id) + '"';
      }
    }
    if (isArray(tree.css) && tree.css.length > 0) {
      line += ' class="';
      for (i = 0; i < tree.css.length; i++) {
        line += tree.css[i] + " ";
      }
      line = line.slice(0, -1);
      line += '"';
    }
    if (isObject(tree.options)) {
      for (const [key, item] of Object.entries(tree.options)) {
        if (!skip_options.includes(key)) {
          line += " " + key + '="' + item + '"';
        }
      }
    }
    line += ">";
    content.push(line);
    if (isArray(tree.sub) && tree.sub.length > 0) {
      if (tree.content) {
        content.push(tree.content);
      }
      for (i = 0; i < tree.sub.length; i++) {
        content.push(this.phfo(tree.sub[i]));
      }
    } else if (tree.content) {
      content.push(tree.content);
    }
    if (!no_close.includes(tree.tag)) {
      content.push("</" + tree.tag + ">");
    }
    return content.join("");
  }
  /**
   * Create HTML elements from array list
   * as a flat element without master object file
   * Is like tree.sub call
   * @param  {Array}  list Array of cel created objects
   * @return {String}      HTML String
   */
  phfa(list) {
    var content = [];
    for (var i = 0; i < list.length; i++) {
      content.push(this.phfo(list[i]));
    }
    return content.join("");
  }
};

// src/utils/HtmlHelpers.mjs
var dom = new HtmlElementCreator();
function escapeHtml(string) {
  return string.replace(/[&<>"'/]/g, function(s) {
    var entityMap = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
      "/": "&#x2F;"
    };
    return entityMap[s];
  });
}
function unescapeHtml(string) {
  return string.replace(/&[#\w]+;/g, function(s) {
    var entityMap = {
      "&amp;": "&",
      "&lt;": "<",
      "&gt;": ">",
      "&quot;": '"',
      "&#39;": "'",
      "&#x2F;": "/"
    };
    return entityMap[s];
  });
}
function html_options(name, data, selected = "", options_only = false, return_string = false, sort = "") {
  return this.html_options_block(
    name,
    data,
    selected,
    0,
    options_only,
    return_string,
    sort
  );
}
function html_options_block(name, data, selected = "", multiple = 0, options_only = false, return_string = false, sort = "", onchange = "") {
  var content = [];
  var element_select;
  var select_options = {};
  var element_option;
  var data_list = [];
  var value;
  var options = {};
  if (multiple > 0) {
    select_options.multiple = "";
    if (multiple > 1) {
      select_options.size = multiple;
    }
  }
  if (onchange) {
    select_options.OnChange = onchange;
  }
  element_select = dom.cel("select", name, "", [], select_options);
  if (sort == "keys") {
    data_list = Object.keys(data).sort();
  } else if (sort == "values") {
    data_list = Object.keys(data).sort((a, b) => ("" + data[a]).localeCompare(data[b]));
  } else {
    data_list = Object.keys(data);
  }
  for (const key of data_list) {
    value = data[key];
    options = {
      "label": value,
      "value": key,
      "selected": ""
    };
    if (multiple == 0 && !Array.isArray(selected) && selected == key) {
      options.selected = "";
    }
    if (multiple == 1 && Array.isArray(selected) && selected.indexOf(key) != -1) {
      options.selected = "";
    }
    element_option = dom.cel("option", "", value, [], options);
    dom.ael(element_select, element_option);
  }
  if (!options_only) {
    if (return_string) {
      content.push(dom.phfo(element_select));
      return content.join("");
    } else {
      return element_select;
    }
  } else {
    if (return_string) {
      for (var i = 0; i < element_select.sub.length; i++) {
        content.push(dom.phfo(element_select.sub[i]));
      }
      return content.join("");
    } else {
      return element_select.sub;
    }
  }
}
function html_options_refill(name, data, sort = "") {
  var element_option;
  var option_selected;
  var data_list = [];
  var value;
  if (document.getElementById(name)) {
    if (sort == "keys") {
      data_list = Object.keys(data).sort();
    } else if (sort == "values") {
      data_list = Object.keys(data).sort((a, b) => ("" + data[a]).localeCompare(data[b]));
    } else {
      data_list = Object.keys(data);
    }
    [].forEach.call(document.querySelectorAll("#" + name + " :checked"), function(elm) {
      option_selected = elm.value;
    });
    loadEl(name).innerHTML = "";
    for (const key of data_list) {
      value = data[key];
      element_option = document.createElement("option");
      element_option.label = value;
      element_option.value = key;
      element_option.innerHTML = value;
      if (key == option_selected) {
        element_option.selected = true;
      }
      loadEl(name).appendChild(element_option);
    }
  }
}

// src/utils/MathHelpers.mjs
function dec2hex(dec) {
  return ("0x" + dec.toString(16)).substring(-2);
}
function getRandomIntInclusive(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1) + min);
}
function roundPrecision(number, precision) {
  if (isNaN(number) || isNaN(precision)) {
    return number;
  }
  return Math.round(number * Math.pow(10, precision)) / Math.pow(10, precision);
}

// src/utils/StringHelpers.mjs
function formatString(string, ...args) {
  return string.replace(/{(\d+)}/g, function(match, number) {
    return typeof args[number] != "undefined" ? args[number] : match;
  });
}
function numberWithCommas(number) {
  var parts = number.toString().split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}
function convertLBtoBR(string) {
  return string.replace(/(?:\r\n|\r|\n)/g, "<br>");
}

// src/utils/DateTimeHelpers.mjs
function getTimestamp() {
  var date = /* @__PURE__ */ new Date();
  return date.getTime();
}

// src/utils/UniqIdGenerators.mjs
function generateId(len) {
  var arr = new Uint8Array((len || 40) / 2);
  (window.crypto || // @ts-ignore
  window.msCrypto).getRandomValues(arr);
  return Array.from(arr, self.dec2hex).join("");
}
function randomIdF() {
  return Math.random().toString(36).substring(2);
}

// src/utils/ResizingAndMove.mjs
function getWindowSize() {
  var width, height;
  width = window.innerWidth || (window.document.documentElement.clientWidth || window.document.body.clientWidth);
  height = window.innerHeight || (window.document.documentElement.clientHeight || window.document.body.clientHeight);
  return {
    width,
    height
  };
}
function getScrollOffset() {
  var left, top;
  left = window.pageXOffset || (window.document.documentElement.scrollLeft || window.document.body.scrollLeft);
  top = window.pageYOffset || (window.document.documentElement.scrollTop || window.document.body.scrollTop);
  return {
    left,
    top
  };
}
function getScrollOffsetOpener() {
  var left, top;
  left = opener.window.pageXOffset || (opener.document.documentElement.scrollLeft || opener.document.body.scrollLeft);
  top = opener.window.pageYOffset || (opener.document.documentElement.scrollTop || opener.document.body.scrollTop);
  return {
    left,
    top
  };
}
function setCenter(id, left, top) {
  var dimensions = {
    height: $("#" + id).height() ?? 0,
    width: $("#" + id).width() ?? 0
  };
  var type = $("#" + id).css("position");
  var viewport = this.getWindowSize();
  var offset = this.getScrollOffset();
  if (left) {
    $("#" + id).css({
      left: viewport.width / 2 - dimensions.width / 2 + offset.left + "px"
    });
  }
  if (top) {
    var top_pos = type == "fixed" ? viewport.height / 2 - dimensions.height / 2 : viewport.height / 2 - dimensions.height / 2 + offset.top;
    $("#" + id).css({
      top: top_pos + "px"
    });
  }
}
function goToPos(element, offset = 0, duration = 500, base = "body,html") {
  try {
    let element_offset = $("#" + element).offset();
    if (element_offset == void 0) {
      return;
    }
    if ($("#" + element).length) {
      $(base).animate({
        scrollTop: element_offset.top - offset
      }, duration);
    }
  } catch (err) {
    errorCatch(err);
  }
}
function goTo(target) {
  loadEl(target).scrollIntoView({
    behavior: "smooth"
  });
}

// src/utils/FormatBytes.mjs
function formatBytes(bytes) {
  var i = -1;
  if (typeof bytes === "bigint") {
    bytes = Number(bytes);
  }
  if (isNaN(bytes)) {
    return bytes.toString();
  }
  do {
    bytes = bytes / 1024;
    i++;
  } while (bytes > 99);
  return Math.round(bytes * Math.pow(10, 2)) / Math.pow(10, 2) + ["kB", "MB", "GB", "TB", "PB", "EB"][i];
}
function formatBytesLong(bytes) {
  if (typeof bytes === "bigint") {
    bytes = Number(bytes);
  }
  if (isNaN(bytes)) {
    return bytes.toString();
  }
  let negative = false;
  if (bytes < 0) {
    negative = true;
    bytes *= -1;
  }
  var i = Math.floor(Math.log(bytes) / Math.log(1024));
  var sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
  return (negative ? "-" : "") + ((bytes / Math.pow(1024, i)).toFixed(2) + " " + sizes[i]).toString();
}
function stringByteFormat(bytes, raw = false) {
  if (!(typeof bytes === "string" || bytes instanceof String)) {
    return bytes.toString();
  }
  let valid_units = "bkmgtpezy";
  let regex = /([\d.,]*)\s?(eb|pb|tb|gb|mb|kb|e|p|t|g|m|k|b)$/i;
  let matches = bytes.match(regex);
  if (matches !== null) {
    let m1 = parseFloat(matches[1].replace(/[^0-9.]/, ""));
    let m2 = matches[2].replace(/[^bkmgtpezy]/i, "").charAt(0).toLowerCase();
    if (m2) {
      bytes = m1 * Math.pow(1024, valid_units.indexOf(m2));
    }
  }
  if (raw) {
    return bytes;
  }
  return Math.round(bytes);
}

// src/utils/UrlParser.mjs
function parseQueryString(query = "", return_key = "", single = false) {
  return getQueryStringParam(return_key, query, single);
}
function getQueryStringParam(search = "", query = "", single = false) {
  if (!query) {
    query = window.location.href;
  }
  const url = new URL(query);
  let param = null;
  if (search) {
    let _params = url.searchParams.getAll(search);
    if (_params.length == 1 || single === true) {
      param = _params[0];
    } else if (_params.length > 1) {
      param = _params;
    }
  } else {
    param = {};
    for (const [key] of url.searchParams.entries()) {
      if (typeof param[key] === "undefined") {
        let _params = url.searchParams.getAll(key);
        param[key] = _params.length < 2 || single === true ? _params[0] : _params;
      }
    }
  }
  return param;
}
function hasUrlParameter(key) {
  var urlParams = new URLSearchParams(window.location.search);
  return urlParams.has(key);
}
function getUrlParameter(key) {
  var urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(key);
}
function updateUrlParameter(key, value, reload = false) {
  const url = new URL(window.location.href);
  url.searchParams.set(key, value);
  const newUrl = url.toString();
  window.history.pushState({ path: newUrl }, "", newUrl);
  if (reload) {
    window.location.reload();
  }
}
function removeUrlParameter(key, reload = false) {
  const url = new URL(window.location.href);
  url.searchParams.delete(key);
  window.history.pushState({}, "", url.toString());
  if (reload) {
    window.location.reload();
  }
}

// src/utils/LoginLogout.mjs
function loginLogout() {
  const form = document.createElement("form");
  form.method = "post";
  const hiddenField = document.createElement("input");
  hiddenField.type = "hidden";
  hiddenField.name = "login_logout";
  hiddenField.value = "Logout";
  form.appendChild(hiddenField);
  document.body.appendChild(form);
  form.submit();
}

// src/utils/ActionIndicatorOverlayBox.mjs
function actionIndicator(loc, overlay = true) {
  if ($("#indicator").is(":visible")) {
    this.actionIndicatorHide(loc, overlay);
  } else {
    this.actionIndicatorShow(loc, overlay);
  }
}
function actionIndicatorShow(loc, overlay = true) {
  if (!$("#indicator").is(":visible")) {
    if (!$("#indicator").hasClass("progress")) {
      $("#indicator").addClass("progress");
    }
    setCenter("indicator", true, true);
    $("#indicator").show();
  }
  if (overlay === true) {
    this.overlayBoxShow();
  }
}
function actionIndicatorHide(loc, overlay = true) {
  $("#indicator").hide();
  if (overlay === true) {
    overlayBoxHide();
  }
}
function overlayBoxShow() {
  if ($("#overlayBox").is(":visible")) {
    $("#overlayBox").css("zIndex", "100");
  } else {
    $("#overlayBox").show();
    $("#overlayBox").css("zIndex", "98");
  }
}
function overlayBoxHide() {
  if (parseInt($("#overlayBox").css("zIndex")) >= 100) {
    $("#overlayBox").css("zIndex", "98");
  } else {
    $("#overlayBox").hide();
  }
}
function setOverlayBox() {
  if (!$("#overlayBox").is(":visible")) {
    $("#overlayBox").show();
  }
}
function hideOverlayBox() {
  if ($("#overlayBox").is(":visible")) {
    $("#overlayBox").hide();
  }
}
function ClearCall() {
  $("#actionBox").html("");
  $("#actionBox").hide();
  $("#overlayBox").hide();
}
var ActionIndicatorOverlayBox = class {
  // open overlay boxes counter for z-index
  #GL_OB_S = 100;
  #GL_OB_BASE = 100;
  /**
   * show action indicator
   * - checks if not existing and add
   * - only shows if not visible (else ignore)
   * - overlaybox check is called and shown on a fixzed
   *   zIndex of 1000
   * - indicator is page centered
   * @param {String} loc ID string, only used for console log
   */
  showActionIndicator(loc) {
    if ($("#indicator").length == 0) {
      var el = document.createElement("div");
      el.className = "progress hide";
      el.id = "indicator";
      $("body").append(el);
    } else if (!$("#indicator").hasClass("progress")) {
      $("#indicator").addClass("progress").hide();
    }
    if (!$("#indicator").is(":visible")) {
      this.checkOverlayExists();
      if (!$("#overlayBox").is(":visible")) {
        $("#overlayBox").show();
      }
      $("#overlayBox").css("zIndex", 1e3);
      $("#indicator").show();
      setCenter("indicator", true, true);
    }
  }
  /**
   * hide action indicator, if it is visiable
   * If the global variable GL_OB_S is > GL_OB_BASE then
   * the overlayBox is not hidden but the zIndex
   * is set to this value
   * @param {String} loc ID string, only used for console log
   */
  hideActionIndicator(loc) {
    if ($("#indicator").is(":visible")) {
      $("#indicator").hide();
      if (this.#GL_OB_S > this.#GL_OB_BASE) {
        $("#overlayBox").css("zIndex", this.#GL_OB_S);
      } else {
        $("#overlayBox").hide();
        $("#overlayBox").css("zIndex", this.#GL_OB_BASE);
      }
    }
  }
  /**
   * checks if overlayBox exists, if not it is
   * added as hidden item at the body end
   */
  checkOverlayExists() {
    if ($("#overlayBox").length == 0) {
      var el = document.createElement("div");
      el.className = "overlayBoxElement hide";
      el.id = "overlayBox";
      $("body").append(el);
    }
  }
  /**
   * show overlay box
   * if not visible show and set zIndex to 10 (GL_OB_BASE)
   * if visible, add +1 to the GL_OB_S variable and
   * up zIndex by this value
   */
  showOverlayBoxLayers(el_id) {
    if (!$("#overlayBox").is(":visible")) {
      $("#overlayBox").show();
      $("#overlayBox").css("zIndex", this.#GL_OB_BASE);
      this.#GL_OB_S = this.#GL_OB_BASE;
    }
    this.#GL_OB_S++;
    $("#overlayBox").css("zIndex", this.#GL_OB_S);
    if (el_id) {
      if ($("#" + el_id).length > 0) {
        $("#" + el_id).css("zIndex", this.#GL_OB_S + 1);
        $("#" + el_id).show();
      }
    }
  }
  /**
   * hide overlay box
   * lower GL_OB_S value by -1
   * if we are 10 (GL_OB_BASE) or below hide the overlayIndex
   * and set zIndex and GL_OB_S to 0
   * else just set zIndex to the new GL_OB_S value
   * @param {String} el_id Target to hide layer
   */
  hideOverlayBoxLayers(el_id = "") {
    this.#GL_OB_S--;
    if (this.#GL_OB_S <= this.#GL_OB_BASE) {
      this.#GL_OB_S = this.#GL_OB_BASE;
      $("#overlayBox").hide();
      $("#overlayBox").css("zIndex", this.#GL_OB_BASE);
    } else {
      $("#overlayBox").css("zIndex", this.#GL_OB_S);
    }
    if (el_id) {
      $("#" + el_id).hide();
      $("#" + el_id).css("zIndex", 0);
    }
  }
  /**
   * only for single action box
   */
  clearCallActionBox() {
    $("#actionBox").html("");
    $("#actionBox").hide();
    this.hideOverlayBoxLayers();
  }
};

// src/utils/l10nTranslation.mjs
var l10nTranslation = class {
  #i18n = {};
  constructor(i18n2) {
    this.#i18n = i18n2;
  }
  /**
   * uses the i18n object created in the translation template
   * that is filled from gettext in PHP
   * @param  {String} string text to translate
   * @return {String}        translated text (based on PHP selected language)
   */
  __(string) {
    if (typeof this.#i18n !== "undefined" && isObject(this.#i18n) && this.#i18n[string]) {
      return this.#i18n[string];
    } else {
      return string;
    }
  }
};

// src/utils/ActionBox.mjs
var ActionBox = class {
  // open overlay boxes counter for z-index
  zIndex = {
    base: 100,
    max: 110,
    indicator: 0,
    boxes: {},
    active: [],
    top: ""
  };
  // general action box storage
  action_box_storage = {};
  // set to 10 min (*60 for seconds, *1000 for microseconds)
  action_box_cache_timeout = 10 * 60 * 1e3;
  hec;
  l10n;
  /**
   * action box creator
   * @param {Object} hec  HtmlElementCreator
   * @param {Object} l10n l10nTranslation
   */
  constructor(hec2, l10n2) {
    this.hec = hec2;
    this.l10n = l10n2;
  }
  /**
   * Show an action box
   * @param {string} [target_id='actionBox'] where to attach content to, if not exists, create new
   * @param {string} [content='']            content to add to the box
   * @param {array}  [action_box_css=[]]     additional css elements for the action box
   * @param {number} [override=0]            override size adjust
   * @param {number} [content_override=0]    override content size adjust
   */
  showFillActionBox(target_id = "actionBox", content = "", action_box_css = [], override = 0, content_override = 0) {
    this.fillActionBox(target_id, content, action_box_css);
    this.showActionBox(target_id, override, content_override);
  }
  /**
   * Fill action box with content, create it if it does not existgs
   * @param {string} [target_id='actionBox'] where to attach content to, if not exists, create new
   * @param {string} [content='']            content to add to the box
   * @param {array}  [action_box_css=[]]     additional css elements for the action box
   */
  fillActionBox(target_id = "actionBox", content = "", action_box_css = []) {
    if (!exists(target_id)) {
      $("#mainContainer").after(
        this.hec.phfo(this.hec.cel("div", target_id, "", ["actionBoxElement", "hide"].concat(action_box_css)))
      );
    }
    $("#" + target_id).html(content);
  }
  /**
   * Adjust the size of the action box
   * @param {string} [target_id='actionBox'] which actionBox to work on
   * @param {number} [override=0]            override size adjust
   * @param {number} [content_override=0]    override content size adjust
   */
  adjustActionBox(target_id = "actionBox", override = 0, content_override = 0) {
    this.adjustActionBoxHeight(target_id, override, content_override);
    setCenter(target_id, true, true);
  }
  /**
   * hide any open action boxes and hide overlay
   */
  hideAllActionBoxes() {
    $('#actionBox, div[id^="actionBox-"].actionBoxElement').hide();
    $("#overlayBox").hide();
  }
  /**
   * hide action box, but do not clear content
   * DEPRECATED
   * @param {string} [target_id='actionBox']
   */
  hideActionBox(target_id = "actionBox") {
    this.closeActionBoxFloat(target_id, false);
  }
  /**
   * Just show and adjust the box
   * DEPRECAED
   * @param {string}  [target_id='actionBox'] which actionBox to work on
   * @param {number}  [override=0]            override size adjust
   * @param {number}  [content_override=0]    override content size adjust
   * @param {Boolean} [hide_all=false]        if set to true, hide all other action boxes
   */
  showActionBox(target_id = "actionBox", override = 0, content_override = 0, hide_all = true) {
    this.showActionBoxFloat(target_id, override, content_override, hide_all);
  }
  /**
   * close an action box with default clear content
   * for just hide use hideActionBox
   * DEPRECATED
   * @param {String}  [target_id='actionBox'] which action box to close, default is set
   * @param {Boolean} [clean=true]            if set to false will not remove html content, just hide
   */
  closeActionBox(target_id = "actionBox", clean = true) {
    this.closeActionBoxFloat(target_id, clean);
  }
  /**
   * TODO: better stacked action box: OPEN
   * @param {string}  [target_id='actionBox'] which actionBox to work on
   * @param {number}  [override=0]            override size adjust
   * @param {number}  [content_override=0]    override content size adjust
   * @param {boolean} [hide_all=false]        if set to true, hide all other action boxes
   */
  showActionBoxFloat(target_id = "actionBox", override = 0, content_override = 0, hide_all = false) {
    if (hide_all === true) {
      this.hideAllActionBoxes();
    }
    if (!exists("overlayBox")) {
      $("body").prepend(this.hec.phfo(this.hec.cel("div", "overlayBox", "", ["overlayBoxElement"])));
      $("#overlayBox").css("zIndex", this.zIndex.base);
    }
    $("#overlayBox").show();
    if (!objectKeyExists(this.zIndex.boxes, target_id)) {
      this.zIndex.boxes[target_id] = this.zIndex.max;
      this.zIndex.max += 10;
    } else if (this.zIndex.boxes[target_id] + 10 < this.zIndex.max) {
      this.zIndex.boxes[target_id] = this.zIndex.max;
      this.zIndex.max += 10;
    }
    if (!this.zIndex.indicator) {
      $("#overlayBox").css("zIndex", this.zIndex.boxes[target_id] - 1);
    }
    $("#" + target_id).css("zIndex", this.zIndex.boxes[target_id]).show();
    if (this.zIndex.active.indexOf(target_id) == -1) {
      this.zIndex.active.push(target_id);
    }
    this.zIndex.top = target_id;
    this.adjustActionBox(target_id, override, content_override);
  }
  /**
   * TODO: better stacked action box: CLOSE
   * @param {String}  [target_id='actionBox']  which action box to close, default is set
   * @param {Boolean} [clean=true]             if set to false will not remove html content, just hide
   */
  closeActionBoxFloat(target_id = "actionBox", clean = true) {
    if (!exists(target_id)) {
      return;
    }
    if (objectKeyExists(this.action_box_storage, target_id) && clean === true) {
      this.action_box_storage[target_id] = {};
    }
    if (clean === true) {
      $("#" + target_id).html("");
    }
    $("#" + target_id).hide();
    let idx = this.zIndex.active.indexOf(target_id);
    this.zIndex.active.splice(idx, 1);
    let visible_zIndexes = $('#actionBox:visible, div[id^="actionBox-"].actionBoxElement:visible').map((i, el) => ({
      id: el.id,
      zIndex: $("#" + el.id).css("zIndex")
    })).get();
    if (visible_zIndexes.length > 0) {
      let max_zIndex = 0;
      let max_el_id = "";
      for (let zIndex_el of visible_zIndexes) {
        if (parseInt(zIndex_el.zIndex) > max_zIndex) {
          max_zIndex = parseInt(zIndex_el.zIndex);
          max_el_id = zIndex_el.id;
        }
      }
      $("#overlayBox").css("zIndex", max_zIndex - 1);
      this.zIndex.top = max_el_id;
    } else {
      $("#overlayBox").hide();
    }
  }
  /**
   * create a new action box and fill it with basic elements
   * @param {String}  [target_id='actionBox']
   * @param {String}  [title='']
   * @param {Object}  [contents={}]
   * @param {Object}  [headers={}]
   * @param {Boolean} [show_close=true]
   * @param {Object}  [settings={}]     Optional settings, eg style sheets
   */
  createActionBox(target_id = "actionBox", title = "", contents = {}, headers = {}, settings = {}, show_close = true) {
    if (!objectKeyExists(this.action_box_storage, target_id)) {
      this.action_box_storage[target_id] = {};
    }
    let header_css = [];
    if (objectKeyExists(settings, "header_css")) {
      header_css = settings.header_css;
    }
    let action_box_css = [];
    if (objectKeyExists(settings, "action_box_css")) {
      action_box_css = settings.action_box_css;
    }
    let elements = [];
    elements.push(this.hec.phfo(
      this.hec.aelx(
        this.hec.cel("div", target_id + "_title", "", ["actionBoxTitle", "flx-spbt"].concat(header_css)),
        ...show_close === true ? [
          // title
          this.hec.cel("div", "", title, ["fs-b", "w-80"]),
          // close button
          this.hec.aelx(
            this.hec.cel("div", target_id + "_title_close_button", "", ["w-20", "tar"]),
            this.hec.cel(
              "input",
              target_id + "_title_close",
              "",
              ["button-close", "fs-s"],
              {
                type: "button",
                value: this.l10n.__("Close"),
                OnClick: "closeActionBox('" + target_id + "', false);"
              }
            )
          )
        ] : [
          this.hec.cel("div", "", title, ["fs-b", "w-100"])
        ]
      )
    ));
    if (getObjectCount(headers) > 0) {
      if (objectKeyExists(headers, "raw_string")) {
        elements.push(headers.raw_string);
      } else {
        elements.push(this.hec.phfo(headers));
      }
    }
    if (getObjectCount(contents) > 0) {
      if (objectKeyExists(contents, "raw_string")) {
        elements.push(contents.raw_string);
      } else {
        elements.push(this.hec.phfo(contents));
      }
    } else {
      elements.push(this.hec.phfo(this.hec.cel("div", target_id + "_content", "", [])));
    }
    elements.push(this.hec.phfo(
      this.hec.aelx(
        this.hec.cel("div", target_id + "_footer", "", ["pd-5", "flx-spbt"]),
        ...show_close === true ? [
          // dummy spacer
          this.hec.cel("div", "", "", ["fs-b", "w-80"]),
          // close button
          this.hec.aelx(
            this.hec.cel("div", target_id + "_footer_close_button", "", ["tar", "w-20"]),
            this.hec.cel(
              "input",
              target_id + "_footer_close",
              "",
              ["button-close", "fs-s"],
              {
                type: "button",
                value: this.l10n.__("Close"),
                OnClick: "closeActionBox('" + target_id + "', false);"
              }
            )
          )
        ] : [
          this.hec.cel("div", "", "", ["fs-b", "w-100"])
        ]
      )
    ));
    elements.push(this.hec.phfo(this.hec.cel("input", target_id + "-cache_time", "", [], {
      type: "hidden",
      value: Date.now()
    })));
    this.fillActionBox(target_id, elements.join(""), action_box_css);
  }
  /**
   * adjusts the action box height based on content and window height of browser
   * TODO: border on outside/and other margin things need to be added in overall adjustment
   * @param {String} [target_id='actionBox'] target id, if not set, fall back to default
   * @param {Number} [override=0]            override value to add to the actionBox height
   * @param {Number} [content_override=0]    override the value from _content block if it exists
   */
  adjustActionBoxHeight(target_id = "actionBox", override = 0, content_override = 0) {
    var new_height = 0;
    var dim = {};
    var abc_dim = {};
    var content_id = "";
    if (isNaN(override)) {
      override = 0;
    }
    if (isNaN(content_override)) {
      content_override = 0;
    }
    switch (target_id) {
      case "actionBox":
        content_id = "action_box";
        break;
      case "actionBoxSub":
        content_id = "action_box_sub";
        break;
      default:
        content_id = target_id;
        break;
    }
    $.each([target_id, content_id + "_content"], function(i, v) {
      $("#" + v).css({
        "height": "",
        "width": ""
      });
    });
    if (exists(content_id + "_title")) {
      dim.height = $("#" + content_id + "_title").outerHeight();
      console.log("Target: %s, Action box Title: %s", target_id, dim.height);
      new_height += dim.height ?? 0;
    }
    if (exists(content_id + "_header")) {
      dim.height = $("#" + content_id + "_header").outerHeight();
      console.log("Target: %s, Action box Header: %s", target_id, dim.height);
      new_height += dim.height ?? 0;
    }
    if (exists(content_id + "_content")) {
      if (content_override > 0) {
        console.log("Target: %s, Action box Content Override: %s", target_id, content_override);
        new_height += content_override;
      } else {
        abc_dim.height = $("#" + content_id + "_content").outerHeight();
        console.log("Target: %s, Action box Content: %s", target_id, abc_dim.height);
        new_height += abc_dim.height ?? 0;
      }
    }
    if (exists(content_id + "_footer")) {
      dim.height = $("#" + content_id + "_footer").outerHeight();
      console.log("Target: %s, Action box Footer: %s", target_id, dim.height);
      new_height += dim.height ?? 0;
    }
    new_height += override;
    var viewport = getWindowSize();
    if (new_height >= viewport.height) {
      if (exists(content_id + "_content")) {
        if (!$("#" + content_id + "_content").hasClass("of-s-y")) {
          $("#" + content_id + "_content").addClass("of-s-y");
        }
      }
      console.log("Target: %s, Viewport: %s, ActionBox (NH): %s, ABcontent: %s, ABouter: %s", target_id, viewport.height, new_height, abc_dim.height, $("#" + target_id).outerHeight());
      var m_height = viewport.height - (new_height - (abc_dim.height ?? 0));
      console.log("Target: %s, New ABcontent: %s", target_id, m_height);
      $("#" + content_id + "_content").css("height", m_height + "px");
      new_height = new_height - (abc_dim.height ?? 0) + m_height;
      console.log("Target: %s, New Hight: %s", target_id, new_height);
    } else {
      if (exists(content_id + "_content")) {
        if ($("#" + content_id + "_content").hasClass("of-s-y")) {
          $("#" + content_id + "_content").removeClass("of-s-y");
        }
      }
    }
    console.log("Target: %s, Action Box new height: %s px (override %s px, content override %s px), window height: %s px, Visible Height: %s px", target_id, new_height, override, content_override, viewport.height, $("#" + content_id).outerHeight());
    $("#" + target_id).css("height", new_height + "px");
  }
};

// src/utils/LoginNavMenu.mjs
var LoginNavMenu = class {
  hec;
  l10n;
  /**
   * action box creator
   * @param {Object} hec  HtmlElementCreator
   * @param {Object} l10n l10nTranslation
   */
  constructor(hec2, l10n2) {
    this.hec = hec2;
    this.l10n = l10n2;
  }
  /**
   * create login string and logout button elements
   * @param {String} login_string             the login string to show on the left
   * @param {String} [header_id='mainHeader'] the target for the main element block
   *                                          if not set mainHeader is assumed
   *                                          this is the target div for the "loginRow"
   */
  createLoginRow(login_string, header_id = "mainHeader") {
    if (exists(header_id)) {
      if (!exists("loginRow")) {
        $("#" + header_id).html(this.hec.phfo(this.hec.cel("div", "loginRow", "", ["loginRow", "flx-spbt"])));
      }
      $("#loginRow").html(this.hec.phfo(this.hec.cel("div", "loginRow-name", login_string)));
      $("#loginRow").append(this.hec.phfo(this.hec.cel("div", "loginRow-info", "")));
      $("#loginRow").append(this.hec.phfo(
        this.hec.aelx(
          // outer div
          this.hec.cel("div", "loginRow-logout"),
          // inner element
          this.hec.cel("input", "logout", "", [], {
            value: this.l10n.__("Logout"),
            type: "button",
            onClick: "loginLogout()"
          })
        )
      ));
    }
  }
  /**
   * create the top nav menu that switches physical between pages
   * (edit access data based)
   * @param {Object} nav_menu                 the built nav menu with highlight info
   * @param {String} [header_id='mainHeader'] the target for the main element block
   *                                          if not set mainHeader is assumed
   *                                          this is the target div for the "menuRow"
   */
  createNavMenu(nav_menu, header_id = "mainHeader") {
    if (isObject(nav_menu) && getObjectCount(nav_menu) > 1) {
      if (!exists("menuRow")) {
        $("#" + header_id).html(this.hec.phfo(this.hec.cel("div", "menuRow", "", ["menuRow", "flx-s"])));
      }
      var content = [];
      $.each(nav_menu, function(key, item) {
        if (key != 0) {
          content.push(this.hec.phfo(this.hec.cel("div", "", "&middot;", ["pd-2"])));
        }
        if (item.enabled) {
          if (window.location.href.indexOf(item.url) != -1) {
            item.selected = 1;
          }
          content.push(this.hec.phfo(
            this.hec.aelx(
              this.hec.cel("div"),
              this.hec.cel("a", "", item.name, ["pd-2"].concat(item.selected ? "highlight" : ""), {
                href: item.url
              })
            )
          ));
        }
      });
      $("#menuRow").html(content.join(""));
    } else {
      $("#menuRow").hide();
    }
  }
};

// src/utils/BrowserDetect.mjs
function isWebkit() {
  return "GestureEvent" in window;
}
function isMobileWebKit() {
  return "ongesturechange" in window;
}
function isDesktopWebKit() {
  return typeof window !== "undefined" && "safari" in window && "pushNotification" in window.safari;
}

// src/utils.mjs
var aiob = new ActionIndicatorOverlayBox();
var hec = new HtmlElementCreator();
var l10n = new l10nTranslation(typeof i18n === "undefined" ? {} : i18n);
var ab = new ActionBox(hec, l10n);
var lnm = new LoginNavMenu(hec, l10n);
if (!String.prototype.format) {
  String.prototype.format = function() {
    console.error("[DEPRECATED] use StringHelpers.formatString");
    return formatString(this, arguments);
  };
}
if (Number.prototype.round) {
  Number.prototype.round = function(prec) {
    console.error("[DEPRECATED] use MathHelpers.roundPrecision");
    return roundPrecision(this, prec);
  };
}
if (!String.prototype.escapeHTML) {
  String.prototype.escapeHTML = function() {
    console.error("[DEPRECATED] use HtmlHelpers.escapeHtml");
    return escapeHtml(this);
  };
}
if (!String.prototype.unescapeHTML) {
  String.prototype.unescapeHTML = function() {
    console.error("[DEPRECATED] use HtmlHelpers.unescapeHtml");
    return unescapeHtml(this);
  };
}
function escapeHtml2(string) {
  return escapeHtml(string);
}
function roundPrecision2(number, prec) {
  return roundPrecision(number, prec);
}
function formatString2(string, ...args) {
  return formatString(string, ...args);
}
function unescapeHtml2(string) {
  return unescapeHtml(string);
}
function loadEl2(el_id) {
  return loadEl(el_id);
}
function pop2(theURL, winName, features) {
  pop(theURL, winName, features);
}
function expandTA2(ta_id) {
  expandTA(ta_id);
}
function getWindowSize2() {
  return getWindowSize();
}
function getScrollOffset2() {
  return getScrollOffset();
}
function getScrollOffsetOpener2() {
  return getScrollOffsetOpener();
}
function setCenter2(id, left, top) {
  setCenter(id, left, top);
}
function goToPos2(element, offset = 0, duration = 500, base = "body,html") {
  goToPos(element, offset, duration, base);
}
function goTo2(target) {
  goTo(target);
}
function __(string) {
  return l10n.__(string);
}
function numberWithCommas2(x) {
  return numberWithCommas(x);
}
function convertLBtoBR2(string) {
  return convertLBtoBR(string);
}
function getTimestamp2() {
  return getTimestamp();
}
function dec2hex2(dec) {
  return dec2hex(dec);
}
function generateId2(len) {
  return generateId(len);
}
function randomIdF2() {
  return randomIdF();
}
function getRandomIntInclusive2(min, max) {
  return getRandomIntInclusive(min, max);
}
function isFunction2(name) {
  return isFunction(name);
}
function executeFunctionByName2(functionName, context) {
  return executeFunctionByName(functionName, context);
}
function isObject2(val) {
  return isObject(val);
}
function isArray2(val) {
  return isArray(val);
}
function isIterable2(val) {
  return isIterable(val);
}
function getObjectCount2(object) {
  return getObjectCount(object);
}
function keyInObject2(key, object) {
  return keyInObject(key, object);
}
function getKeyByValue2(object, value) {
  return getKeyByValue(object, value);
}
function valueInObject2(object, value) {
  return valueInObject(object, value);
}
function deepCopyFunction2(inObject) {
  return deepCopyFunction(inObject);
}
function exists2(id) {
  return exists(id);
}
function formatBytes2(bytes) {
  return formatBytes(bytes);
}
function formatBytesLong2(bytes) {
  return formatBytesLong(bytes);
}
function stringByteFormat2(bytes) {
  return stringByteFormat(bytes);
}
function errorCatch2(err) {
  errorCatch(err);
}
function actionIndicator2(loc, overlay = true) {
  actionIndicator(loc, overlay);
}
function actionIndicatorShow2(loc, overlay = true) {
  actionIndicatorShow(loc, overlay);
}
function actionIndicatorHide2(loc, overlay = true) {
  actionIndicatorHide(loc, overlay);
}
function overlayBoxShow2() {
  overlayBoxShow();
}
function overlayBoxHide2() {
  overlayBoxHide();
}
function setOverlayBox2() {
  setOverlayBox();
}
function hideOverlayBox2() {
  hideOverlayBox();
}
function ClearCall2() {
  ClearCall();
}
function showActionIndicator(loc) {
  aiob.showActionIndicator(loc);
}
function hideActionIndicator(loc) {
  aiob.hideActionIndicator(loc);
}
function checkOverlayExists() {
  aiob.checkOverlayExists();
}
function showOverlayBoxLayers(el_id) {
  aiob.showOverlayBoxLayers(el_id);
}
function hideOverlayBoxLayers(el_id = "") {
  aiob.hideOverlayBoxLayers(el_id);
}
function clearCallActionBox() {
  aiob.clearCallActionBox();
}
function cel(tag, id = "", content = "", css = [], options = {}) {
  return hec.cel(tag, id, content, css, options);
}
function ael(base, attach, id = "") {
  return hec.ael(base, attach, id);
}
function aelx(base, ...attach) {
  return hec.aelx(base, ...attach);
}
function aelxar(base, attach) {
  return hec.aelxar(base, attach);
}
function rel(base) {
  return hec.rel(base);
}
function rcssel(_element, css) {
  return hec.rcssel(_element, css);
}
function acssel(_element, css) {
  return hec.acssel(_element, css);
}
function scssel(_element, rcss, acss) {
  hec.scssel(_element, rcss, acss);
}
function phfo(tree) {
  return hec.phfo(tree);
}
function phfa(list) {
  return hec.phfa(list);
}
function html_options2(name, data, selected = "", options_only = false, return_string = false, sort = "") {
  return html_options(name, data, selected, options_only, return_string, sort);
}
function html_options_block2(name, data, selected = "", multiple = 0, options_only = false, return_string = false, sort = "", onchange = "") {
  return html_options_block(
    name,
    data,
    selected,
    multiple,
    options_only,
    return_string,
    sort,
    onchange
  );
}
function html_options_refill2(name, data, sort = "") {
  html_options_refill(name, data, sort);
}
function parseQueryString2(query = "", return_key = "", single = false) {
  return parseQueryString(query, return_key, single);
}
function getQueryStringParam2(search = "", query = "", single = false) {
  return getQueryStringParam(search, query, single);
}
function updateUrlParameter2(key, value, reload = false) {
  return updateUrlParameter(key, value, reload);
}
function removeUrlParameter2(key, reload = false) {
  return removeUrlParameter(key, reload);
}
function hasUrlParameter2(key) {
  return hasUrlParameter(key);
}
function getUrlParameter2(key) {
  return getUrlParameter(key);
}
function loginLogout2() {
  loginLogout();
}
function createLoginRow(login_string, header_id = "mainHeader") {
  lnm.createLoginRow(login_string, header_id);
}
function createNavMenu(nav_menu, header_id = "mainHeader") {
  lnm.createNavMenu(nav_menu, header_id);
}
function showFillActionBox(target_id = "actionBox", content = "", action_box_css = [], override = 0, content_override = 0) {
  ab.showFillActionBox(target_id, content, action_box_css, override, content_override);
}
function fillActionBox(target_id = "actionBox", content = "", action_box_css = []) {
  ab.fillActionBox(target_id, content, action_box_css);
}
function adjustActionBox(target_id = "actionBox", override = 0, content_override = 0) {
  ab.adjustActionBox(target_id, override, content_override);
}
function hideAllActionBoxes() {
  ab.hideAllActionBoxes();
}
function hideActionBox(target_id = "actionBox") {
  ab.hideActionBox(target_id);
}
function showActionBox(target_id = "actionBox", override = 0, content_override = 0, hide_all = true) {
  ab.showActionBox(target_id, override, content_override, hide_all);
}
function closeActionBox(target_id = "actionBox", clean = true) {
  ab.closeActionBox(target_id, clean);
}
function showActionBoxFloat(target_id = "actionBox", override = 0, content_override = 0, hide_all = false) {
  ab.showActionBoxFloat(target_id, override, content_override, hide_all);
}
function closeActionBoxFloat(target_id = "actionBox", clean = true) {
  ab.closeActionBoxFloat(target_id, clean);
}
function createActionBox(target_id = "actionBox", title = "", contents = {}, headers = {}, settings = {}, show_close = true) {
  ab.createActionBox(target_id, title, contents, headers, settings, show_close);
}
function adjustActionBoxHeight(target_id = "actionBox", override = 0, content_override = 0) {
  ab.adjustActionBoxHeight(target_id, override, content_override);
}
function isWebkit2() {
  return isWebkit();
}
function isMobileWebKit2() {
  return isMobileWebKit();
}
function isDesktopWebKit2() {
  return isDesktopWebKit();
}
