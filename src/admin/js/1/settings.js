!function(t){function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/",e(e.s=307)}({1:function(t,e,n){"use strict";function r(t){if(null===t||void 0===t)throw new TypeError("Object.assign cannot be called with null or undefined");return Object(t)}var o=Object.getOwnPropertySymbols,i=Object.prototype.hasOwnProperty,s=Object.prototype.propertyIsEnumerable;t.exports=function(){try{if(!Object.assign)return!1;var t=new String("abc");if(t[5]="de","5"===Object.getOwnPropertyNames(t)[0])return!1;for(var e={},n=0;n<10;n++)e["_"+String.fromCharCode(n)]=n;if("0123456789"!==Object.getOwnPropertyNames(e).map(function(t){return e[t]}).join(""))return!1;var r={};return"abcdefghijklmnopqrst".split("").forEach(function(t){r[t]=t}),"abcdefghijklmnopqrst"===Object.keys(Object.assign({},r)).join("")}catch(t){return!1}}()?Object.assign:function(t,e){for(var n,a,u=r(t),f=1;f<arguments.length;f++){n=Object(arguments[f]);for(var c in n)i.call(n,c)&&(u[c]=n[c]);if(o){a=o(n);for(var l=0;l<a.length;l++)s.call(n,a[l])&&(u[a[l]]=n[a[l]])}}return u}},14:function(t,e,n){"use strict";"undefined"===typeof Promise&&(n(15).enable(),window.Promise=n(17)),n(18),Object.assign=n(1)},15:function(t,e,n){"use strict";function r(){f=!1,a._47=null,a._71=null}function o(t){function e(e){(t.allRejections||s(l[e].error,t.whitelist||u))&&(l[e].displayId=c++,t.onUnhandled?(l[e].logged=!0,t.onUnhandled(l[e].displayId,l[e].error)):(l[e].logged=!0,i(l[e].displayId,l[e].error)))}function n(e){l[e].logged&&(t.onHandled?t.onHandled(l[e].displayId,l[e].error):l[e].onUnhandled||(console.warn("Promise Rejection Handled (id: "+l[e].displayId+"):"),console.warn('  This means you can ignore any previous messages of the form "Possible Unhandled Promise Rejection" with id '+l[e].displayId+".")))}t=t||{},f&&r(),f=!0;var o=0,c=0,l={};a._47=function(t){2===t._83&&l[t._56]&&(l[t._56].logged?n(t._56):clearTimeout(l[t._56].timeout),delete l[t._56])},a._71=function(t,n){0===t._75&&(t._56=o++,l[t._56]={displayId:null,error:n,timeout:setTimeout(e.bind(null,t._56),s(n,u)?100:2e3),logged:!1})}}function i(t,e){console.warn("Possible Unhandled Promise Rejection (id: "+t+"):"),((e&&(e.stack||e))+"").split("\n").forEach(function(t){console.warn("  "+t)})}function s(t,e){return e.some(function(e){return t instanceof e})}var a=n(7),u=[ReferenceError,TypeError,RangeError],f=!1;e.disable=r,e.enable=o},16:function(t,e,n){"use strict";(function(e){function n(t){s.length||(i(),a=!0),s[s.length]=t}function r(){for(;u<s.length;){var t=u;if(u+=1,s[t].call(),u>f){for(var e=0,n=s.length-u;e<n;e++)s[e]=s[e+u];s.length-=u,u=0}}s.length=0,u=0,a=!1}function o(t){return function(){function e(){clearTimeout(n),clearInterval(r),t()}var n=setTimeout(e,0),r=setInterval(e,50)}}t.exports=n;var i,s=[],a=!1,u=0,f=1024,c="undefined"!==typeof e?e:self,l=c.MutationObserver||c.WebKitMutationObserver;i="function"===typeof l?function(t){var e=1,n=new l(t),r=document.createTextNode("");return n.observe(r,{characterData:!0}),function(){e=-e,r.data=e}}(r):o(r),n.requestFlush=i,n.makeRequestCallFromTimer=o}).call(e,n(8))},17:function(t,e,n){"use strict";function r(t){var e=new o(o._44);return e._83=1,e._18=t,e}var o=n(7);t.exports=o;var i=r(!0),s=r(!1),a=r(null),u=r(void 0),f=r(0),c=r("");o.resolve=function(t){if(t instanceof o)return t;if(null===t)return a;if(void 0===t)return u;if(!0===t)return i;if(!1===t)return s;if(0===t)return f;if(""===t)return c;if("object"===typeof t||"function"===typeof t)try{var e=t.then;if("function"===typeof e)return new o(e.bind(t))}catch(t){return new o(function(e,n){n(t)})}return r(t)},o.all=function(t){var e=Array.prototype.slice.call(t);return new o(function(t,n){function r(s,a){if(a&&("object"===typeof a||"function"===typeof a)){if(a instanceof o&&a.then===o.prototype.then){for(;3===a._83;)a=a._18;return 1===a._83?r(s,a._18):(2===a._83&&n(a._18),void a.then(function(t){r(s,t)},n))}var u=a.then;if("function"===typeof u){return void new o(u.bind(a)).then(function(t){r(s,t)},n)}}e[s]=a,0===--i&&t(e)}if(0===e.length)return t([]);for(var i=e.length,s=0;s<e.length;s++)r(s,e[s])})},o.reject=function(t){return new o(function(e,n){n(t)})},o.race=function(t){return new o(function(e,n){t.forEach(function(t){o.resolve(t).then(e,n)})})},o.prototype.catch=function(t){return this.then(null,t)}},18:function(t,e){!function(t){"use strict";function e(t){if("string"!==typeof t&&(t=String(t)),/[^a-z0-9\-#$%&'*+.\^_`|~]/i.test(t))throw new TypeError("Invalid character in header field name");return t.toLowerCase()}function n(t){return"string"!==typeof t&&(t=String(t)),t}function r(t){var e={next:function(){var e=t.shift();return{done:void 0===e,value:e}}};return v.iterable&&(e[Symbol.iterator]=function(){return e}),e}function o(t){this.map={},t instanceof o?t.forEach(function(t,e){this.append(e,t)},this):Array.isArray(t)?t.forEach(function(t){this.append(t[0],t[1])},this):t&&Object.getOwnPropertyNames(t).forEach(function(e){this.append(e,t[e])},this)}function i(t){if(t.bodyUsed)return Promise.reject(new TypeError("Already read"));t.bodyUsed=!0}function s(t){return new Promise(function(e,n){t.onload=function(){e(t.result)},t.onerror=function(){n(t.error)}})}function a(t){var e=new FileReader,n=s(e);return e.readAsArrayBuffer(t),n}function u(t){var e=new FileReader,n=s(e);return e.readAsText(t),n}function f(t){for(var e=new Uint8Array(t),n=new Array(e.length),r=0;r<e.length;r++)n[r]=String.fromCharCode(e[r]);return n.join("")}function c(t){if(t.slice)return t.slice(0);var e=new Uint8Array(t.byteLength);return e.set(new Uint8Array(t)),e.buffer}function l(){return this.bodyUsed=!1,this._initBody=function(t){if(this._bodyInit=t,t)if("string"===typeof t)this._bodyText=t;else if(v.blob&&Blob.prototype.isPrototypeOf(t))this._bodyBlob=t;else if(v.formData&&FormData.prototype.isPrototypeOf(t))this._bodyFormData=t;else if(v.searchParams&&URLSearchParams.prototype.isPrototypeOf(t))this._bodyText=t.toString();else if(v.arrayBuffer&&v.blob&&_(t))this._bodyArrayBuffer=c(t.buffer),this._bodyInit=new Blob([this._bodyArrayBuffer]);else{if(!v.arrayBuffer||!ArrayBuffer.prototype.isPrototypeOf(t)&&!w(t))throw new Error("unsupported BodyInit type");this._bodyArrayBuffer=c(t)}else this._bodyText="";this.headers.get("content-type")||("string"===typeof t?this.headers.set("content-type","text/plain;charset=UTF-8"):this._bodyBlob&&this._bodyBlob.type?this.headers.set("content-type",this._bodyBlob.type):v.searchParams&&URLSearchParams.prototype.isPrototypeOf(t)&&this.headers.set("content-type","application/x-www-form-urlencoded;charset=UTF-8"))},v.blob&&(this.blob=function(){var t=i(this);if(t)return t;if(this._bodyBlob)return Promise.resolve(this._bodyBlob);if(this._bodyArrayBuffer)return Promise.resolve(new Blob([this._bodyArrayBuffer]));if(this._bodyFormData)throw new Error("could not read FormData body as blob");return Promise.resolve(new Blob([this._bodyText]))},this.arrayBuffer=function(){return this._bodyArrayBuffer?i(this)||Promise.resolve(this._bodyArrayBuffer):this.blob().then(a)}),this.text=function(){var t=i(this);if(t)return t;if(this._bodyBlob)return u(this._bodyBlob);if(this._bodyArrayBuffer)return Promise.resolve(f(this._bodyArrayBuffer));if(this._bodyFormData)throw new Error("could not read FormData body as text");return Promise.resolve(this._bodyText)},v.formData&&(this.formData=function(){return this.text().then(p)}),this.json=function(){return this.text().then(JSON.parse)},this}function d(t){var e=t.toUpperCase();return g.indexOf(e)>-1?e:t}function h(t,e){e=e||{};var n=e.body;if(t instanceof h){if(t.bodyUsed)throw new TypeError("Already read");this.url=t.url,this.credentials=t.credentials,e.headers||(this.headers=new o(t.headers)),this.method=t.method,this.mode=t.mode,n||null==t._bodyInit||(n=t._bodyInit,t.bodyUsed=!0)}else this.url=String(t);if(this.credentials=e.credentials||this.credentials||"omit",!e.headers&&this.headers||(this.headers=new o(e.headers)),this.method=d(e.method||this.method||"GET"),this.mode=e.mode||this.mode||null,this.referrer=null,("GET"===this.method||"HEAD"===this.method)&&n)throw new TypeError("Body not allowed for GET or HEAD requests");this._initBody(n)}function p(t){var e=new FormData;return t.trim().split("&").forEach(function(t){if(t){var n=t.split("="),r=n.shift().replace(/\+/g," "),o=n.join("=").replace(/\+/g," ");e.append(decodeURIComponent(r),decodeURIComponent(o))}}),e}function y(t){var e=new o;return t.split(/\r?\n/).forEach(function(t){var n=t.split(":"),r=n.shift().trim();if(r){var o=n.join(":").trim();e.append(r,o)}}),e}function b(t,e){e||(e={}),this.type="default",this.status="status"in e?e.status:200,this.ok=this.status>=200&&this.status<300,this.statusText="statusText"in e?e.statusText:"OK",this.headers=new o(e.headers),this.url=e.url||"",this._initBody(t)}if(!t.fetch){var v={searchParams:"URLSearchParams"in t,iterable:"Symbol"in t&&"iterator"in Symbol,blob:"FileReader"in t&&"Blob"in t&&function(){try{return new Blob,!0}catch(t){return!1}}(),formData:"FormData"in t,arrayBuffer:"ArrayBuffer"in t};if(v.arrayBuffer)var m=["[object Int8Array]","[object Uint8Array]","[object Uint8ClampedArray]","[object Int16Array]","[object Uint16Array]","[object Int32Array]","[object Uint32Array]","[object Float32Array]","[object Float64Array]"],_=function(t){return t&&DataView.prototype.isPrototypeOf(t)},w=ArrayBuffer.isView||function(t){return t&&m.indexOf(Object.prototype.toString.call(t))>-1};o.prototype.append=function(t,r){t=e(t),r=n(r);var o=this.map[t];this.map[t]=o?o+","+r:r},o.prototype.delete=function(t){delete this.map[e(t)]},o.prototype.get=function(t){return t=e(t),this.has(t)?this.map[t]:null},o.prototype.has=function(t){return this.map.hasOwnProperty(e(t))},o.prototype.set=function(t,r){this.map[e(t)]=n(r)},o.prototype.forEach=function(t,e){for(var n in this.map)this.map.hasOwnProperty(n)&&t.call(e,this.map[n],n,this)},o.prototype.keys=function(){var t=[];return this.forEach(function(e,n){t.push(n)}),r(t)},o.prototype.values=function(){var t=[];return this.forEach(function(e){t.push(e)}),r(t)},o.prototype.entries=function(){var t=[];return this.forEach(function(e,n){t.push([n,e])}),r(t)},v.iterable&&(o.prototype[Symbol.iterator]=o.prototype.entries);var g=["DELETE","GET","HEAD","OPTIONS","POST","PUT"];h.prototype.clone=function(){return new h(this,{body:this._bodyInit})},l.call(h.prototype),l.call(b.prototype),b.prototype.clone=function(){return new b(this._bodyInit,{status:this.status,statusText:this.statusText,headers:new o(this.headers),url:this.url})},b.error=function(){var t=new b(null,{status:0,statusText:""});return t.type="error",t};var j=[301,302,303,307,308];b.redirect=function(t,e){if(-1===j.indexOf(e))throw new RangeError("Invalid status code");return new b(null,{status:e,headers:{location:t}})},t.Headers=o,t.Request=h,t.Response=b,t.fetch=function(t,e){return new Promise(function(n,r){var o=new h(t,e),i=new XMLHttpRequest;i.onload=function(){var t={status:i.status,statusText:i.statusText,headers:y(i.getAllResponseHeaders()||"")};t.url="responseURL"in i?i.responseURL:t.headers.get("X-Request-URL");var e="response"in i?i.response:i.responseText;n(new b(e,t))},i.onerror=function(){r(new TypeError("Network request failed"))},i.ontimeout=function(){r(new TypeError("Network request failed"))},i.open(o.method,o.url,!0),"include"===o.credentials&&(i.withCredentials=!0),"responseType"in i&&v.blob&&(i.responseType="blob"),o.headers.forEach(function(t,e){i.setRequestHeader(e,t)}),i.send("undefined"===typeof o._bodyInit?null:o._bodyInit)})},t.fetch.polyfill=!0}}("undefined"!==typeof self?self:this)},307:function(t,e,n){n(14),t.exports=n(308)},308:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n(309),o=(n.n(r),n(310)),i=n(311),s=n(312),a=n(313);!function(t,e){t(function(){Object(o.a)("#wl-key"),t("#wl-key").trigger("keyup"),Object(i.a)("#wl-country-code","#wl-site-language"),Object(s.a)("#wl-publisher-media-uploader",{title:e.l10n.logo_selection_title,button:e.l10n.logo_selection_button,multiple:!1,library:{type:"image"}},function(e){t("#wl-publisher-media-uploader-preview").attr("src",e.url).show(),t("#wl-publisher-media-uploader-id").val(e.id)}),Object(a.a)(".wl-tabs-element")})}(jQuery,wlSettings)},309:function(t,e){},310:function(t,e,n){"use strict";var r=n(43),o=jQuery,i=function(t){o(t).on("keyup",function(){var t=o(this);t.removeClass("untouched valid invalid"),Object(r.a)(t,function(){wp.ajax.post("wl_validate_key",{key:t.val()}).done(function(e){e&&e.valid?t.addClass("valid"):t.addClass("invalid")})})})};e.a=i},311:function(t,e,n){"use strict";var r=(n(43),jQuery),o=function(t,e){r(e).on("change",function(){var e=r(this),n=r(t),o=(n.val(),n.data("country-codes")),i=e.val();console.log(o),wp.ajax.post("wl_update_country_options",{lang:i}).done(function(t){console.log(t),n.html(t)})})};e.a=o},312:function(t,e,n){"use strict";var r=jQuery,o=function(t,e,n){var o=wp.media(e);o.on("select",function(){n(o.state().get("selection").first().toJSON())}),r(t).on("click",function(){o.open()})};e.a=o},313:function(t,e,n){"use strict";var r=jQuery,o=function(t){r(t).each(function(){var t=r(this);t.tabs({active:t.data("active")})})};e.a=o},43:function(t,e,n){"use strict";var r=function(t,e){for(var n=arguments.length,r=Array(n>3?n-3:0),o=3;o<n;o++)r[o-3]=arguments[o];var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:500;clearTimeout(t.data("timeout")),t.data("timeout",setTimeout.apply(void 0,[e,i].concat(r)))};e.a=r},7:function(t,e,n){"use strict";function r(){}function o(t){try{return t.then}catch(t){return v=t,m}}function i(t,e){try{return t(e)}catch(t){return v=t,m}}function s(t,e,n){try{t(e,n)}catch(t){return v=t,m}}function a(t){if("object"!==typeof this)throw new TypeError("Promises must be constructed via new");if("function"!==typeof t)throw new TypeError("Promise constructor's argument is not a function");this._75=0,this._83=0,this._18=null,this._38=null,t!==r&&y(t,this)}function u(t,e,n){return new t.constructor(function(o,i){var s=new a(r);s.then(o,i),f(t,new p(e,n,s))})}function f(t,e){for(;3===t._83;)t=t._18;if(a._47&&a._47(t),0===t._83)return 0===t._75?(t._75=1,void(t._38=e)):1===t._75?(t._75=2,void(t._38=[t._38,e])):void t._38.push(e);c(t,e)}function c(t,e){b(function(){var n=1===t._83?e.onFulfilled:e.onRejected;if(null===n)return void(1===t._83?l(e.promise,t._18):d(e.promise,t._18));var r=i(n,t._18);r===m?d(e.promise,v):l(e.promise,r)})}function l(t,e){if(e===t)return d(t,new TypeError("A promise cannot be resolved with itself."));if(e&&("object"===typeof e||"function"===typeof e)){var n=o(e);if(n===m)return d(t,v);if(n===t.then&&e instanceof a)return t._83=3,t._18=e,void h(t);if("function"===typeof n)return void y(n.bind(e),t)}t._83=1,t._18=e,h(t)}function d(t,e){t._83=2,t._18=e,a._71&&a._71(t,e),h(t)}function h(t){if(1===t._75&&(f(t,t._38),t._38=null),2===t._75){for(var e=0;e<t._38.length;e++)f(t,t._38[e]);t._38=null}}function p(t,e,n){this.onFulfilled="function"===typeof t?t:null,this.onRejected="function"===typeof e?e:null,this.promise=n}function y(t,e){var n=!1,r=s(t,function(t){n||(n=!0,l(e,t))},function(t){n||(n=!0,d(e,t))});n||r!==m||(n=!0,d(e,v))}var b=n(16),v=null,m={};t.exports=a,a._47=null,a._71=null,a._44=r,a.prototype.then=function(t,e){if(this.constructor!==a)return u(this,t,e);var n=new a(r);return f(this,new p(t,e,n)),n}},8:function(t,e){var n;n=function(){return this}();try{n=n||Function("return this")()||(0,eval)("this")}catch(t){"object"===typeof window&&(n=window)}t.exports=n}});
//# sourceMappingURL=settings.js.map