!function(n){var e={};function t(i){if(e[i])return e[i].exports;var Q=e[i]={i:i,l:!1,exports:{}};return n[i].call(Q.exports,Q,Q.exports,t),Q.l=!0,Q.exports}t.m=n,t.c=e,t.d=function(n,e,i){t.o(n,e)||Object.defineProperty(n,e,{enumerable:!0,get:i})},t.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},t.t=function(n,e){if(1&e&&(n=t(n)),8&e)return n;if(4&e&&"object"==typeof n&&n&&n.__esModule)return n;var i=Object.create(null);if(t.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:n}),2&e&&"string"!=typeof n)for(var Q in n)t.d(i,Q,function(e){return n[e]}.bind(null,Q));return i},t.n=function(n){var e=n&&n.__esModule?function(){return n.default}:function(){return n};return t.d(e,"a",e),e},t.o=function(n,e){return Object.prototype.hasOwnProperty.call(n,e)},t.p="",t(t.s=187)}({182:function(module,__webpack_exports__,__webpack_require__){"use strict";eval('/* harmony import */ var backbone__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(49);\n/* harmony import */ var backbone__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(backbone__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _constants_faq_hook_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(188);\n/* harmony import */ var _validators_faq_validator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(189);\n/**\n * TinyMceToolbarHandler handles the toolbar button.\n * @since 3.26.0\n * @author Naveen Muthusamy <naveen@wordlift.io>\n */\n\n/**\n * Internal dependencies.\n */\n\n\n\nconst TINYMCE_TOOLBAR_BUTTON_NAME = "wl-faq-toolbar-button";\n\nclass TinymceToolbarHandler {\n  /**\n   * Construct the TinymceToolbarHandler\n   * @param editor {tinymce.Editor} instance.\n   */\n  constructor(editor) {\n    this.editor = editor;\n  }\n  /**\n   * Sets the button text based on the text selected by user.\n   * @param selectedText The text selected by user.\n   * @param button Button present in toolbar.\n   */\n\n\n  setButtonTextBasedOnSelectedText(selectedText, button) {\n    if (_validators_faq_validator__WEBPACK_IMPORTED_MODULE_2__[/* default */ "a"].isQuestion(selectedText)) {\n      button.innerText = "Add Question";\n    } else {\n      button.innerText = "Add Answer";\n    }\n  }\n  /**\n   * When there is no selection disable the button, determine\n   * if it is question or answer and change the button text.\n   */\n\n\n  changeButtonStateOnSelectedText() {\n    const editor = this.editor;\n    const selectedText = editor.selection.getContent({\n      format: "text"\n    });\n    const container = document.getElementById(TINYMCE_TOOLBAR_BUTTON_NAME);\n    const button = container.getElementsByTagName("button")[0];\n\n    if (selectedText.length > 0) {\n      container.classList.remove("mce-disabled");\n      button.disabled = false;\n      this.setButtonTextBasedOnSelectedText(selectedText, button);\n    } else {\n      container.classList.add("mce-disabled");\n      button.disabled = true;\n    }\n  }\n\n  changeToolBarButtonStateBasedOnTextSelected() {\n    const editor = this.editor;\n    editor.on("NodeChange", e => {\n      this.changeButtonStateOnSelectedText();\n    });\n  }\n\n  addButtonToToolBar() {\n    const editor = this.editor;\n    editor.addButton(TINYMCE_TOOLBAR_BUTTON_NAME, {\n      title: "Add question or answer",\n      text: "Add Question or Answer",\n      id: TINYMCE_TOOLBAR_BUTTON_NAME,\n      onclick: function () {\n        Object(backbone__WEBPACK_IMPORTED_MODULE_0__["trigger"])(_constants_faq_hook_constants__WEBPACK_IMPORTED_MODULE_1__[/* FAQ_EVENT_HANDLER_SELECTION_CHANGED */ "a"], editor.selection.getContent({\n          format: "text"\n        }));\n        console.log(document.getElementById(TINYMCE_TOOLBAR_BUTTON_NAME));\n      }\n    });\n    this.changeToolBarButtonStateBasedOnTextSelected();\n  }\n\n}\n\n/* harmony default export */ __webpack_exports__["a"] = (TinymceToolbarHandler);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvZmFxL2hvb2tzL3RpbnltY2UvdGlueW1jZS10b29sYmFyLWhhbmRsZXIuanM/M2JiMiJdLCJuYW1lcyI6WyJUSU5ZTUNFX1RPT0xCQVJfQlVUVE9OX05BTUUiLCJUaW55bWNlVG9vbGJhckhhbmRsZXIiLCJjb25zdHJ1Y3RvciIsImVkaXRvciIsInNldEJ1dHRvblRleHRCYXNlZE9uU2VsZWN0ZWRUZXh0Iiwic2VsZWN0ZWRUZXh0IiwiYnV0dG9uIiwiRmFxVmFsaWRhdG9yIiwiaXNRdWVzdGlvbiIsImlubmVyVGV4dCIsImNoYW5nZUJ1dHRvblN0YXRlT25TZWxlY3RlZFRleHQiLCJzZWxlY3Rpb24iLCJnZXRDb250ZW50IiwiZm9ybWF0IiwiY29udGFpbmVyIiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsImdldEVsZW1lbnRzQnlUYWdOYW1lIiwibGVuZ3RoIiwiY2xhc3NMaXN0IiwicmVtb3ZlIiwiZGlzYWJsZWQiLCJhZGQiLCJjaGFuZ2VUb29sQmFyQnV0dG9uU3RhdGVCYXNlZE9uVGV4dFNlbGVjdGVkIiwib24iLCJlIiwiYWRkQnV0dG9uVG9Ub29sQmFyIiwiYWRkQnV0dG9uIiwidGl0bGUiLCJ0ZXh0IiwiaWQiLCJvbmNsaWNrIiwidHJpZ2dlciIsIkZBUV9FVkVOVF9IQU5ETEVSX1NFTEVDVElPTl9DSEFOR0VEIiwiY29uc29sZSIsImxvZyJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTs7Ozs7O0FBS0E7OztBQUdBO0FBQ0E7QUFDQTtBQUVBLE1BQU1BLDJCQUEyQixHQUFHLHVCQUFwQzs7QUFFQSxNQUFNQyxxQkFBTixDQUE0QjtBQUMxQjs7OztBQUlBQyxhQUFXLENBQUNDLE1BQUQsRUFBUztBQUNsQixTQUFLQSxNQUFMLEdBQWNBLE1BQWQ7QUFDRDtBQUVEOzs7Ozs7O0FBS0FDLGtDQUFnQyxDQUFDQyxZQUFELEVBQWVDLE1BQWYsRUFBdUI7QUFDckQsUUFBSUMseUVBQVksQ0FBQ0MsVUFBYixDQUF3QkgsWUFBeEIsQ0FBSixFQUEyQztBQUN6Q0MsWUFBTSxDQUFDRyxTQUFQLEdBQW1CLGNBQW5CO0FBQ0QsS0FGRCxNQUVPO0FBQ0xILFlBQU0sQ0FBQ0csU0FBUCxHQUFtQixZQUFuQjtBQUNEO0FBQ0Y7QUFFRDs7Ozs7O0FBSUFDLGlDQUErQixHQUFHO0FBQ2hDLFVBQU1QLE1BQU0sR0FBRyxLQUFLQSxNQUFwQjtBQUNBLFVBQU1FLFlBQVksR0FBR0YsTUFBTSxDQUFDUSxTQUFQLENBQWlCQyxVQUFqQixDQUE0QjtBQUFFQyxZQUFNLEVBQUU7QUFBVixLQUE1QixDQUFyQjtBQUNBLFVBQU1DLFNBQVMsR0FBR0MsUUFBUSxDQUFDQyxjQUFULENBQXdCaEIsMkJBQXhCLENBQWxCO0FBQ0EsVUFBTU0sTUFBTSxHQUFHUSxTQUFTLENBQUNHLG9CQUFWLENBQStCLFFBQS9CLEVBQXlDLENBQXpDLENBQWY7O0FBQ0EsUUFBSVosWUFBWSxDQUFDYSxNQUFiLEdBQXNCLENBQTFCLEVBQTZCO0FBQzNCSixlQUFTLENBQUNLLFNBQVYsQ0FBb0JDLE1BQXBCLENBQTJCLGNBQTNCO0FBQ0FkLFlBQU0sQ0FBQ2UsUUFBUCxHQUFrQixLQUFsQjtBQUNBLFdBQUtqQixnQ0FBTCxDQUFzQ0MsWUFBdEMsRUFBb0RDLE1BQXBEO0FBQ0QsS0FKRCxNQUlPO0FBQ0xRLGVBQVMsQ0FBQ0ssU0FBVixDQUFvQkcsR0FBcEIsQ0FBd0IsY0FBeEI7QUFDQWhCLFlBQU0sQ0FBQ2UsUUFBUCxHQUFrQixJQUFsQjtBQUNEO0FBQ0Y7O0FBRURFLDZDQUEyQyxHQUFHO0FBQzVDLFVBQU1wQixNQUFNLEdBQUcsS0FBS0EsTUFBcEI7QUFDQUEsVUFBTSxDQUFDcUIsRUFBUCxDQUFVLFlBQVYsRUFBd0JDLENBQUMsSUFBSTtBQUMzQixXQUFLZiwrQkFBTDtBQUNELEtBRkQ7QUFHRDs7QUFFRGdCLG9CQUFrQixHQUFHO0FBQ25CLFVBQU12QixNQUFNLEdBQUcsS0FBS0EsTUFBcEI7QUFDQUEsVUFBTSxDQUFDd0IsU0FBUCxDQUFpQjNCLDJCQUFqQixFQUE4QztBQUM1QzRCLFdBQUssRUFBRSx3QkFEcUM7QUFFNUNDLFVBQUksRUFBRSx3QkFGc0M7QUFHNUNDLFFBQUUsRUFBRTlCLDJCQUh3QztBQUk1QytCLGFBQU8sRUFBRSxZQUFXO0FBQ2xCQyxnRUFBTyxDQUFDQyx5R0FBRCxFQUFzQzlCLE1BQU0sQ0FBQ1EsU0FBUCxDQUFpQkMsVUFBakIsQ0FBNEI7QUFBRUMsZ0JBQU0sRUFBRTtBQUFWLFNBQTVCLENBQXRDLENBQVA7QUFDQXFCLGVBQU8sQ0FBQ0MsR0FBUixDQUFZcEIsUUFBUSxDQUFDQyxjQUFULENBQXdCaEIsMkJBQXhCLENBQVo7QUFDRDtBQVAyQyxLQUE5QztBQVNBLFNBQUt1QiwyQ0FBTDtBQUNEOztBQTVEeUI7O0FBK0RidEIsOEVBQWYiLCJmaWxlIjoiMTgyLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBUaW55TWNlVG9vbGJhckhhbmRsZXIgaGFuZGxlcyB0aGUgdG9vbGJhciBidXR0b24uXG4gKiBAc2luY2UgMy4yNi4wXG4gKiBAYXV0aG9yIE5hdmVlbiBNdXRodXNhbXkgPG5hdmVlbkB3b3JkbGlmdC5pbz5cbiAqL1xuLyoqXG4gKiBJbnRlcm5hbCBkZXBlbmRlbmNpZXMuXG4gKi9cbmltcG9ydCB7IHRyaWdnZXIgfSBmcm9tIFwiYmFja2JvbmVcIjtcbmltcG9ydCB7IEZBUV9FVkVOVF9IQU5ETEVSX1NFTEVDVElPTl9DSEFOR0VEIH0gZnJvbSBcIi4uLy4uL2NvbnN0YW50cy9mYXEtaG9vay1jb25zdGFudHNcIjtcbmltcG9ydCBGYXFWYWxpZGF0b3IgZnJvbSBcIi4uL3ZhbGlkYXRvcnMvZmFxLXZhbGlkYXRvclwiO1xuXG5jb25zdCBUSU5ZTUNFX1RPT0xCQVJfQlVUVE9OX05BTUUgPSBcIndsLWZhcS10b29sYmFyLWJ1dHRvblwiO1xuXG5jbGFzcyBUaW55bWNlVG9vbGJhckhhbmRsZXIge1xuICAvKipcbiAgICogQ29uc3RydWN0IHRoZSBUaW55bWNlVG9vbGJhckhhbmRsZXJcbiAgICogQHBhcmFtIGVkaXRvciB7dGlueW1jZS5FZGl0b3J9IGluc3RhbmNlLlxuICAgKi9cbiAgY29uc3RydWN0b3IoZWRpdG9yKSB7XG4gICAgdGhpcy5lZGl0b3IgPSBlZGl0b3I7XG4gIH1cblxuICAvKipcbiAgICogU2V0cyB0aGUgYnV0dG9uIHRleHQgYmFzZWQgb24gdGhlIHRleHQgc2VsZWN0ZWQgYnkgdXNlci5cbiAgICogQHBhcmFtIHNlbGVjdGVkVGV4dCBUaGUgdGV4dCBzZWxlY3RlZCBieSB1c2VyLlxuICAgKiBAcGFyYW0gYnV0dG9uIEJ1dHRvbiBwcmVzZW50IGluIHRvb2xiYXIuXG4gICAqL1xuICBzZXRCdXR0b25UZXh0QmFzZWRPblNlbGVjdGVkVGV4dChzZWxlY3RlZFRleHQsIGJ1dHRvbikge1xuICAgIGlmIChGYXFWYWxpZGF0b3IuaXNRdWVzdGlvbihzZWxlY3RlZFRleHQpKSB7XG4gICAgICBidXR0b24uaW5uZXJUZXh0ID0gXCJBZGQgUXVlc3Rpb25cIjtcbiAgICB9IGVsc2Uge1xuICAgICAgYnV0dG9uLmlubmVyVGV4dCA9IFwiQWRkIEFuc3dlclwiO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBXaGVuIHRoZXJlIGlzIG5vIHNlbGVjdGlvbiBkaXNhYmxlIHRoZSBidXR0b24sIGRldGVybWluZVxuICAgKiBpZiBpdCBpcyBxdWVzdGlvbiBvciBhbnN3ZXIgYW5kIGNoYW5nZSB0aGUgYnV0dG9uIHRleHQuXG4gICAqL1xuICBjaGFuZ2VCdXR0b25TdGF0ZU9uU2VsZWN0ZWRUZXh0KCkge1xuICAgIGNvbnN0IGVkaXRvciA9IHRoaXMuZWRpdG9yO1xuICAgIGNvbnN0IHNlbGVjdGVkVGV4dCA9IGVkaXRvci5zZWxlY3Rpb24uZ2V0Q29udGVudCh7IGZvcm1hdDogXCJ0ZXh0XCIgfSk7XG4gICAgY29uc3QgY29udGFpbmVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoVElOWU1DRV9UT09MQkFSX0JVVFRPTl9OQU1FKTtcbiAgICBjb25zdCBidXR0b24gPSBjb250YWluZXIuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJidXR0b25cIilbMF07XG4gICAgaWYgKHNlbGVjdGVkVGV4dC5sZW5ndGggPiAwKSB7XG4gICAgICBjb250YWluZXIuY2xhc3NMaXN0LnJlbW92ZShcIm1jZS1kaXNhYmxlZFwiKTtcbiAgICAgIGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgdGhpcy5zZXRCdXR0b25UZXh0QmFzZWRPblNlbGVjdGVkVGV4dChzZWxlY3RlZFRleHQsIGJ1dHRvbik7XG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnRhaW5lci5jbGFzc0xpc3QuYWRkKFwibWNlLWRpc2FibGVkXCIpO1xuICAgICAgYnV0dG9uLmRpc2FibGVkID0gdHJ1ZTtcbiAgICB9XG4gIH1cblxuICBjaGFuZ2VUb29sQmFyQnV0dG9uU3RhdGVCYXNlZE9uVGV4dFNlbGVjdGVkKCkge1xuICAgIGNvbnN0IGVkaXRvciA9IHRoaXMuZWRpdG9yO1xuICAgIGVkaXRvci5vbihcIk5vZGVDaGFuZ2VcIiwgZSA9PiB7XG4gICAgICB0aGlzLmNoYW5nZUJ1dHRvblN0YXRlT25TZWxlY3RlZFRleHQoKTtcbiAgICB9KTtcbiAgfVxuXG4gIGFkZEJ1dHRvblRvVG9vbEJhcigpIHtcbiAgICBjb25zdCBlZGl0b3IgPSB0aGlzLmVkaXRvcjtcbiAgICBlZGl0b3IuYWRkQnV0dG9uKFRJTllNQ0VfVE9PTEJBUl9CVVRUT05fTkFNRSwge1xuICAgICAgdGl0bGU6IFwiQWRkIHF1ZXN0aW9uIG9yIGFuc3dlclwiLFxuICAgICAgdGV4dDogXCJBZGQgUXVlc3Rpb24gb3IgQW5zd2VyXCIsXG4gICAgICBpZDogVElOWU1DRV9UT09MQkFSX0JVVFRPTl9OQU1FLFxuICAgICAgb25jbGljazogZnVuY3Rpb24oKSB7XG4gICAgICAgIHRyaWdnZXIoRkFRX0VWRU5UX0hBTkRMRVJfU0VMRUNUSU9OX0NIQU5HRUQsIGVkaXRvci5zZWxlY3Rpb24uZ2V0Q29udGVudCh7IGZvcm1hdDogXCJ0ZXh0XCIgfSkpO1xuICAgICAgICBjb25zb2xlLmxvZyhkb2N1bWVudC5nZXRFbGVtZW50QnlJZChUSU5ZTUNFX1RPT0xCQVJfQlVUVE9OX05BTUUpKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgICB0aGlzLmNoYW5nZVRvb2xCYXJCdXR0b25TdGF0ZUJhc2VkT25UZXh0U2VsZWN0ZWQoKTtcbiAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBUaW55bWNlVG9vbGJhckhhbmRsZXI7XG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///182\n')},187:function(module,__webpack_exports__,__webpack_require__){"use strict";eval('__webpack_require__.r(__webpack_exports__);\n/* WEBPACK VAR INJECTION */(function(global) {/* harmony import */ var _tinymce_toolbar_handler__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(182);\n/**\n * This file is automatically loaded by the tinymce via registering in backend.\n * It emits events captured by the faq event handler class.\n * @since 3.26.0\n * @author Naveen Muthusamy <naveen@wordlift.io>\n */\n\n/**\n * Internal dependencies.\n */\n\nconst FAQ_TINYMCE_PLUGIN_NAME = "wl_faq_tinymce";\nconst tinymce = global["tinymce"];\ntinymce.PluginManager.add(FAQ_TINYMCE_PLUGIN_NAME, function (editor) {\n  const toolBarHandler = new _tinymce_toolbar_handler__WEBPACK_IMPORTED_MODULE_0__[/* default */ "a"](editor);\n  toolBarHandler.addButtonToToolBar();\n});\n/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(24)))//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvZmFxL2hvb2tzL3RpbnltY2UvdGlueW1jZS1mYXEtcGx1Z2luLmpzPzQ1YmEiXSwibmFtZXMiOlsiRkFRX1RJTllNQ0VfUExVR0lOX05BTUUiLCJ0aW55bWNlIiwiZ2xvYmFsIiwiUGx1Z2luTWFuYWdlciIsImFkZCIsImVkaXRvciIsInRvb2xCYXJIYW5kbGVyIiwiVGlueW1jZVRvb2xiYXJIYW5kbGVyIiwiYWRkQnV0dG9uVG9Ub29sQmFyIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUFBO0FBQUE7Ozs7Ozs7QUFPQTs7O0FBR0E7QUFFQSxNQUFNQSx1QkFBdUIsR0FBRyxnQkFBaEM7QUFDQSxNQUFNQyxPQUFPLEdBQUdDLE1BQU0sQ0FBQyxTQUFELENBQXRCO0FBQ0FELE9BQU8sQ0FBQ0UsYUFBUixDQUFzQkMsR0FBdEIsQ0FBMEJKLHVCQUExQixFQUFtRCxVQUFVSyxNQUFWLEVBQWtCO0FBQ25FLFFBQU1DLGNBQWMsR0FBRyxJQUFJQyx3RUFBSixDQUEwQkYsTUFBMUIsQ0FBdkI7QUFDQUMsZ0JBQWMsQ0FBQ0Usa0JBQWY7QUFDRCxDQUhELEUiLCJmaWxlIjoiMTg3LmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBUaGlzIGZpbGUgaXMgYXV0b21hdGljYWxseSBsb2FkZWQgYnkgdGhlIHRpbnltY2UgdmlhIHJlZ2lzdGVyaW5nIGluIGJhY2tlbmQuXG4gKiBJdCBlbWl0cyBldmVudHMgY2FwdHVyZWQgYnkgdGhlIGZhcSBldmVudCBoYW5kbGVyIGNsYXNzLlxuICogQHNpbmNlIDMuMjYuMFxuICogQGF1dGhvciBOYXZlZW4gTXV0aHVzYW15IDxuYXZlZW5Ad29yZGxpZnQuaW8+XG4gKi9cblxuLyoqXG4gKiBJbnRlcm5hbCBkZXBlbmRlbmNpZXMuXG4gKi9cbmltcG9ydCBUaW55bWNlVG9vbGJhckhhbmRsZXIgZnJvbSBcIi4vdGlueW1jZS10b29sYmFyLWhhbmRsZXJcIjtcblxuY29uc3QgRkFRX1RJTllNQ0VfUExVR0lOX05BTUUgPSBcIndsX2ZhcV90aW55bWNlXCI7XG5jb25zdCB0aW55bWNlID0gZ2xvYmFsW1widGlueW1jZVwiXTtcbnRpbnltY2UuUGx1Z2luTWFuYWdlci5hZGQoRkFRX1RJTllNQ0VfUExVR0lOX05BTUUsIGZ1bmN0aW9uIChlZGl0b3IpIHtcbiAgY29uc3QgdG9vbEJhckhhbmRsZXIgPSBuZXcgVGlueW1jZVRvb2xiYXJIYW5kbGVyKGVkaXRvcik7XG4gIHRvb2xCYXJIYW5kbGVyLmFkZEJ1dHRvblRvVG9vbEJhcigpO1xufSk7XG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///187\n')},188:function(module,__webpack_exports__,__webpack_require__){"use strict";eval('/* unused harmony export FAQ_REQUEST_ADD_NEW_QUESTION */\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return FAQ_EVENT_HANDLER_SELECTION_CHANGED; });\n/**\n * Constants for the FAQ hooks.\n *\n * @since 3.26.0\n * @author Naveen Muthusamy <naveen@wordlift.io>\n */\n\n/**\n * Event name when the text selection changed in any of text editor, emitted\n * from the hooks.\n * @type {string}\n */\nconst FAQ_REQUEST_ADD_NEW_QUESTION = "FAQ_REQUEST_ADD_NEW_QUESTION";\n/**\n * Event emitted by hook when the text selection is changed.\n * @type {string}\n */\n\nconst FAQ_EVENT_HANDLER_SELECTION_CHANGED = "FAQ_EVENT_HANDLER_SELECTION_CHANGED";//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvZmFxL2NvbnN0YW50cy9mYXEtaG9vay1jb25zdGFudHMuanM/MmQ5OCJdLCJuYW1lcyI6WyJGQVFfUkVRVUVTVF9BRERfTkVXX1FVRVNUSU9OIiwiRkFRX0VWRU5UX0hBTkRMRVJfU0VMRUNUSU9OX0NIQU5HRUQiXSwibWFwcGluZ3MiOiJBQUFBO0FBQUE7QUFBQTs7Ozs7OztBQU9BOzs7OztBQUtPLE1BQU1BLDRCQUE0QixHQUFHLDhCQUFyQztBQUVQOzs7OztBQUlPLE1BQU1DLG1DQUFtQyxHQUFHLHFDQUE1QyIsImZpbGUiOiIxODguanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIENvbnN0YW50cyBmb3IgdGhlIEZBUSBob29rcy5cbiAqXG4gKiBAc2luY2UgMy4yNi4wXG4gKiBAYXV0aG9yIE5hdmVlbiBNdXRodXNhbXkgPG5hdmVlbkB3b3JkbGlmdC5pbz5cbiAqL1xuXG4vKipcbiAqIEV2ZW50IG5hbWUgd2hlbiB0aGUgdGV4dCBzZWxlY3Rpb24gY2hhbmdlZCBpbiBhbnkgb2YgdGV4dCBlZGl0b3IsIGVtaXR0ZWRcbiAqIGZyb20gdGhlIGhvb2tzLlxuICogQHR5cGUge3N0cmluZ31cbiAqL1xuZXhwb3J0IGNvbnN0IEZBUV9SRVFVRVNUX0FERF9ORVdfUVVFU1RJT04gPSBcIkZBUV9SRVFVRVNUX0FERF9ORVdfUVVFU1RJT05cIjtcblxuLyoqXG4gKiBFdmVudCBlbWl0dGVkIGJ5IGhvb2sgd2hlbiB0aGUgdGV4dCBzZWxlY3Rpb24gaXMgY2hhbmdlZC5cbiAqIEB0eXBlIHtzdHJpbmd9XG4gKi9cbmV4cG9ydCBjb25zdCBGQVFfRVZFTlRfSEFORExFUl9TRUxFQ1RJT05fQ0hBTkdFRCA9IFwiRkFRX0VWRU5UX0hBTkRMRVJfU0VMRUNUSU9OX0NIQU5HRURcIjtcbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///188\n')},189:function(module,__webpack_exports__,__webpack_require__){"use strict";eval('/**\n * FaqValidator validates the text selected by user, determines if it is question\n * or answer.\n *\n * @since 3.26.0\n * @author Naveen Muthusamy <naveen@wordlift.io>\n */\nclass FaqValidator {\n  static isQuestion(text) {\n    return text.endsWith("?");\n  }\n\n}\n\n/* harmony default export */ __webpack_exports__["a"] = (FaqValidator);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvZmFxL2hvb2tzL3ZhbGlkYXRvcnMvZmFxLXZhbGlkYXRvci5qcz9lOTdmIl0sIm5hbWVzIjpbIkZhcVZhbGlkYXRvciIsImlzUXVlc3Rpb24iLCJ0ZXh0IiwiZW5kc1dpdGgiXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7O0FBT0EsTUFBTUEsWUFBTixDQUFtQjtBQUVmLFNBQU9DLFVBQVAsQ0FBa0JDLElBQWxCLEVBQXdCO0FBQ3BCLFdBQU9BLElBQUksQ0FBQ0MsUUFBTCxDQUFjLEdBQWQsQ0FBUDtBQUNIOztBQUpjOztBQVFKSCxxRUFBZiIsImZpbGUiOiIxODkuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEZhcVZhbGlkYXRvciB2YWxpZGF0ZXMgdGhlIHRleHQgc2VsZWN0ZWQgYnkgdXNlciwgZGV0ZXJtaW5lcyBpZiBpdCBpcyBxdWVzdGlvblxuICogb3IgYW5zd2VyLlxuICpcbiAqIEBzaW5jZSAzLjI2LjBcbiAqIEBhdXRob3IgTmF2ZWVuIE11dGh1c2FteSA8bmF2ZWVuQHdvcmRsaWZ0LmlvPlxuICovXG5jbGFzcyBGYXFWYWxpZGF0b3Ige1xuXG4gICAgc3RhdGljIGlzUXVlc3Rpb24odGV4dCkge1xuICAgICAgICByZXR1cm4gdGV4dC5lbmRzV2l0aChcIj9cIik7XG4gICAgfVxuXG59XG5cbmV4cG9ydCBkZWZhdWx0IEZhcVZhbGlkYXRvciJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///189\n')},24:function(module,exports){eval('var g;\n\n// This works in non-strict mode\ng = (function() {\n\treturn this;\n})();\n\ntry {\n\t// This works if eval is allowed (see CSP)\n\tg = g || new Function("return this")();\n} catch (e) {\n\t// This works if the window reference is available\n\tif (typeof window === "object") g = window;\n}\n\n// g can still be undefined, but nothing to do about it...\n// We return undefined, instead of nothing here, so it\'s\n// easier to handle this case. if(!global) { ...}\n\nmodule.exports = g;\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vKHdlYnBhY2spL2J1aWxkaW4vZ2xvYmFsLmpzP2NkMDAiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsQ0FBQzs7QUFFRDtBQUNBO0FBQ0E7QUFDQSxDQUFDO0FBQ0Q7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSw0Q0FBNEM7O0FBRTVDIiwiZmlsZSI6IjI0LmpzIiwic291cmNlc0NvbnRlbnQiOlsidmFyIGc7XG5cbi8vIFRoaXMgd29ya3MgaW4gbm9uLXN0cmljdCBtb2RlXG5nID0gKGZ1bmN0aW9uKCkge1xuXHRyZXR1cm4gdGhpcztcbn0pKCk7XG5cbnRyeSB7XG5cdC8vIFRoaXMgd29ya3MgaWYgZXZhbCBpcyBhbGxvd2VkIChzZWUgQ1NQKVxuXHRnID0gZyB8fCBuZXcgRnVuY3Rpb24oXCJyZXR1cm4gdGhpc1wiKSgpO1xufSBjYXRjaCAoZSkge1xuXHQvLyBUaGlzIHdvcmtzIGlmIHRoZSB3aW5kb3cgcmVmZXJlbmNlIGlzIGF2YWlsYWJsZVxuXHRpZiAodHlwZW9mIHdpbmRvdyA9PT0gXCJvYmplY3RcIikgZyA9IHdpbmRvdztcbn1cblxuLy8gZyBjYW4gc3RpbGwgYmUgdW5kZWZpbmVkLCBidXQgbm90aGluZyB0byBkbyBhYm91dCBpdC4uLlxuLy8gV2UgcmV0dXJuIHVuZGVmaW5lZCwgaW5zdGVhZCBvZiBub3RoaW5nIGhlcmUsIHNvIGl0J3Ncbi8vIGVhc2llciB0byBoYW5kbGUgdGhpcyBjYXNlLiBpZighZ2xvYmFsKSB7IC4uLn1cblxubW9kdWxlLmV4cG9ydHMgPSBnO1xuIl0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///24\n')},49:function(module,exports){eval("module.exports = Backbone;//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vZXh0ZXJuYWwgXCJCYWNrYm9uZVwiPzViYzAiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEiLCJmaWxlIjoiNDkuanMiLCJzb3VyY2VzQ29udGVudCI6WyJtb2R1bGUuZXhwb3J0cyA9IEJhY2tib25lOyJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///49\n")}});