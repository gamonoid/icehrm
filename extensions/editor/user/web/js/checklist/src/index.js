/**
 * Build styles
 */
import './index.css';

import {IconChecklist, IconCheck, IconUser} from '@codexteam/icons';
import {
  extractContentAfterCaret, fragmentToHtml, make, getHTML, moveCaret,
} from './utils';


/**
 * Require polyfills
 */
import './polyfills.js';

/**
 * @typedef {object} ChecklistData
 * @property {ChecklistItem[]} items - checklist elements
 */

/**
 * @typedef {object} ChecklistItem
 * @property {string} text - item label
 * @property {boolean} checked - is the item checked
 */

/**
 * Checklist Tool for the Editor.js 2.0
 */
export default class Checklist {
  /**
   * Notify core that read-only mode is supported
   *
   * @returns {boolean}
   */
  static get isReadOnlySupported() {
    return true;
  }

  /**
   * Allow to use native Enter behaviour
   *
   * @returns {boolean}
   * @public
   */
  static get enableLineBreaks() {
    return true;
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
      icon: IconUser,
      title: 'User Tasks',
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
      export: (data) => data.items.map(({ text }) => text).join('. '),
      /**
       * To create a checklist from other block's string, just put it at the first item
       *
       * @param {string} string - string to create list tool data from that
       * @returns {ChecklistData}
       */
      import: (string) => ({
        items: [
          {
            text: string,
            checked: false,
          },
        ],
      }),
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
    data, config, api, readOnly,
  }) {
    /**
     * HTML nodes
     *
     * @private
     */
    this._elements = {
      wrapper: null,
      items: [],
    };
    this.readOnly = readOnly;
    this.api = api;
    /**
     * Fill or create tool's data structure
     */
    this.data = data || {};
  }

  /**
   * Returns checklist tag with items
   *
   * @returns {Element}
   */
  render() {
    this._elements.wrapper = make('div', [this.CSS.baseBlock, this.CSS.wrapper]);

    /**
     * If there is no data, create first empty item
     */
    if (!this.data.items) {
      this.data.items = [
        {
          text: '',
          checked: false,
        },
      ];
    }

    this.data.items.forEach((item) => {
      const newItem = this.createChecklistItem(item);

      this._elements.wrapper.appendChild(newItem);
    });

    /**
     * If read-only mode is on, do not bind events
     */
    if (this.readOnly && !editor_can_select_checks) {
      return this._elements.wrapper;
    }

    /**
     * Add event-listeners
     */
    this._elements.wrapper.addEventListener('keydown', (event) => {
      const [ENTER, BACKSPACE] = [13, 8]; // key codes

      switch (event.keyCode) {
        case ENTER:
          this.enterPressed(event);
          break;
        case BACKSPACE:
          this.backspace(event);
          break;
      }
    }, false);

    this._elements.wrapper.addEventListener('click', (event) => {
      this.toggleCheckbox(event);
    });

    return this._elements.wrapper;
  }

  /**
   * Return Checklist data
   *
   * @returns {ChecklistData}
   */
  save() {
    /**
     * @type {ChecklistItem[]}
     */
    let items = this.items.map((itemEl) => {
      const input = this.getItemInput(itemEl);
      const avatar = this.getAvatar(itemEl);

      return {
        text: getHTML(input),
        checked: itemEl.classList.contains(this.CSS.itemChecked),
        user_image: avatar.src,
        user_name: avatar.title,
      };
    });

    /**
     * Skip empty items
     */
    items = items.filter((item) => item.text.trim().length !== 0);

    return {
      items,
    };
  }

  /**
   * Validate data: check if Checklist has items
   *
   * @param {ChecklistData} savedData — data received after saving
   * @returns {boolean} false if saved data is not correct, otherwise true
   * @public
   */
  validate(savedData) {
    return !!savedData.items.length;
  }

  /**
   * Toggle checklist item state
   *
   * @param {MouseEvent} event - click
   * @returns {void}
   */
  toggleCheckbox(event) {
    const checkListItem = event.target.closest(`.${this.CSS.item}`);
    const checkbox = checkListItem.querySelector(`.${this.CSS.checkboxContainer}`);

    if (checkbox.contains(event.target)) {
      checkListItem.classList.toggle(this.CSS.itemChecked);
      checkbox.classList.add(this.CSS.noHover);
      checkbox.addEventListener('mouseleave', () => this.removeSpecialHoverBehavior(checkbox), { once: true });
    }
  }

  findEmployeeImage(user_name) {
    const found = window.editorEmployees.find((element) => element.value === user_name);
    return found ? found.image : '';
  }

  /**
   * Create Checklist items
   *
   * @param {ChecklistItem} item - data.item
   * @returns {Element} checkListItem - new element of checklist
   */
  createChecklistItem(item = {}) {
    const checkListItem = make('div', this.CSS.item);
    const checkbox = make('span', this.CSS.checkbox);
    const checkboxContainer = make('div', this.CSS.checkboxContainer);
    const textField = make('div', this.CSS.textField, {
      innerHTML: item.text ? item.text : '',
      contentEditable: !this.readOnly,
    });

    const image = this.findEmployeeImage(item.user_name);

    const avatar = make('img', this.CSS.avatar, {
      src: image ? image.replaceAll('&amp;', '&') : BASE_URL+'images/user-icon.png',
      title: item.user_name ? item.user_name : 'Not assigned',
      'data-toggle': 'tooltip',
    });

    if (!this.readOnly) {
      avatar.setAttribute('onclick', 'selectEmployee(this)');
    }


    const usernameField = make('div', this.CSS.userName, {
      innerHTML: `[${item.user_name}]`,
      contentEditable: !this.readOnly,
    });

    if (item.checked) {
      checkListItem.classList.add(this.CSS.itemChecked);
    }

    checkbox.innerHTML = IconCheck;

    checkboxContainer.appendChild(checkbox);

    checkListItem.appendChild(avatar);
    checkListItem.appendChild(checkboxContainer);
    checkListItem.appendChild(textField);

    return checkListItem;
  }


  /**
   * Append new elements to the list by pressing Enter
   *
   * @param {KeyboardEvent} event - keyboard event
   */
  enterPressed(event) {
    event.preventDefault();

    const { items } = this;
    const currentItem = document.activeElement.closest(`.${this.CSS.item}`);
    const currentItemIndex = items.indexOf(currentItem);
    const isLastItem = currentItemIndex === items.length - 1;

    /**
     * Prevent checklist item generation if it's the last item and it's empty
     * and get out of checklist
     */
    if (isLastItem) {
      const currentItemText = getHTML(this.getItemInput(currentItem));
      const isEmptyItem = currentItemText.length === 0;

      if (isEmptyItem) {
        const currentBlockIndex = this.api.blocks.getCurrentBlockIndex();

        currentItem.remove();

        this.api.blocks.insert();
        this.api.caret.setToBlock(currentBlockIndex + 1);

        return;
      }
    }

    /**
     * Cut content after caret
     */
    const fragmentAfterCaret = extractContentAfterCaret();
    const htmlAfterCaret = fragmentToHtml(fragmentAfterCaret);

    /**
     * Create new checklist item
     */
    const newItem = this.createChecklistItem({
      text: htmlAfterCaret,
      checked: false,
    });

    /**
     * Insert new checklist item as sibling to currently selected item
     */
    this._elements.wrapper.insertBefore(newItem, currentItem.nextSibling);

    /**
     * Move caret to contentEditable textField of new checklist item
     */
    moveCaret(this.getItemInput(newItem), true);
  }

  /**
   * Handle backspace
   *
   * @param {KeyboardEvent} event - keyboard event
   */
  backspace(event) {
    const currentItem = event.target.closest(`.${this.CSS.item}`);
    const currentIndex = this.items.indexOf(currentItem);
    const prevItem = this.items[currentIndex - 1];

    if (!prevItem) {
      return;
    }

    const selection = window.getSelection();
    const caretAtTheBeginning = selection.focusOffset === 0;

    if (!caretAtTheBeginning) {
      return;
    }

    event.preventDefault();

    /**
     * Append content after caret to the previous item
     * and remove the current one
     */
    const fragmentAfterCaret = extractContentAfterCaret();
    const prevItemInput = this.getItemInput(prevItem);
    const prevItemChildNodesLength = prevItemInput.childNodes.length;

    prevItemInput.appendChild(fragmentAfterCaret);

    moveCaret(prevItemInput, undefined, prevItemChildNodesLength);

    currentItem.remove();
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
      wrapper: 'cdx-checklist',
      item: 'cdx-checklist__item',
      itemChecked: 'cdx-checklist__item--checked',
      noHover: 'cdx-checklist__item-checkbox--no-hover',
      checkbox: 'cdx-checklist__item-checkbox-check',
      textField: 'cdx-checklist__item-text',
      checkboxContainer: 'cdx-checklist__item-checkbox',
      avatar: 'cdx-checklist__item-avatar',
      userName: 'cdx-checklist__item-username',
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
  removeSpecialHoverBehavior(el) {
    el.classList.remove(this.CSS.noHover);
  }

  /**
   * Find and return item's content editable element
   *
   * @private
   * @param {Element} el - item wrapper
   * @returns {Element}
   */
  getItemInput(el) {
    return el.querySelector(`.${this.CSS.textField}`);
  }

  getAvatar(el) {
    return el.querySelector(`.${this.CSS.avatar}`);
  }
}
