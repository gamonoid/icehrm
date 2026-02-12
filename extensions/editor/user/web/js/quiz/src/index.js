import { LANGUAGES, settings, TEXTS, Toolbox, TYPES } from "./constants";
import {
  createButton,
  createIconButton,
  createLoader,
  renderSettings,
  renderVariants,
} from "./utils";
import "./index.scss";
import "./loader.scss";

class Quiz {
  #question = "";
  #answers = new Set();
  #variants = [];
  #type = "singleSelect";
  #language = "en";
  #validation = { min: 2 };

  constructor(args) {
    const { data, block, readOnly, config, api } = args;
    this.data = data;
    this.readOnly = readOnly ?? false;
    this.config = config;
    this.block = block;
    this.api = api;
    this.settings = settings;

    if (config.validation) {
      this.#validation = { ...this.#validation, ...config.validation };
    }
    this._setVariants();
    if (Array.isArray(data?.answers)) {
      this.#answers = new Set(data.answers);
    }
    if (typeof data?.type === "string") this.#type = data.type;
    if (typeof data?.question === "string") this.#question = data.question;
    if (LANGUAGES.includes(config.language)) this.#language = config.language;

    this._createRootContainer();
    this._insertHeader();
    this._insertBody();
    this._insertFooter();
  }

  static get isReadOnlySupported() {
    return true;
  }

  static get enableLineBreaks() {
    return true;
  }

  static get toolbox() {
    return Toolbox;
  }

  render() {
    this._renderHeader();
    this._renderBody();
    this._renderFooter();

    return this.container;
  }

  renderSettings = () => {
    return renderSettings(this.settings, this._changeType, this);
  };

  _renderHeader() {
    this._renderQuestion();
  }

  _changeType = (setting) => {
    if (setting.type !== this.#type) {
      this.#type = setting.type;
      this.#answers = new Set();
      this._renderVariants();
    }
  };

  _addVariant = (index) => {
    const newVariant = { value: this.#variants.length, text: "" };
    if (index) {
      this.#variants = this.#variants
        .slice(0, index)
        .concat([newVariant])
        .concat(this.#variants.slice(index));
    } else {
      this.#variants.push({ value: this.#variants.length, text: "" });
    }
    this._renderVariants({ autoFocus: true, focusIndex: index });
  };

  _deleteVariant = (index) => {
    this.#variants[index] = null;
    this.#variants = this.#variants.filter(Boolean);
    this._renderVariants();
  };

  _renderQuestion() {
    // render nothing if question is not provided in read only mode
    if (this.readOnly && !this.#question) return;

    const question = document.createElement("p");
    question.className = "question";
    question.innerHTML = this.#question;
    question.contentEditable = true;
    question.onblur = (event) => (this.#question = event.target.innerHTML);
    this.header.appendChild(question);
  }

  _renderVariants(config) {
    const { autoFocus = false, focusIndex } = config || {};
    const variantsContainer = renderVariants(this.#variants, this.#type, this);
    this.body.innerHTML = "";
    this.body.appendChild(variantsContainer);

    if (autoFocus) {
      let focusingItem = null;
      if (focusIndex) {
        const variants = variantsContainer.children;
        focusingItem = variants[focusIndex];
      } else {
        focusingItem = variantsContainer.lastElementChild;
      }
      const focusingItemParagraph = focusingItem.querySelector("p");
      focusingItemParagraph.focus();
    }
  }

  _renderBody() {
    this._renderVariants();
  }

  _renderError(message) {
    let error = this.body.querySelector(".cdx-quiz-error");
    if (error) {
      error.innerText = message;
      return;
    }
    error = document.createElement("div");
    error.className = "cdx-quiz-error";
    error.innerText = message;
    this.body.appendChild(error);
  }

  _renderSuccess(message) {
    let error = this.body.querySelector(".cdx-quiz-success");
    if (error) {
      error.innerText = message;
      return;
    }
    error = document.createElement("div");
    error.className = "cdx-quiz-success";
    error.innerText = message;
    this.body.appendChild(error);
  }

  _clearErrorAndSuccess() {
    const error = this.body.querySelector(".cdx-quiz-error");
    error?.remove();
    const success = this.body.querySelector(".cdx-quiz-success");
    success?.remove();
  }

  _renderFooter() {
    this.footer.className = "quiz-footer";

    const buttons = document.createDocumentFragment();
    if (this.readOnly) {
      const submitBtn = createButton();
      const submitText = TEXTS[this.#language].footer.submit;
      submitBtn.innerText = submitText;

      submitBtn.onclick = async () => {
        if (this.#answers.size === 0) {
          this._clearErrorAndSuccess();
          this._renderError(TEXTS[this.#language].errors.required);
        } else {
          const loader = createLoader();
          submitBtn.innerText = "";
          submitBtn.appendChild(loader);

          try {
            this.config.onSubmit({
              id: this.block.id,
              selectedVariants: Array.from(this.#answers),
            }).then((value) => {
              console.log(value);
              this._clearErrorAndSuccess();
              if (!value.correct) {
                this._renderError('Incorrect answer. Please try again.');
              } else {
                this._renderSuccess('Correct answer.');
              }
            });
          } catch (e) {
            console.error(e);
          }

          submitBtn.innerText = submitText;
        }
      };
      buttons.appendChild(submitBtn);
    } else {
      const addVariantBtn = createButton();
      addVariantBtn.classList.add("qt-add-btn");
      addVariantBtn.innerText = "+";
      addVariantBtn.onclick = () => this._addVariant();
      buttons.appendChild(addVariantBtn);
    }

    this.footer.innerHTML = "";
    this.footer.appendChild(buttons);
  }

  _variantInputChangeHandler = (event) => {
    event.stopPropagation();
    const value = Number(event.target.value);
    const checked = event.target.checked;

    // if input is unchecked remove the value from the answers
    if (!checked) {
      this.#answers.delete(value);
      return;
    }

    // add the value to the answers
    if (this.#type === TYPES.singleSelect) {
      this.#answers = new Set([value]);
    } else {
      this.#answers.add(value);
    }
    this._clearErrorAndSuccess();
  };

  _variantTextChangeHandler = (event, index) => {
    this.#variants[index] = {
      ...this.#variants[index],
      text: event.target.innerHTML,
    };
  };

  _setVariants() {
    const min = this.#validation.min;

    const isArray = Array.isArray(this.data?.variants);
    if (!isArray) {
      // if variants is not provided -> set default variants
      this.#variants = Array(min)
        .fill("")
        .map((_, index) => ({ value: index, text: "" }));
      return;
    }

    const variants = Array.from(this.data.variants);
    if (variants.length < min)
      console.error("Quiz plugin variants size should NOT be LESS than ", min);
    this.#variants = variants;
  }

  _createRootContainer() {
    this.container = document.createElement("div");
    this.container.className = "quiz-tool-container";
  }

  _insertHeader() {
    this.header = document.createElement("div");
    this.header.className = "quiz-header";
    this.container.appendChild(this.header);
  }

  _insertBody() {
    this.body = document.createElement("form");
    this.container.appendChild(this.body);
  }

  _insertFooter() {
    this.footer = document.createElement("div");
    this.container.appendChild(this.footer);
  }

  get validation() {
    return this.#validation;
  }

  save() {
    return {
      question: this.#question,
      variants: this.#variants,
      answers: Array.from(this.#answers),
      type: this.#type,
    };
  }

  getAnswers = () => {
    return this.#answers;
  };
}

export default Quiz;
