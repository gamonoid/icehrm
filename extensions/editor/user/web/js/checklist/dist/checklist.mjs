(function(){"use strict";try{if(typeof document<"u"){var e=document.createElement("style");e.appendChild(document.createTextNode('.cdx-checklist{gap:6px;display:flex;flex-direction:column}.cdx-checklist__item{display:flex;box-sizing:content-box;align-items:flex-start}.cdx-checklist__item-avatar{vertical-align:middle;width:30px;height:30px;border-radius:50%;margin-right:20px}.cdx-checklist__item-text{outline:none;flex-grow:1;line-height:1.57em}.cdx-checklist__item-username{outline:none;flex-grow:1;line-height:1.57em;font-size:12px;font-weight:700}.cdx-checklist__item-checkbox{width:22px;height:22px;display:flex;align-items:center;margin-right:8px;margin-top:calc(.785em - 11px);cursor:pointer}.cdx-checklist__item-checkbox svg{opacity:0;height:20px;width:20px;position:absolute;left:-1px;top:-1px;max-height:20px}@media (hover: hover){.cdx-checklist__item-checkbox:not(.cdx-checklist__item-checkbox--no-hover):hover .cdx-checklist__item-checkbox-check svg{opacity:1}}.cdx-checklist__item-checkbox-check{cursor:pointer;display:inline-block;flex-shrink:0;position:relative;width:20px;height:20px;box-sizing:border-box;margin-left:0;border-radius:5px;border:1px solid #C9C9C9;background:#fff}.cdx-checklist__item-checkbox-check:before{content:"";position:absolute;top:0;right:0;bottom:0;left:0;border-radius:100%;background-color:#369fff;visibility:hidden;pointer-events:none;transform:scale(1);transition:transform .4s ease-out,opacity .4s}@media (hover: hover){.cdx-checklist__item--checked .cdx-checklist__item-checkbox:not(.cdx-checklist__item--checked .cdx-checklist__item-checkbox--no-hover):hover .cdx-checklist__item-checkbox-check{background:#0059AB;border-color:#0059ab}}.cdx-checklist__item--checked .cdx-checklist__item-checkbox-check{background:#369FFF;border-color:#369fff}.cdx-checklist__item--checked .cdx-checklist__item-checkbox-check svg{opacity:1}.cdx-checklist__item--checked .cdx-checklist__item-checkbox-check svg path{stroke:#fff}.cdx-checklist__item--checked .cdx-checklist__item-checkbox-check:before{opacity:0;visibility:visible;transform:scale(2.5)}')),document.head.appendChild(e)}}catch(c){console.error("vite-plugin-css-injected-by-js",c)}})();
const g = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M7 12L10.4884 15.8372C10.5677 15.9245 10.705 15.9245 10.7844 15.8372L17 9"/></svg>', C = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M12 10C12.7145 10 13.239 9.56559 13.5392 9.11536C13.844 8.65814 14 8.0841 14 7.5C14 6.9159 13.844 6.34186 13.5392 5.88464C13.239 5.43441 12.7145 5 12 5C11.2855 5 10.761 5.43441 10.4608 5.88464C10.156 6.34186 10 6.9159 10 7.5C10 8.0841 10.156 8.65814 10.4608 9.11536C10.761 9.56559 11.2855 10 12 10Z"/><ellipse cx="12" cy="16" stroke="currentColor" stroke-width="2" rx="3" ry="5" transform="rotate(-90 12 16)"/></svg>';
function d() {
  const i = document.activeElement, t = window.getSelection().getRangeAt(0), n = t.cloneRange();
  return n.selectNodeContents(i), n.setStart(t.endContainer, t.endOffset), n.extractContents();
}
function k(i) {
  const e = document.createElement("div");
  return e.appendChild(i), e.innerHTML;
}
function r(i, e = null, t = {}) {
  const n = document.createElement(i);
  Array.isArray(e) ? n.classList.add(...e) : e && n.classList.add(e);
  for (const s in t)
    n[s] = t[s];
  return n;
}
function m(i) {
  return i.innerHTML.replace("<br>", " ").trim();
}
function p(i, e = !1, t = void 0) {
  const n = document.createRange(), s = window.getSelection();
  n.selectNodeContents(i), t !== void 0 && (n.setStart(i, t), n.setEnd(i, t)), n.collapse(e), s.removeAllRanges(), s.addRange(n);
}
Element.prototype.matches || (Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector);
Element.prototype.closest || (Element.prototype.closest = function(i) {
  let e = this;
  if (!document.documentElement.contains(e))
    return null;
  do {
    if (e.matches(i))
      return e;
    e = e.parentElement || e.parentNode;
  } while (e !== null && e.nodeType === 1);
  return null;
});
class f {
  /**
   * Notify core that read-only mode is supported
   *
   * @returns {boolean}
   */
  static get isReadOnlySupported() {
    return !0;
  }
  /**
   * Allow to use native Enter behaviour
   *
   * @returns {boolean}
   * @public
   */
  static get enableLineBreaks() {
    return !0;
  }
  /**
   * Get Tool toolbox settings
   * icon - Tool icon's SVG
   * title - title to show in toolbox
   *
   * @returns {{icon: string, title: string}}
   */
  static get toolbox() {
    return {
      icon: C,
      title: "User Tasks"
    };
  }
  /**
   * Allow Checkbox Tool to be converted to/from other block
   *
   * @returns {{export: Function, import: Function}}
   */
  static get conversionConfig() {
    return {
      /**
       * To create exported string from the checkbox, concatenate items by dot-symbol.
       *
       * @param {ChecklistData} data - checklist data to create a string from that
       * @returns {string}
       */
      export: (e) => e.items.map(({ text: t }) => t).join(". "),
      /**
       * To create a checklist from other block's string, just put it at the first item
       *
       * @param {string} string - string to create list tool data from that
       * @returns {ChecklistData}
       */
      import: (e) => ({
        items: [
          {
            text: e,
            checked: !1
          }
        ]
      })
    };
  }
  /**
   * Render plugin`s main Element and fill it with saved data
   *
   * @param {object} options - block constructor options
   * @param {ChecklistData} options.data - previously saved data
   * @param {object} options.config - user config for Tool
   * @param {object} options.api - Editor.js API
   * @param {boolean} options.readOnly - read only mode flag
   */
  constructor({
    data: e,
    config: t,
    api: n,
    readOnly: s
  }) {
    this._elements = {
      wrapper: null,
      items: []
    }, this.readOnly = s, this.api = n, this.data = e || {};
  }
  /**
   * Returns checklist tag with items
   *
   * @returns {Element}
   */
  render() {
    return this._elements.wrapper = r("div", [this.CSS.baseBlock, this.CSS.wrapper]), this.data.items || (this.data.items = [
      {
        text: "",
        checked: !1
      }
    ]), this.data.items.forEach((e) => {
      const t = this.createChecklistItem(e);
      this._elements.wrapper.appendChild(t);
    }), this.readOnly && !editor_can_select_checks ? this._elements.wrapper : (this._elements.wrapper.addEventListener("keydown", (e) => {
      const [t, n] = [13, 8];
      switch (e.keyCode) {
        case t:
          this.enterPressed(e);
          break;
        case n:
          this.backspace(e);
          break;
      }
    }, !1), this._elements.wrapper.addEventListener("click", (e) => {
      this.toggleCheckbox(e);
    }), this._elements.wrapper);
  }
  /**
   * Return Checklist data
   *
   * @returns {ChecklistData}
   */
  save() {
    let e = this.items.map((t) => {
      const n = this.getItemInput(t), s = this.getAvatar(t);
      return {
        text: m(n),
        checked: t.classList.contains(this.CSS.itemChecked),
        user_image: s.src,
        user_name: s.title
      };
    });
    return e = e.filter((t) => t.text.trim().length !== 0), {
      items: e
    };
  }
  /**
   * Validate data: check if Checklist has items
   *
   * @param {ChecklistData} savedData — data received after saving
   * @returns {boolean} false if saved data is not correct, otherwise true
   * @public
   */
  validate(e) {
    return !!e.items.length;
  }
  /**
   * Toggle checklist item state
   *
   * @param {MouseEvent} event - click
   * @returns {void}
   */
  toggleCheckbox(e) {
    const t = e.target.closest(`.${this.CSS.item}`), n = t.querySelector(`.${this.CSS.checkboxContainer}`);
    n.contains(e.target) && (t.classList.toggle(this.CSS.itemChecked), n.classList.add(this.CSS.noHover), n.addEventListener("mouseleave", () => this.removeSpecialHoverBehavior(n), { once: !0 }));
  }
  findEmployeeImage(e) {
    const t = window.editorEmployees.find((n) => n.value === e);
    return t ? t.image : "";
  }
  /**
   * Create Checklist items
   *
   * @param {ChecklistItem} item - data.item
   * @returns {Element} checkListItem - new element of checklist
   */
  createChecklistItem(e = {}) {
    const t = r("div", this.CSS.item), n = r("span", this.CSS.checkbox), s = r("div", this.CSS.checkboxContainer), l = r("div", this.CSS.textField, {
      innerHTML: e.text ? e.text : "",
      contentEditable: !this.readOnly
    }), a = this.findEmployeeImage(e.user_name), c = r("img", this.CSS.avatar, {
      src: a ? a.replaceAll("&amp;", "&") : BASE_URL + "images/user-icon.png",
      title: e.user_name ? e.user_name : "Not assigned",
      "data-toggle": "tooltip"
    });
    return this.readOnly || c.setAttribute("onclick", "selectEmployee(this)"), r("div", this.CSS.userName, {
      innerHTML: `[${e.user_name}]`,
      contentEditable: !this.readOnly
    }), e.checked && t.classList.add(this.CSS.itemChecked), n.innerHTML = g, s.appendChild(n), t.appendChild(c), t.appendChild(s), t.appendChild(l), t;
  }
  /**
   * Append new elements to the list by pressing Enter
   *
   * @param {KeyboardEvent} event - keyboard event
   */
  enterPressed(e) {
    e.preventDefault();
    const { items: t } = this, n = document.activeElement.closest(`.${this.CSS.item}`);
    if (t.indexOf(n) === t.length - 1 && m(this.getItemInput(n)).length === 0) {
      const u = this.api.blocks.getCurrentBlockIndex();
      n.remove(), this.api.blocks.insert(), this.api.caret.setToBlock(u + 1);
      return;
    }
    const a = d(), c = k(a), o = this.createChecklistItem({
      text: c,
      checked: !1
    });
    this._elements.wrapper.insertBefore(o, n.nextSibling), p(this.getItemInput(o), !0);
  }
  /**
   * Handle backspace
   *
   * @param {KeyboardEvent} event - keyboard event
   */
  backspace(e) {
    const t = e.target.closest(`.${this.CSS.item}`), n = this.items.indexOf(t), s = this.items[n - 1];
    if (!s || !(window.getSelection().focusOffset === 0))
      return;
    e.preventDefault();
    const c = d(), o = this.getItemInput(s), h = o.childNodes.length;
    o.appendChild(c), p(o, void 0, h), t.remove();
  }
  /**
   * Styles
   *
   * @private
   * @returns {object<string>}
   */
  get CSS() {
    return {
      baseBlock: this.api.styles.block,
      wrapper: "cdx-checklist",
      item: "cdx-checklist__item",
      itemChecked: "cdx-checklist__item--checked",
      noHover: "cdx-checklist__item-checkbox--no-hover",
      checkbox: "cdx-checklist__item-checkbox-check",
      textField: "cdx-checklist__item-text",
      checkboxContainer: "cdx-checklist__item-checkbox",
      avatar: "cdx-checklist__item-avatar",
      userName: "cdx-checklist__item-username"
    };
  }
  /**
   * Return all items elements
   *
   * @returns {Element[]}
   */
  get items() {
    return Array.from(this._elements.wrapper.querySelectorAll(`.${this.CSS.item}`));
  }
  /**
   * Removes class responsible for special hover behavior on an item
   *
   * @private
   * @param {Element} el - item wrapper
   * @returns {Element}
   */
  removeSpecialHoverBehavior(e) {
    e.classList.remove(this.CSS.noHover);
  }
  /**
   * Find and return item's content editable element
   *
   * @private
   * @param {Element} el - item wrapper
   * @returns {Element}
   */
  getItemInput(e) {
    return e.querySelector(`.${this.CSS.textField}`);
  }
  getAvatar(e) {
    return e.querySelector(`.${this.CSS.avatar}`);
  }
}
export {
  f as default
};
